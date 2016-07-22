var g_cur_ss_id = 0;
var g_max_ss_id = 0;
var g_timer = null;
var g_last = true;
var g_ssd = null;
var g_ssd_ts = 0;
var g_v_cur_ss_id = null;
var g_v_max_ss_id = null;
var g_v_ssd = null;
var g_v_ctrl_title = null;
var g_cate = {};
var g_cate_ss_id = 0;
var g_catelist_change = false;
var g_v_loading = null;
var g_v_stat_dialog = null;
var g_v_alert_sound = null;
var g_v_ctrl_player = null;
var g_audio_ok = true;
var g_v_sd_catelist = null;
var g_v_sd_topcate = null;
var g_v_sd_catelist_checkall = null;
var g_v_setting_dialog = null;

function redirect(url){ var w = window.open(); var s = w.document.createElement('script'); s.innerHTML = 'window.open("'+url+'", "_self");'; w.document.body.appendChild(s);}

function new_alert(sf)
{
    if(!g_audio_ok) return;
    
    if(!sf) sf = g_v_alert_sound.val();

    var ao = g_v_ctrl_player.attr('src', sf).get(0);
    if(ao && ao.play){
        ao.play();
    }
    else {
        g_audio_ok = false;
        alert('Your browser does not support the audio element.');
    }
}

function stop_alert()
{
    var ao = g_v_ctrl_player.get(0);
    if(ao && ao.pause) ao.pause();
}

function is_in_wantlist(cate_id, topcate_id)
{
    if(g_cate) {
        if( g_cate[cate_id] ) {
            if( !g_cate[cate_id][0] ) return 0;
        } else if( g_cate[topcate_id] ) {
            if( !g_cate[topcate_id][0] ) return 0;
        }
    }
    
    return 1;
}

function show_ssd()
{
    if(!g_ssd) return;
    
    var n = '';
    var o = '';
    var r = '?' + Math.random();
    for(var i = 0; i < g_ssd.length; i++) {
        var d = g_ssd[i];
        if( !is_in_wantlist(d[8], d[10]) ) continue;
        
        if(d[1] == g_ssd_ts) {
            //n += '<div class="ssd_prod"><a href="javascript:redirect(\''+d[4]+r+'\')"><img class="prod_img" src="'+d[5]+'" alt=""/></a>';
			n += '<div class="ssd_prod"><a target="_blank" href="'+d[4]+r+'"><img class="prod_img" src="'+d[5]+'" alt=""/></a>';
            n += '<div><a target="_blank" href="zoom.php?link='+escape(d[5])+'" class="prod_title prod_title_new">'+d[7]+'</a></div>';
            n += '<div><a target="_blank" href="http://www.ebay.com/sch/i.html?_nkw='+escape(d[9].replace('&amp;', ' ')+' '+d[7].replace('&amp;', ' '))+'" class="prod_price">'+d[2]+' - '+d[3]+'</a></div>'
            n += '<div><a class="prod_cate" href="#" onclick="load_prod_stat('+d[6]+');return false;">'+d[9]+'</a></div>'
            n += '</div>';
            
        } else {
            //o += '<div class="ssd_prod"' + (o ? '' : ' style="clear:left"') + '><a href="javascript:redirect(\''+d[4]+r+'\')"><img class="prod_img" src="'+d[5]+'" alt=""/></a>';
			o += '<div class="ssd_prod"' + (o ? '' : ' style="clear:left"') + '><a target="_blank" href="'+d[4]+r+'"><img class="prod_img" src="'+d[5]+'" alt=""/></a>';
            o += '<div><a target="_blank" href="zoom.php?link='+escape(d[5])+'" class="prod_title">'+d[7]+'</a></div>';
            o += '<div><a target="_blank" href="http://www.ebay.com/sch/i.html?_nkw='+escape(d[9].replace('&amp;', ' ')+' '+d[7].replace('&amp;', ' '))+'" class="prod_price">'+d[2]+' - '+d[3]+'</a></div>'
            o += '<div><a class="prod_cate" href="#" onclick="load_prod_stat('+d[6]+');return false;">'+d[9]+'</a></div>'
            o += '</div>';
            
        }
        
    }
    
    t = new Date(g_ssd_ts * 1000);
    g_v_ctrl_title.html( g_cur_ss_id + ' - ' + t.toLocaleString() );
    g_v_ssd.html(n + o);
}

