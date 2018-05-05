<?php
/* Smarty version 3.1.30, created on 2018-02-03 16:00:28
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/apptoken.add.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a756c1c682150_61662670',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '88eb905b0a05ff47bf4afceb706d1dd9deb01a5e' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/apptoken.add.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a756c1c682150_61662670 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>操作员列表</title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/font-awesome.min.css" rel="stylesheet">
<link href="css/animate.min.css" rel="stylesheet">
<link href="css/style.min.css" rel="stylesheet">
<link href="css/plugins/iCheck/custom.css" rel="stylesheet">
<?php echo '<script'; ?>
 src="js/jquery.js"><?php echo '</script'; ?>
> 
<?php echo '<script'; ?>
 src="js/main.js?v=1.0"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="js/jquery.form.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="js/plugins/iCheck/icheck.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
$(document).ready(function(){$(".i-checks").iCheck({checkboxClass:"icheckbox_square-green",radioClass:"iradio_square-green"})});
<?php echo '</script'; ?>
>
</head>
<body class="gray-bg">
<div class="row wrapper border-bottom white-bg page-heading">
<div class="col-sm-12">
<h3>管理员管理</h3><ol class="breadcrumb"><li>当前位置</li><li>管理员管理</li><li> <strong>令牌修改</strong></li></ol>
</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
<p>
<a href="index.php?a=more&c=apptoken" class="btn btn-primary">返回上一步</a>
</p>

<form method="post" action="" onsubmit="return checkpost(document.checkform);" name="checkform">
<div class="float-e-margins">
<div class="ibox-title"><b>令牌修改</b></div>
</div>
<div class="ibox-content">
<table class="table table-hover m-b-none">
<tbody>


<tr>
	<td>应该名称 <span class="text-danger">*</span></td>
	<td><input type="text"  name="appname" id="appname"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>应用值 <span class="text-danger">*</span></td>
	<td><input type="text"  name="appvalue" id="appvalue"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>密钥 <span class="text-danger">*</span></td>
	<td><input type="text"  name="key" id="key" class="form-control"></td>
	<td></td>
</tr>
<tr>
	
	<td>开始时间 <span class="text-danger">*</span></td>
	<td><input type="text"  name="starttime" id="starttime"  class="form-control"></td>
	<td>}</td>
</tr>
<tr>

<tr>

	<td>结束时间 <span class="text-danger">*</span></td>
	<td><input type="text"  name="endtime" id="endtime"  class="form-control"></td>
	<td></td>
</tr>
<tr>

	<td>允许IP <span class="text-danger">*</span></td>
	<td><input type="text"  name="allowip" id="allowip"  class="form-control"></td>
	<td>* 表示允许一切IP</td>
</tr>
<tr>
<tr>

	<td>有效时间 <span class="text-danger">*</span></td>
	<td><input type="text"  name="duetime" id="duetime"  class="form-control"></td>
	<td>小时</td>
</tr>
<tr>



	<td>统计 <span class="text-danger">*</span></td>
	<td><input type="text"  name="static" id="static"  class="form-control"></td>
	<td></td>
</tr>


</tbody>
</table>
</div>


<div class="text-center m-t-sm">
<button type="submit" class="btn btn-primary">提 交</button>
</div>
</form>
</div>
</div>

</body>
</html>
<?php }
}
