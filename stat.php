<?php
require_once(dirname(__file__) . '/init.php');

isset($_REQUEST['prod_id']) or die();
$prod_id = @intval($_REQUEST['prod_id']);

OpenDB();

$res = $g_db->query("select * from product where prod_id=$prod_id");
if($res->num_rows < 1) die();
$prod = $res->fetch_row();
$prod[4] = sprintf("%0.2f", intval($prod[4]) / 100.0);
$prod[5] = sprintf("%0.2f", intval($prod[5]) / 100.0);

$cts = time();
$stat = array();
$res = $g_db->query('select ssd_id,img_id,ss_id,start_ts,end_ts,sale_price,regular_price from snapshot_detail '
                    .'where prod_id=' . $prod_id . ' order by ssd_id desc');
while ($e = $res->fetch_row()) {
    $e[5] = sprintf("%0.2f", intval($e[5]) / 100.0);
    $e[6] = sprintf("%0.2f", intval($e[6]) / 100.0);
    
    $sts = intval($e[3]);
    $ets = intval($e[4]);
    if(!$ets) $ets = $cts;
    $t_s = $ets - $sts;
    $t_h = $t_s / 3600;
    $t_s = $t_s % 3600;
    $t_m = $t_s / 60;
    $t_s = $t_s % 60;
    $e[] = sprintf('%02d:%02d:%02d', $t_h, $t_m, $t_s);
    
    $stat[] = $e;
}

print json_encode( array('req_prod_id' => $prod_id, 'prod' => $prod, 'stat' => $stat) );

?>