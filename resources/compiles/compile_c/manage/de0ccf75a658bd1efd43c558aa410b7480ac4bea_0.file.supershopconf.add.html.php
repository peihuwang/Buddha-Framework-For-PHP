<?php
/* Smarty version 3.1.30, created on 2018-01-14 15:05:50
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/supershopconf.add.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a5b014ea95e47_47561931',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'de0ccf75a658bd1efd43c558aa410b7480ac4bea' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/supershopconf.add.html',
      1 => 1511528433,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a5b014ea95e47_47561931 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>创建无敌店铺</title>
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
<h3>创建无敌店铺</h3><ol class="breadcrumb"><li>当前位置</li><li>无敌软件管理</li><li> <strong>创建无敌店铺</strong></li></ol>
</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
<p>
<a href="index.php?a=more&c=supershopconf" class="btn btn-primary">返回上一步</a>
</p>

<form method="post" action="" onsubmit="return checkpost(document.checkform);" name="checkform">
<div class="float-e-margins">
<div class="ibox-title"><b>创建无敌店铺</b></div>
</div>
<div class="ibox-content">
<table class="table table-hover m-b-none">
<tbody>
<tr>
	<td>应用名 <span class="text-danger">*</span></td>
	<td><input type="text" value="" name="appname" id="appname"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>店铺内码Id <span class="text-danger">*</span></td>
	<td><input type="text" value="" name="shop_id" id="shop_id"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>用户内码Id <span class="text-danger">*</span></td>
	<td><input type="text" value="" name="user_id" id="user_id"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>公匙（相当于账号） <span class="text-danger">*</span></td>
	<td><input type="text" value="" name="appKey" id="appKey" class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>私匙（相当于密码） <span class="text-danger">*</span></td>
	<td><input type="text" value="" name="appSecret" id="appSecret"  class="form-control"></td>
	<td></td>
</tr>
<tr>


<tr>
	<td>应用开始日期</td>
	<td><input type="text" value="" name="starttime" class="form-control state some_class" ></td>
	<td></td>
</tr>

<tr>
	<td>应用结束日期</td>
	<td><input type="text" value="" name="endtime" class="form-control state some_class" ></td>
	<td></td>
</tr>
<tr>
	<td>是否上架</td>
	<td><input type="checkbox" value="1" name="buddhastatus" class="i-checks" checked></td>
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

<link rel="stylesheet" href="js/datetimepicke/datetimepicker.css">
<?php echo '<script'; ?>
 src="js/datetimepicke/datetimepicker.full.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>


	$(function() {
         //日期时间
		$('.some_class').datetimepicker({
			lang: 'ch',
			timepicker: false,
			format: 'Y-m-d',
			minDate: '-1970/01/02',
		});


	})
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
