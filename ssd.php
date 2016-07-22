<?php
require_once(dirname(__file__) . '/init.php');

isset($_REQUEST['ss_id']) or die();
$ss_id = @intval($_REQUEST['ss_id']);

OpenDB();

$res = $g_db->query("select ts,data from snapshot where ss_id=$ss_id");
if($res->num_rows < 1) die();
$ss = $res->fetch_row();

$pds = array();
$sds = unpack('V*', $ss[1]);
if($sds) {
    $res = $g_db->query('select ssd.prod_id,ssd.start_ts,ssd.sale_price,ssd.regular_price,p.prod_url,i.img_url,p.prod_id,p.prod_name,c.cate_id,c.cate_name,c.topcate_id from snapshot_detail ssd '
                        .'left join image i on (ssd.img_id=i.img_id) '
                        .'left join product p on (ssd.prod_id=p.prod_id) '
                        .'left join category c on (ssd.cate_id=c.cate_id) '
                        .'where ssd_id in ('. implode(',', $sds) . ') order by ssd.sale_price asc');
    while ($e = $res->fetch_row()) {
        $e[2] = sprintf("%0.2f", intval($e[2]) / 100.0);
        $e[3] = sprintf("%0.2f", intval($e[3]) / 100.0);
        $pds[] = $e;
    }
}

$s = json_encode( array('req_ss_id' => $ss_id, 'ssd_ts' => $ss[0], 'ssd' => $pds) );

$f_hs = md5($s);
$r_hs = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : '');

header('Cache-Control: public');
header("Etag: $f_hs");
header("Last-Modified: " . gmdate("D, d M Y H:i:s", 0) . " GMT");
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . " GMT");

if(!strcasecmp($f_hs, $r_hs)) {
    header("HTTP/1.1 304 Not Modified");
    die();
}

print $s;
?>