function load_ssd_cb(d)
{
    if(!d || !d.req_ss_id || d.req_ss_id != g_cur_ss_id) return;
    g_v_loading.hide();
    g_ssd = d.ssd;
    g_ssd_ts = d.ssd_ts;
    show_ssd();
}

function load_ssd(ss_id)
{
    if(g_cur_ss_id == ss_id && g_ssd) {
        //show_ssd();
        
    } else {
        g_ssd = null;
        g_ssd_ts = 0;
        g_cur_ss_id = ss_id;
        $.get('ssd.php?ss_id=' + ss_id, load_ssd_cb, 'json');
        update_ctrl_info();
        g_v_loading.show();
    }
}

function load_ssd_disable_last(ss_id)
{
    g_last = false;
    load_ssd(ss_id);
}

function update_ctrl_info()
{
    g_v_cur_ss_id.val('' + g_cur_ss_id);
    g_v_max_ss_id.val('' + g_max_ss_id);
}

function load_ss_id_cb(d)
{
    if(d && d.max_ss_id && g_max_ss_id < d.max_ss_id) {
        g_max_ss_id = d.max_ss_id;
        if(g_last)
            load_ssd(g_max_ss_id);
        else
            update_ctrl_info();
        
        if(d.alert) new_alert();
    }
    
    if(g_timer) { window.clearTimeout(g_timer); g_timer = null; }
    g_timer = window.setTimeout("load_ss_id()", 3000);
}

function delay_load_ss_id()
{
    if(g_timer) { window.clearTimeout(g_timer); g_timer = null; }
    g_timer = window.setTimeout("load_ss_id()", 5000);
}

function load_ss_id()
{
    if(g_timer) { window.clearTimeout(g_timer); g_timer = null; }
    $.get('ss.php?ss_id=' + g_max_ss_id + '&_r=' + Math.random(), load_ss_id_cb, 'json').error(delay_load_ss_id);
}

function ctrl_button_last_click()
{
    g_last = true;
    stop_alert();
    if(g_cur_ss_id == g_max_ss_id) return;
    load_ssd(g_max_ss_id);
}

function ctrl_button_prev_click()
{
    g_last = false;
    if(g_cur_ss_id <= 1) return;
    load_ssd(g_cur_ss_id - 1);
}

function ctrl_button_next_click()
{
    g_last = false;
    if(g_cur_ss_id >= g_max_ss_id) return;
    load_ssd(g_cur_ss_id + 1);
}

function save_catelist_to_cookie()
{
    var s = '';
    for(k in g_cate) s += k + '=' + g_cate[k][0] + '&';
    $.cookie('_af_catelist_v1_', s, {expires:365});
}

