<?php
require_once(dirname(__file__) . '/init.php');

isset($_REQUEST['term']) or die();
$s = trim($_REQUEST['term']);

$kws = array();
$s = strtolower( preg_replace('/[^0-9a-z]+/i', ' ', $s) );
foreach(explode(' ', $s) as $p) {
    if(!$p || is_numeric($p)) continue;
    $kws[ $p ] = strlen($p);
}

if(!$kws) die();

OpenDB();

$kws_qs = '';
foreach($kws as $k=>$v) $kws_qs .= sprintf("skw_word like '%s%%' or ", $g_db->real_escape_string($k));
if($kws_qs) $kws_qs = substr($kws_qs, 0, -4);

$inc_qs = '';
if(count($kws) > 1) {
    foreach($kws as $k=>$v) if($v > 2) $inc_qs .= sprintf("i_skw.skw_word like '%s%%' or ", $g_db->real_escape_string($k));
    if($inc_qs) $inc_qs = '(select count(*) from search_index i_si left join search_keyword i_skw on (i_skw.skw_id=i_si.skw_id) where i_si.prod_id=si.prod_id and ('.substr($inc_qs, 0, -4).')) and ';
    foreach($kws as $k=>$v) {
        if($v > 2) continue;
        $inc_qs .= sprintf("(select count(*) from search_index i_si left join search_keyword i_skw on (i_skw.skw_id=i_si.skw_id) where i_si.prod_id=si.prod_id and i_skw.skw_word like '%s%%') and ", $g_db->real_escape_string($k));
    }
    if($inc_qs) $inc_qs = substr($inc_qs, 0, -5);
}

if($inc_qs) {
    $qs = "select m.prod_id,p.prod_name,c.cate_name,p.last_ss_id,p.ss_count,p.lowest,p.highest,m.pos from 
(select si.prod_id,sum(si.weight) as pos,(".$inc_qs.") as keep from search_keyword skw 
left join search_index si on (skw.skw_id=si.skw_id) 
where " . $kws_qs . " group by si.prod_id) m 
left join product p on (m.prod_id=p.prod_id) 
left join category c on (p.cate_id=c.cate_id) 
where m.keep > 0 order by m.pos desc,p.prod_name asc,m.prod_id desc limit 20";

} else {
    $qs = "select si.prod_id,p.prod_name,c.cate_name,p.last_ss_id,p.ss_count,p.lowest,p.highest,sum(si.weight) as pos from search_keyword skw 
left join search_index si on (skw.skw_id=si.skw_id) 
left join product p on (si.prod_id=p.prod_id) 
left join category c on (p.cate_id=c.cate_id) 
where " . $kws_qs . " group by si.prod_id order by pos desc,p.prod_name asc,si.prod_id desc limit 20";

}

//print $qs;

$res = $g_db->query($qs);
$ret = array();
while($e = $res->fetch_assoc()) {
    $e['lowest'] = sprintf("%0.2f", intval($e['lowest']) / 100.0);
    $e['highest'] = sprintf("%0.2f", intval($e['highest']) / 100.0);
    $ret[] = $e;
}

print json_encode($ret);
?>