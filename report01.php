<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>AF</title>

<link type="text/css" href="css/redmond/jquery-ui-1.8.20.custom.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>

<style type="text/css">
body {margin:0;padding:50px 0 0 0;font-family:"Times New Roman",Times,serif;font-size:22px;background-color:#313131;}
#ctrl_blk {position:absolute;left:0;top:0;width:100%;height:46px;border-bottom:4px solid #d3a424}
#ctrl_blk .ctrl_blk_cnt {padding:5px;height:36px;line-height:36px;}
#ctrl_blk .date {font-size:20px;width:120px;}
#ctrl_blk .filter {font-size:20px;width:150px;}
#ctrl_blk .btn {font-size: 14px;}

#data_blk {padding:5px;font-size:20px;color:#FFF;text-align:center}
#data_blk .prod_blk {float:left;width:226px;height:330px;border:5px solid #FFF;margin:5px;overflow:hidden}
#data_blk .prod_blk:nth-child(even) {border:5px solid #ffdaae}
#data_blk .prod_blk > div:first-child {width:226px;height:226px;}

#data_blk .prod_blk > div img {border:none}

</style>

<script type="text/javascript">
var g_v_date = null;
var g_v_filter = null;
var g_cate = {};

function load_catelist_from_cookie()
{
    var s = $.cookie('_af_catelist_v1_');
    if(!s) return;
    
    var kvs = s.split('&');
    for(var i = 0; i < kvs.length; i++) {
        var kv = kvs[i];
        if(!kv) continue;
        var vs = kv.split('=');
        g_cate[ vs[0] ] = [ parseInt(vs[1]), null, null ];
    }
    
}

function is_in_wantlist(cate_id, topcate_id)
{
    if( g_cate[cate_id] ) {
        if( !g_cate[cate_id][0] ) return 0;
    } else if( g_cate[topcate_id] ) {
        if( !g_cate[topcate_id][0] ) return 0;
    }
    
    return 1;
}

function load_data_cb(d)
{
    var s = '';
    if(d) {
        var f = parseInt($('.filter').val());
        var t = new Date();
        for(var i = 0; i < d.length; i++) {
            var r = d[i];
            if( f && !is_in_wantlist(r[7], r[8]) ) continue;
            
            t.setTime( parseInt(r[1]) * 1000 );
            
            s += '<div class="prod_blk"><div><a target="_blank" href="'+r[9]+'"><img src="'
            +r[2]+'"/></a></div><div>'
            +r[3]+'</div><div>$'
            +r[4]+' - $'+r[5]+'</div><div>'
            +r[6]+'</div><div>'
            +t.toLocaleTimeString()+'</div><div>'
            +'</div></div>';
        }
    }
    
    $('#data_blk > div:first-child').html(s);
}

function load_data()
{
    var ts = g_v_date.datepicker("getDate").getTime() / 1000;
    $.get('report01_data.php', {ts:ts}, load_data_cb, 'json');
}


$(function() {
    g_v_date = $('.date').datepicker({onSelect:load_data}).datepicker("setDate", '0d');
    g_v_filter = $('.filter').change(load_data);
    
    $('.btn_prev').button().click(function() {
        var d = g_v_date.datepicker("getDate");
        d.setTime( d.getTime() - 86400000 );
        g_v_date.datepicker("setDate", d);
        load_data();
    });
    $('.btn_next').button().click(function() {
        var d = g_v_date.datepicker("getDate");
        d.setTime( d.getTime() + 86400000 );
        g_v_date.datepicker("setDate", d);
        load_data();
    });
    
    load_catelist_from_cookie();
    
    g_v_filter.change();
})

</script>

</head>

<body>

<div id="ctrl_blk">
    <div class="ctrl_blk_cnt">
        <span class="btn btn_prev">&lt;</span>
        <input type="text" class="date" />
        <span class="btn btn_next">&gt;</span>
        <select class="filter">
            <option value="1">Apply Filter ?</option>
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>
    </div>
</div>

<div id="data_blk"><div></div><div style="clear:both"></div></div>

</body>

</html>
