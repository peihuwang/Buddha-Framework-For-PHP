<?php
/* Smarty version 3.1.30, created on 2018-02-06 07:37:31
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/apptoken.edit.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a78eabb0b72f4_80035915',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '63c001aab68a21f7befdf091efbd02e3f138f7de' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/apptoken.edit.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a78eabb0b72f4_80035915 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/localhost.com/vendor/smarty/plugins/modifier.date_format.php';
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
<input type="hidden" name="id" value="<?php echo $_smarty_tpl->tpl_vars['optionList']->value['id'];?>
">

<tr>
	<td>应该名称 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['apptokeninfo']->value['appname'];?>
" name="appname" id="appname"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>应用值 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['apptokeninfo']->value['appvalue'];?>
" name="appvalue" id="appvalue"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>密钥 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['apptokeninfo']->value['key'];?>
" name="key" id="key" class="form-control"></td>
	<td></td>
</tr>
<tr>
	
	<td>开始时间 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['apptokeninfo']->value['starttime'];?>
" name="starttime" id="starttime"  class="form-control"></td>
	<td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['apptokeninfo']->value['starttime'],"%Y-%m-%d %H:%M:%S");?>
</td>
</tr>
<tr>

<tr>

	<td>结束时间 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['apptokeninfo']->value['endtime'];?>
" name="endtime" id="endtime"  class="form-control"></td>
	<td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['apptokeninfo']->value['endtime'],"%Y-%m-%d %H:%M:%S");?>
</td>
</tr>
<tr>

	<td>允许IP <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['apptokeninfo']->value['allowip'];?>
" name="allowip" id="allowip"  class="form-control"></td>
	<td>* 表示允许一切IP</td>
</tr>
<tr>
<tr>

	<td>有效时间 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['apptokeninfo']->value['duetime'];?>
" name="duetime" id="duetime"  class="form-control"></td>
	<td>小时</td>
</tr>
<tr>
	<td>统计 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['apptokeninfo']->value['static'];?>
" name="static" id="static"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>状态</td>
	<td><input type="checkbox" value="1" name="buddhastatus" class="i-checks" <?php if ($_smarty_tpl->tpl_vars['apptokeninfo']->value['buddhastatus'] == 1) {?>checked<?php }?>/>
		<?php if ($_smarty_tpl->tpl_vars['apptokeninfo']->value['buddhastatus'] == 0) {?>禁用操作<?php } else { ?>恢复正常<?php }?>
		</td>
	<td>当前状态:<?php if ($_smarty_tpl->tpl_vars['apptokeninfo']->value['buddhastatus'] == 0) {?>正常<?php } else { ?>禁用<?php }?></td>
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
