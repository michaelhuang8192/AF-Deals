<?php
require_once(dirname(__file__) . '/init.php');

$pgsz = @intval(@$_REQUEST['pagesize']);
$sidx = @intval(@$_REQUEST['sidx']);
$eidx = @intval(@$_REQUEST['eidx']);

if($pgsz <= 0 || $sidx < 0 || $eidx <= $sidx) die();

$qs = sprintf('SELECT prod_id,MIN(start_ts) AS min_start_ts,img_id 
FROM snapshot_detail 
GROUP BY prod_id 
ORDER BY min_start_ts DESC 
LIMIT %d , %d', $sidx * $pgsz, ($eidx - $sidx) * $pgsz );

$qs = sprintf(
'select i.img_url,p.prod_name,s.min_start_ts,p.lowest,p.highest,c.cate_name from (%s) s 
left join image i on (s.img_id=i.img_id) 
left join product p on(s.prod_id=p.prod_id) 
left join category c on(p.cate_id=c.cate_id) 
',
$qs);


OpenDB();

$rlen = $g_db->query('SELECT count(DISTINCT prod_id) FROM snapshot_detail')->fetch_row();
$rlen = intval($rlen[0]);

$js = array('res'=>array('len'=>$rlen, 'dataseq'=>1));
$ret = array('_'=>null);
for($i = $sidx; $i < $eidx; $i++) $ret[ strval($i) ] = array();

$k = 0;
$i = $sidx;
$res = $g_db->query($qs);
while($e = $res->fetch_row()) {
    $ret[$i][] = array($e[0],
                       html_entity_decode($e[1], ENT_QUOTES, "UTF-8"),
                       html_entity_decode($e[5], ENT_QUOTES, "UTF-8"),
                       number_format(floatval($e[3]) / 100, 2),
                       number_format(floatval($e[4]) / 100, 2),
                       strftime('%m/%d/%y %I:%M:%S %p', $e[2]),
                       );
    if(++$k == $pgsz) { $i++; $k = 0; }
}

$js['res']['rpg'] = $ret;
print json_encode($js);

?>

