<?php
/* Smarty version 3.1.30, created on 2018-02-02 01:41:33
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/menu.add.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a73514ddd6be4_07274957',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '7c043ba9dcaf7d5abd9dff73f6fcfb86a5c560aa' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/menu.add.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a73514ddd6be4_07274957 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>无标题文档</title>
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
 type="text/javascript" src="js/jquery.form.js"><?php echo '</script'; ?>
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
<h3>菜单管理</h3><ol class="breadcrumb"><li>当前位置</li><li>菜单管理</li><li> <strong>添加菜单</strong></li></ol>
</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
	<p><a href="index.php?a=more&c=menu" class="btn btn-primary">返回上一步</a></p>
<div class="float-e-margins">
<div class="ibox-title"><b>添加菜单</b>

</div>
</div>
<form method="post" action="" onsubmit="return checkpost(document.checkform);" name="checkform" enctype="multipart/form-data">
<div class="ibox-content">
<table class="table table-hover m-b-none">
<tbody>
<tr>
	<td width="120">上级菜单 <span class="text-danger">*</span></td>
	<td width="300"><select class="form-control" name="sub">
<option value="">顶级菜单</option>
		<?php echo $_smarty_tpl->tpl_vars['menuoption']->value;?>

	</select></td>
<td></td>
</tr>
<tr>
	<td>菜单名称 <span class="text-danger">*</span></td>
	<td><input type="text"  name="name" id="name" class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>页面参数 <span class="text-danger">*</span></td>
	<td><input type="text" value="" name="services" id="services" class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>标示</td>
	<td><input type="text"  name="operator" id="operator" class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>位置序号</td>
	<td><input type="text" value="0" name="sort" class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>是否启用</td>
	<td><input type="checkbox" value="1" name="isopen" class="i-checks" checked></td>
	<td></td>
</tr>
<tr>
	<td>是否左侧列表显示</td>
	<td><input type="checkbox" value="1" name="isdisplay" class="i-checks" checked></td>
	<td></td>
</tr>
</tbody>
</table>
</div>
<div class="text-center m-t-sm">
<button type="submit" class="btn btn-primary">添加</button>
</div>
</form>
</div>
</div>
<?php echo '<script'; ?>
>
function checkpost(obj){

if($('#name').val()==''){
alert("输入分类名称");
$("#name").focus();
return false;
}
	if($('#services').val()==''){
		alert("输入页面参数");
		$("#services").focus();
		return false;
	}
	if($('#operator')==''){
		alert('标示不能为空')
		$('#operator').focus();
		return false;
	}

}
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
