<?php
require_once(dirname(__file__) . '/init.php');

isset($_REQUEST['ss_id']) or die();
$ss_id = @intval($_REQUEST['ss_id']);

$cates = array(
'12202' => array(1, '', 'Abercrombie Mens'),
'12203' => array(1, '', 'Abercrombie Womens'),
'12551' => array(1, '', 'Hollister Mens'),
'12552' => array(1, '', 'Hollister Womens'),
);

OpenDB();

$res = $g_db->query("select * from category order by cate_id asc");

while ($e = $res->fetch_row()) {
    $cates[ $e[0] ] = array(1, $e[1], $e[2]);
    if( !isset($cates[ $e[1] ]) ) $cates[ $e[1] ] = array(1, 0, 'unknown - ' . $e[1]);
}

print json_encode( array('req_ss_id'=>$ss_id, 'cates'=>$cates) );
?>