<?php
require_once(dirname(__file__) . '/init.php');

$from_ts = @intval(@$_REQUEST['ts']);
if($from_ts <= 0) die();
$to_ts = $from_ts + 86400;

$qs = sprintf(
'
select s.prod_id,s.start_ts,i.img_url,p.prod_name,s.lowest,s.highest,c.cate_name,c.cate_id,c.topcate_id,p.prod_url from (
SELECT prod_id, max(start_ts) as start_ts, min(img_id) as img_id, min(sale_price) as lowest, max(regular_price) as highest 
FROM snapshot_detail 
WHERE start_ts >= %u and start_ts < %u group by prod_id 
) s 
left join image i on (s.img_id=i.img_id) 
left join product p on(s.prod_id=p.prod_id) 
left join category c on(p.cate_id=c.cate_id) order by p.lowest asc limit 2000 
',
$from_ts, $to_ts
);

OpenDB();
$res = $g_db->query($qs);

$pds = array();
while($e = $res->fetch_row()) {
    $e[3] = html_entity_decode($e[3], ENT_QUOTES, "UTF-8");
    $e[6] = html_entity_decode($e[6], ENT_QUOTES, "UTF-8");
    $e[4] = sprintf("%0.2f", intval($e[4]) / 100.0);
    $e[5] = sprintf("%0.2f", intval($e[5]) / 100.0);
    $pds[] = $e;
}

print json_encode($pds);

?>
