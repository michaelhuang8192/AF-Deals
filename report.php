<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>AF</title>

<link type="text/css" href="css/redmond/jquery-ui-1.8.20.custom.css" rel="stylesheet" />
<link type="text/css" href="css/tinygridv3.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script>
<script type="text/javascript" src="js/jquery-tinygridv3.js"></script>

<style type="text/css">
body {margin:0;padding:0;font-family:"Times New Roman",Times,serif;font-size:22px;}

#datagrid .tg_body .tg_row {height:100px !important;}
#datagrid .tg_col_c_image {width:100px;height:100px;}

</style>

<script type="text/javascript">

$(function() {

$('#datagrid').tinygrid({
len: 1,
src: {page:'report_data.php'},
cols: [{name:'Image', width:100, ctrlname:'image'},
       {name:'Name', width:"70%",},
       {name:'Cate', width:"30%",},
       {name:'Lowest', width:100,},
       {name:'Highest', width:100,},
       {name:'Date', width:300,},
       ],
});
    
});

</script>

</head>

<body>

<div id="datagrid"></div>


</body>

</html>