function load_catelist_from_cookie()
{
    g_cate = {};
    g_cate_ss_id = 0;
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

function update_cate_setting(new_cate)
{
    if(!new_cate) return;
    
    old_cate = g_cate;
    for(var k in new_cate) {
        var nc = new_cate[k];
        if(!old_cate[k]) {
            if(nc[0] && nc[1] && old_cate[ nc[1] ])
                nc[0] = old_cate[ nc[1] ][0];
        } else {
            nc[0] = old_cate[k][0];
        }
    }
    g_cate = new_cate;
    save_catelist_to_cookie();
}

function load_setting_cb(d)
{
    if(!d || !d.req_ss_id) return;
    g_cate_ss_id = d.req_ss_id;
    update_cate_setting(d.cates);
    
    var s = '';
    for(var k in g_cate) {
        var c = g_cate[k];
        if(c[1]) continue;
        s += '<option value="'+k+'">'+c[2]+'</option>'
    }
    
    g_v_sd_topcate.html(s);
    sd_topcate_change();
}

function load_setting()
{
    if(g_cate_ss_id == g_max_ss_id) {
        sd_topcate_change();
    } else {
        $.get('cate.php?ss_id=' + g_max_ss_id + '&_r=' + Math.random(), load_setting_cb, 'json');
    }
}

function ctrl_button_setting_click()
{
    if( g_v_setting_dialog.dialog("isOpen") ) return false;
    
    g_catelist_change = false;
    load_setting();
    g_v_setting_dialog.dialog("open");
    return false;
}

function sd_topcate_change()
{
    g_v_sd_catelist_checkall.attr('checked', false);
    
    if(!g_cate) return;
    var topcate_id = g_v_sd_topcate.val();
    if(!topcate_id || !g_cate[topcate_id]) return;
    
    var s = '';
    
    var e = g_cate[topcate_id];
    s += '<input type="checkbox" value="'+topcate_id+'" '+ (e[0] ? 'checked="checked" ' : '') +'/>**Default**<br />';
    
    for(var k in g_cate) {
        e = g_cate[k];
        if(e[1] != topcate_id) continue;
        s += '<input type="checkbox" value="'+k+'" '+ (e[0] ? 'checked="checked" ' : '') +'/>' + e[2] + '<br />';
    }
    
    g_v_sd_catelist.html(s);
    
    $('input[type="checkbox"]', g_v_sd_catelist).change(sd_catelist_input_change);
}

function setting_dialog_close(event, ui)
{
    if(g_catelist_change) {
        save_catelist_to_cookie();
        show_ssd();
    }
}

function sd_catelist_all_change()
{
    var all_checked = $(this).attr('checked') ? 1 : 0;
    var clis = $('input[type="checkbox"]', g_v_sd_catelist);
    for(var i = 0; i < clis.length; i++) {
        var o = $(clis[i]);
        var checked = o.attr('checked') ? 1 : 0;
        if(all_checked != checked) $(clis[i]).click();
    }
    return false;
}

function sd_catelist_input_change()
{
    var o = $(this);
    var cate_id = $(this).val()
    var cate_ck = $(this).attr('checked') ? 1 : 0;
    var cur_cate = g_cate[cate_id];
    if(cur_cate[0] != cate_ck) {
        g_catelist_change = true;
        cur_cate[0] = cate_ck;
    }
    return false;
}

function ctrl_search_render_item(ul, item)
{
    return $('<li class="sli_item"></li>').data("item.autocomplete", item)
    .append('<a href="#" onclick="load_prod_stat('+item.prod_id+');return false;">'
            + '<span class="sli_item_c0">' + item.prod_name
            + '</span> - <span class="sli_item_c1">' + item.cate_name
            + '</span> - <span class="sli_item_c2">$' + item.lowest + ' : $' + item.highest
            + '</span> - <span class="sli_item_c3">' + item.last_ss_id + ' / ' + item.ss_count
            + '</span></a>'
    ).appendTo(ul);
}

function get_real_id(nid)
{
    return (parseInt(nid) & ((1 << 24) - 1));
}

function load_prod_stat_cb(data)
{
    if(!data || !data.req_prod_id) return;
    
    var stat = data.stat;
    var s = '<table class="prod_stat_tb"><th>img id</th><th>sale</th><th>regular</th><th>duration</th><th>start time</th>';
    var t = new Date();
    for(var i = 0; i < stat.length; i++) {
        var a = stat[i];
        t.setTime( parseInt(a[3]) * 1000 );
        s += '<tr title="'+a[2]+'" onclick="load_ssd_disable_last('+a[2]+');return false;"><td>'+get_real_id(a[1])+'</td>'
        +'<td>$'+a[5]+'</td><td>$'+a[6]+'</td><td>'+a[7]+'</td>'
        +'<td>'+t.toLocaleDateString()+' '+t.toLocaleTimeString()+'</td></tr>';
    }
    s += '</table>';
    
    var prod = data.prod;
    g_v_stat_dialog.html(s).dialog("option", "title", prod[6]+' ['+get_real_id(prod[0])+']'+' - '+'['+prod[2]+' / '+prod[3]+']'+' - ($'+prod[4]+' : $'+prod[5]+')');
}

function load_prod_stat(prod_id)
{
    g_v_stat_dialog.dialog("option", "title", 'Loading ' + prod_id).dialog("open");
    $.get('stat.php?prod_id=' + prod_id + '&_r=' + Math.random(), load_prod_stat_cb, 'json');
}

function enable_alert_dialog()
{
    if(navigator && navigator.platform) {
        var pf = navigator.platform.toLowerCase();
        if( pf.indexOf('ipad') > -1 || pf.indexOf('ipod') > -1 || pf.indexOf('iphone') > -1 ) {
            $("#enable_alert_dialog").dialog("open");
        }
    }
}

function enable_alert()
{
    $(this).dialog("close");
    new_alert('sound/beep.mp3');
}

function alert_sound_change()
{
    $.cookie('_alert_sound_file_', $(this).val(), {expires:365});
    new_alert();
}


function ss_id_input_keyup()
{
    var o = $(this);
    var ss_id = parseInt( o.val().replace(/[^0-9]/gi, '') );
    if(!ss_id) ss_id = 0;
    
    if(ss_id < 1 || ss_id > g_max_ss_id) ss_id = g_cur_ss_id;
    o.val(ss_id);
    if(ss_id) load_ssd(ss_id);
}

function ss_id_input_focusout()
{
    $(this).val(g_cur_ss_id);
}

$(document).ready(function(){
    //$.ajaxSetup({cache: false});
    
    $('a.ctrl_button_prev').button({icons:{primary:"ui-icon-seek-prev"}}).click(ctrl_button_prev_click);
    $('a.ctrl_button_next').button({icons:{primary:"ui-icon-seek-next"}}).click(ctrl_button_next_click);
    $('a.ctrl_button_last').button({icons:{primary:"ui-icon-seek-end"}}).click(ctrl_button_last_click);
    $('a.ctrl_button_setting').button({icons:{primary:"ui-icon-gear"}}).click(ctrl_button_setting_click);
    //$('a.ctrl_button_report').button({icons:{primary:"ui-icon-star"}});
    $('select#report').change(function() {
       var href = $(this).val();
       if(!href) return;
       
       $(this).val('');
       window.open(href);
    });
    
    $("#setting_dialog").dialog({autoOpen:false,height:600,width:300, close:setting_dialog_close});
    $('#sd_topcate').change(sd_topcate_change);
    
    $("#stat_dialog").dialog({autoOpen:false,height:500,width:650});
    
    $("#enable_alert_dialog").dialog({autoOpen:false,height:150,width:300,modal:true,buttons:{enable:enable_alert}});
    
    $('#ctrl_search').autocomplete({
        source: "search.php",
        minLength: 2
    }).data("autocomplete")._renderItem = ctrl_search_render_item;
    
    $('#alert_sound').change(alert_sound_change).val( $.cookie('_alert_sound_file_') || $('#alert_sound').val() );
    
    $('#ctrl_info input[name="cur_ss_id"]').keyup(ss_id_input_keyup).focusout(ss_id_input_focusout);
    
    g_v_cur_ss_id = $('#ctrl_info input[name="cur_ss_id"]');
    g_v_max_ss_id = $('#ctrl_info input[name="max_ss_id"]');
    g_v_ssd = $('#ssd_body');
    g_v_ctrl_title = $('#ctrl_title');
    g_v_loading = $('#loading');
    g_v_stat_dialog = $('#stat_dialog');
    g_v_alert_sound = $('#alert_sound');
    g_v_ctrl_player = $('span#ctrl_player audio');
    g_v_sd_catelist = $('#sd_catelist');
    g_v_sd_topcate = $('#sd_topcate');
    g_v_sd_catelist_checkall = $('#sd_catelist_checkall').change(sd_catelist_all_change);
    g_v_setting_dialog = $("#setting_dialog");
    
    load_catelist_from_cookie();
    load_ss_id();
    
    enable_alert_dialog();
    
});
