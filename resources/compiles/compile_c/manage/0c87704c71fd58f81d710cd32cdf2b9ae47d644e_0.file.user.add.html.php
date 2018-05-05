<?php
/* Smarty version 3.1.30, created on 2018-02-07 07:00:09
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/user.add.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7a3379c52e73_52803309',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0c87704c71fd58f81d710cd32cdf2b9ae47d644e' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/user.add.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7a3379c52e73_52803309 (Smarty_Internal_Template $_smarty_tpl) {
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
<h3>注册会员</h3><ol class="breadcrumb"><li>当前位置</li><li>注册会员</li><li> <strong>添加会员</strong></li></ol>
</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
<p>
<a href="index.php?a=more&c=user" class="btn btn-primary">返回上一步</a>
</p>

<form method="post" action="" onsubmit="return checkpost(document.checkform);" name="checkform">
<div class="float-e-margins">
<div class="ibox-title"><b>会员信息</b></div>
</div>
<div class="ibox-content">
<table class="table table-hover m-b-none">
<tbody>
<tr>
	<td>用户名 <span class="text-danger">*</span></td>
	<td><input type="text" value="" name="username" id="username"  class="form-control"></td>
	<td>不可修改</td>
</tr>
<tr>
	<td>手机号 <span class="text-danger">*</span></td>
	<td><input type="text" value="" name="mobile" id="mobile" class="form-control"></td>
	<td>不可修改(代理商手机号请填写0)</td>
</tr>
<tr>
	<td>姓名 <span class="text-danger">*</span></td>
	<td><input type="text" value="" name="realname" id="realname"  class="form-control"></td>
	<td></td>
</tr>
<tr>

<tr>
	<td>代理商联系方式 <span class="text-danger">*</span></td>
	<td><input type="text" value="" name="tel" id="tel" class="form-control"></td>
	<td>可以多个会员重复(是代理商则填写 否则请填写0)</td>
</tr>



<td width="120">会员级别<span class="text-danger">*</span></td>
<td width="300"><select name="typeid" id="typeid" class="form-control" onchange="usertype(this.value)">
	<option value="">选择会员级别</option>
	<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['usertype']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
	<option value="<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['item']->value;?>
</option>
	<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

</select></td>
<td></td>
</tr>
<tr>
	
	<td>邮箱</td>
	<td><input type="text" value="" name="email" id="email"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>密码 <span class="text-danger">*</span></td>
	<td><input type="password" value="" name="password" id="password" class="form-control"></td>
	<td></td>
	
</tr>
<tr>
	<td>确认密码 <span class="text-danger">*</span></td>
	<td><input type="password" value="" name="pasw" id="pasw" class="form-control"></td>
	<td></td>
	
</tr>
<tr id="arear"  style="display: none">
	<td>所在区域<span class="text-danger">*</span></td>
	<td colspan="2" id="areararr" class="form-inline">
		<div class="form-group"><select name="level1" id="level1" class="form-control arear">
		<?php echo $_smarty_tpl->tpl_vars['option_list_1']->value;?>

		</select></div>
		<div class="form-group"><select name="level2" id="level2" class="form-control arear" data-value="" ></select></div>
		<div class="form-group"><select name="level3" id="level3" class="form-control arear" data-value="" ></select></div>
	<!--	<div class="form-group"><select name="level4" id="level4" class="form-control arear" data-value="" ></select></div>
		<div class="form-group"><select name="level5" id="level5" class="form-control arear" data-value="" ></select></div></td>-->
</tr>
<tr id="carry" style="display: none">
	<td>提成比例<span class="text-danger">*</span></td>
	<td><input type="text" value="" id="trate" name="agentrate" class="form-control"></td>
	<td>不能加% 只能写数字</td>
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
<?php echo '<script'; ?>
 type="application/javascript" src="js/user.js"><?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
