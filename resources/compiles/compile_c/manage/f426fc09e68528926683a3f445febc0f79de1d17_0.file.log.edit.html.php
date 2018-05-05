<?php
/* Smarty version 3.1.30, created on 2018-02-06 06:39:52
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/log.edit.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a78dd386d7a75_91377652',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f426fc09e68528926683a3f445febc0f79de1d17' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/log.edit.html',
      1 => 1517870390,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a78dd386d7a75_91377652 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>日志编辑</title>
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
<h3>系统配置</h3><ol class="breadcrumb"><li>当前位置</li><li>系统配置</li><li> <strong>添加日志</strong></li></ol>
</div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
<p><a href="index.php?a=more&c=log" class="btn btn-primary">返回上一步</a></p>
<div class="float-e-margins">
<div class="ibox-title"><b>添加日志</b></div>
</div>
<form method="post" action="" onsubmit="return checkpost(document.checkform);" name="checkform" enctype="multipart/form-data">
<div class="ibox-content">
<table class="table table-hover m-b-none">
<tbody>
<tr>
	<td width="120">操作员编号 </td>
	<td width="300"><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['logedit']->value['uid'];?>
" name="uid" class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>操作员名称 </td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['logedit']->value['username'];?>
" name="username" class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>操作功能</td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['logedit']->value['operateuse'];?>
" name="operateuse"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>操作内容</td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['logedit']->value['operatedesc'];?>
" name="operatedesc"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>原内容</td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['logedit']->value['operateolddesc'];?>
" name="operateolddesc"  class="form-control"></td>
	<td></td>
</tr>
</tbody>
</table>
</div>
<div class="text-center m-t-sm">
<button type="submit" class="btn btn-primary">提 交</button></div>
</form>
</div>

</body>
</html><?php }
}
