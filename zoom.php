<?php

isset($_REQUEST['link']) and ($link = trim(html_entity_decode($_REQUEST['link'], ENT_QUOTES, "UTF-8"))) or die();
($pos = strrpos($link, "_prod")) !== false or die();
$link = substr($link, 0, $pos - 2);

($pos = strrpos($link, "/anf/")) !== false or die();
$link2 = substr($link, 0, $pos + 1);

?>

<html>
    <head>
        
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript">
g_sc_idx = -1;
g_imgs = {};

function s7jsonResponse(data, none)
{
    if(data && data['IMAGE_SET']) {
        var yd = data['IMAGE_SET'].split(',');
        for(var y = 0; y < yd.length; y++) {
            var xd = yd[y].split(';');
            for(var x = 0; x < xd.length; x++) {
                var img_link = xd[x];
                if(!img_link || g_imgs[ img_link ] != undefined) continue;
                g_imgs[ img_link ] = true;
                
                $('#imgs_cnt').append('<img src="'
                                      + '<?php print $link2; ?>'
                                      + img_link + '?$' + img_link.match(/anf\/([a-z]+)_/i)[1] + 'ProductS7Zoom$'
                                      + '" border="0" alt="" /><br/>');
            }
        }
    }
    
    if(g_sc_idx <= 0 || data && data['IMAGE_SET']) {
        g_sc_idx++;
        
        $('#script_loader').html('<'+'script type="text/javascript" src="'
                                 + '<?php print $link; ?>'
                                 + (g_sc_idx < 10 ? '0' : '') + g_sc_idx + '?req=imageset,json'
                                 + '"><'+'/script>');
                                 
    }
}

$(document).ready(function() {
    s7jsonResponse(null);
});

</script>
        
        
    </head>
    
    <body>
        <div id="script_loader"></div>
        <div id="imgs_cnt"></div> 
    </body>
</html>