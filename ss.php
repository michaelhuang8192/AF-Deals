<?php
require_once(dirname(__file__) . '/init.php');

isset($_REQUEST['ss_id']) or die();
$ss_id = @intval($_REQUEST['ss_id']);

OpenDB();

$res = $g_db->query("select count(*) from snapshot");
$row = $res->fetch_row();

$alert = 0;
$last_ssi_id = intval($row[0]);

if($ss_id && $last_ssi_id != $ss_id) {
    
    $catelist = array();
    if(isset( $_COOKIE['_af_catelist_v1_'])) {
        $cs = trim($_COOKIE['_af_catelist_v1_']);
        foreach( explode('&', $cs) as $c ) {
            if(!$c) continue;
            $r = explode('=', $c);
            if(count($r) != 2) continue;
            $catelist[ intval($r[0]) ] = intval($r[1]);
        }
    }
    
    $res = $g_db->query("select data,ts from snapshot where ss_id=$last_ssi_id");
    if($res->num_rows < 1) die();
    $ss = $res->fetch_row();

    $pds = array();
    $sts = intval($ss[1]);
    $sds = unpack('V*', $ss[0]);
    if($sds) {
        $qs = sprintf('select cate_id,start_ts from snapshot_detail '
                      .'where ssd_id in (%s) and ss_id=%u',
                      implode(',', $sds), $last_ssi_id
                      );
        $res = $g_db->query($qs);
        while ($e = $res->fetch_row()) {
            if($sts != intval($e[1])) continue;
            
            $cate_id = intval($e[0]);
            $topcate_id = $g_site_id[1][ $cate_id >> 24 ];
            
            if( isset($catelist[$cate_id]) ) {
                if( !$catelist[$cate_id] ) continue;
                
            } else if( isset($catelist[$topcate_id]) ) {
                if( !$catelist[$topcate_id] ) continue;
                
            }
            
            $alert = 1;
            break;
        }
        
    }
    
}

print json_encode( array('req_ss_id' => $ss_id,
                         'alert' => $alert,
                         'max_ss_id' => $last_ssi_id
                        ));

?>