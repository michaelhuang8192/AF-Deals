<?php
require_once( dirname(__file__) . '/config.php' );

$g_db = null;

function OpenDB()
{
    global $g_config, $g_db;
    
    if($g_db) return $g_db;
    
    $dbc = $g_config['dbc'];
    $g_db = new mysqli($dbc['host'], $dbc['user'], $dbc['passwd'], $dbc['dbn']);
    
    return $g_db;
}


function CloseDB()
{
    global $g_db;
    if(!$g_db) return;
    
    $g_db->close();
    $g_db = null;
}


$_a = array();
$_b = array();
$_k = 0;
foreach($g_topcate_id as $t) {
    $_a[$t] = $_k;
    $_b[$_k] = $t;
    $_k++;
}
$g_site_id = array($_a, $_b);

?>