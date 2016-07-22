<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>AF</title>

<link type="text/css" href="css/redmond/jquery-ui-1.8.20.custom.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>
<script type="text/javascript" src="js/index.js?rev=20130720_002"></script>

<style type="text/css">
body {margin:0;padding:0;font-size:18px;font-family:"Times New Roman",Times,serif;}
#ctrl_cnt {background-color:#f1f1f1;padding:15px;position:fixed;left:0;top:0;height:60px;width:100%;border-bottom:1px solid #9d9d9d;white-space:nowrap}
#ctrl_player {height:0px;width:0px;display:none;}
#ctrl_info input[type="text"] {border:1px solid #b8b8b8; width:70px; font-size:18px;}
#ctrl_search {width:150px;font-size:18px;}
#ctrl_title {color:blue;text-align:center;font-weight:bold;font-size:22px;}
#ssd_cnt {background-color:#fefefe;padding:100px 0 0 10px;}
#report {font-size:18px;width:120px;}
#alert_sound {font-size:18px;width:120px;}
#loading {height:48px;width:48px;position:fixed;left:49%;top:49%;display:none}

.ctrl_button {font-size:18px;}

.ssd_prod {text-align:center;width:230px;height:330px;float:left;background-color:#efefef;font-size:24px;}
.ssd_prod:nth-child(even) {background-color:#fffbef}
.ssd_prod .prod_img {width:226px;height:226px;border:0}
.ssd_prod .prod_title {text-decoration:none;white-space:nowrap;color:#212121}
.ssd_prod .prod_title_new {color:red}
.ssd_prod .prod_price {color:blue;text-decoration:none;}
.ssd_prod .prod_cate {color:#98490a;text-decoration:none;}

li.sli_item {white-space:nowrap;}
span.sli_item_c0 {color:red}
span.sli_item_c1 {color:blue}
span.sli_item_c2 {color:purple}
span.sli_item_c3 {color:green}

.prod_stat_tb {border-collapse:collapse;font-size:16px;width:100%;color:#2d2d2d}
.prod_stat_tb th {background-color:#f1f0f0}
.prod_stat_tb td {text-align:center;cursor:pointer}
.prod_stat_tb, .prod_stat_tb th, .prod_stat_tb td {border:1px solid #a1a1a1}

</style>

</head>

<body>

<div id="main_cnt">
    <div id="ssd_cnt">
        <div id="ssd_body"></div>
    </div>
    <div id="ctrl_cnt">
        <a class="ctrl_button ctrl_button_prev" href="#" title="Prev">&nbsp;</a>
        <span id="ctrl_info"><input type="text" name="cur_ss_id" /> / <input type="text" name="max_ss_id" disabled="disabled" /></span>
        <a class="ctrl_button ctrl_button_next" href="#" title="Next">&nbsp;</a>
        <a class="ctrl_button ctrl_button_last" href="#" title="Last">&nbsp;</a>
        <a class="ctrl_button ctrl_button_setting" href="#" title="Setting">&nbsp;</a>
        
        <span>
            <select id="report">
                <option value=""> - Report - </option>
                <option value="report.php">Unique - All</option>
                <option value="report01.php">Unique - Daily</option>
            </select>
        </span>
        
        <span id="ctrl_player"><audio controls="controls" id="ctrl_audio_player"></audio></span>
        <span>
            <select id="alert_sound">
<?php
$sfs = glob("sound/*.mp3");
sort($sfs);
foreach($sfs as $s) {
    print "<option value=\"$s\">" . basename($s) ."</option>";
}
?>
            </select>
        </span>
        
        <span><input id="ctrl_search" /></span>
        <div id="ctrl_title"></div>
    </div>
    <div id="loading"><img src="img/loading.gif" alt="" /></div>
    <div id="setting_dialog" title="Setting">
        <div>
            <select id="sd_topcate"></select>
            <br/>
            <input type="checkbox" id="sd_catelist_checkall" value="0" /><span>--- All ---</span>
        </div>
        <div id="sd_catelist"></div>
    </div>
    <div id="stat_dialog"></div>
    <div id="enable_alert_dialog" title="Enable Alert"></div>
</div>

</body>

</html>