<?php
require_once(dirname(__file__) . '/../init.php');

$data = json_decode(file_get_contents('data_tmp.txt'), true);
$cur_ts = time();
OpenDB();

$g_flog = fopen('af_log.txt', 'a');
function conlog()
{
    global $g_flog;
    $a = func_get_args();
    fwrite($g_flog, call_user_func_array('sprintf', $a) . "\n");
}

$pds = array();
$res = $g_db->query('select ss_id,data from snapshot order by ss_id desc limit 1');
$row = $res->fetch_row();
$new_ss_id = intval($row[0]) + 1;
$sds = unpack('V*', $row[1]);
if($sds) {
    $res = $g_db->query('select ssd_id,prod_id,cate_id,img_id,sale_price,regular_price from snapshot_detail where ssd_id in ('
                        . implode(',', $sds) . ')');
    while ($e = $res->fetch_row()) {
        $k = sprintf('%10u%10u', $e[1], $e[3]);
        $pds[$k] = array_map('intval', $e);
    }
}

if( count($sds) != count($pds) ) die('Len Not Match');

$cate_nzs = array();
$res = $g_db->query('select * from category');
while ($e = $res->fetch_row()) {
    $cate_nzs[ sprintf('%10u%s', intval($e[1]), $e[2]) ] = intval($e[0]);
}

$new_ssd = array();
$sds = array();
$sprods = array();
foreach($data as $cate) {
    $topcate_id = $cate[0];
    $site_id = $g_site_id[0][$topcate_id] << 24;
    $cate_name = htmlentities(strtolower(html_entity_decode($cate[2], ENT_QUOTES, "UTF-8")), ENT_QUOTES, "UTF-8");
    
    $cate_nz_id = sprintf('%10u%s', $topcate_id, $cate_name);
    if( isset($cate_nzs[$cate_nz_id]) ) {
        $cate_id = $cate_nzs[$cate_nz_id];
    } else {
        $qs = sprintf("insert into category values (NULL, %u,'%s')",
                      $topcate_id,
                      $g_db->real_escape_string($cate_name)
                      );
        $g_db->real_query($qs);
        $cate_nzs[$cate_nz_id] = $cate_id = $g_db->insert_id;
    }
    //printf("-- %s %u\n", $cate_name, $cate_id);
    
    foreach($cate[3] as $p) {
        $prod_id = $p[0] | $site_id;
        $sale_price = intval(floatval($p[5]) * 100);
        $regular_price = intval(floatval($p[4]) * 100);
        
        $m = explode('/anf/', $p[1], 2);
        $m = explode('_', $m[1], 4);
        //$img_id = intval(sprintf('%u%02u', $m[1], $m[2])) | $site_id;
        $img_id = (intval($m[1]) * 100 + intval($m[2])) | $site_id;
        
        $sprod_id = sprintf('%10u%10u', $prod_id, $img_id);
        if(isset($sprods[$sprod_id])) continue;
        $sprods[$sprod_id] = true;
        
        if( isset($pds[$sprod_id]) && ($pd=$pds[$sprod_id])
           && $pd[4] == $sale_price
           && $pd[5] == $regular_price )
        {
            $sds[] = $pd[0];
            unset( $pds[$sprod_id] );
            continue;
        } else {
            $qs = sprintf("insert into snapshot_detail values (NULL,%u,%u,%u,%u,%u,%u,%u,%u)",
                  $prod_id,
                  $cate_id,
                  $img_id,
                  $new_ss_id,
                  $cur_ts,
                  0,
                  $sale_price,
                  $regular_price
                  );
            $g_db->real_query($qs);
            $sds[] = $g_db->insert_id;
            printf("snapshot_detail:%d\n", $g_db->insert_id);
            
            if( isset($new_ssd[$prod_id]) ) {
                $new_ssd[$prod_id][0] = min($sale_price, $new_ssd[$prod_id][0]);
                $new_ssd[$prod_id][1] = max($regular_price, $new_ssd[$prod_id][1]);
            } else
                $new_ssd[$prod_id] = array($sale_price, $regular_price);
        }
        
        if( isset($pds[$sprod_id]) ) continue;
        
        $e_img_url = $g_db->real_escape_string(htmlentities($p[1], ENT_QUOTES, "UTF-8"));
        $qs = sprintf("insert into image values (%u,'%s') on duplicate key update img_url='%s'",
                  $img_id,
                  $e_img_url,
                  $e_img_url
                  );
        $g_db->real_query($qs);
        
        $prod_name = strtolower(html_entity_decode($p[3], ENT_QUOTES, "UTF-8"));
        $e_prod_name = $g_db->real_escape_string(htmlentities($prod_name, ENT_QUOTES, "UTF-8"));
        $e_prod_url = $g_db->real_escape_string(htmlentities($p[2], ENT_QUOTES, "UTF-8"));
        $qs = sprintf("insert into product values (%u,%u,0,0,%u,%u,'%s','%s') on duplicate key update prod_name='%s',prod_url='%s'",
                  $prod_id, $cate_id, $sale_price, $regular_price,
                  $e_prod_name,
                  $e_prod_url,
                  $e_prod_name,
                  $e_prod_url
                  );
        $g_db->real_query($qs);
        if(!$g_db->affected_rows) continue;
        
        $kws = array();
        $prod_name = preg_replace('/[^0-9a-z]+/i', ' ', $prod_name);
        foreach(explode(' ', $prod_name) as $np) {
            if(!$np || strlen($np) < 2 || is_numeric($np)) continue;
            if( isset($kws[ $np ]) )
                $kws[ $np ]++;
            else
                $kws[ $np ] = 10;
        }
        
        if(!$kws) continue;
        
        $sk_qs = 'insert ignore into search_keyword values ';
        $si_qs = 'insert into search_index values ';
        foreach($kws as $kk=>$kv) {
            $sk_qs .= sprintf("(NULL,'%s'),",
                              $g_db->real_escape_string($kk)
                              );
            $si_qs .= sprintf("((select skw_id from search_keyword where skw_word='%s' limit 1),%u,%u),",
                              $g_db->real_escape_string($kk), $prod_id, $kv
                              );
        }
        $g_db->real_query(substr($sk_qs, 0, -1));
        $g_db->real_query(substr($si_qs, 0, -1));
        
    }
    
}


if($pds || $new_ssd) {
    if($pds) {
        $s = '';
        foreach($pds as $k=>$v) $s .= $v[0] . ',';
        
        if($s) $s = substr($s, 0, -1);
        $g_db->real_query(sprintf('update snapshot_detail set end_ts=%u where ssd_id in (%s)', $cur_ts, $s));
    }
    
    array_unshift($sds, 'V*');
    $qs = sprintf("insert into snapshot values (%u,%u,'%s')",
                  $new_ss_id,
                  $cur_ts,
                  $g_db->real_escape_string(call_user_func_array('pack', $sds))
                  );
    $g_db->real_query($qs);
    printf("snapshot:%u\n", $new_ss_id);
    
    foreach($new_ssd as $k=>$v) {
        $qs = sprintf("update product set last_ss_id=%u,ss_count=ss_count+1,lowest=least(lowest,%u),highest=greatest(highest,%u) where prod_id=%u",
                      $new_ss_id, $v[0], $v[1], $k
                      );
        $g_db->real_query($qs);
    }
}

CloseDB();

print "Done";

fclose($g_flog);
?>