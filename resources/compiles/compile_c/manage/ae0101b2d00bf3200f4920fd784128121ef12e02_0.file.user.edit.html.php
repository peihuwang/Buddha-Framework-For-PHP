<?php
/* Smarty version 3.1.30, created on 2018-02-08 07:56:21
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/user.edit.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7b9225239b27_87504066',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ae0101b2d00bf3200f4920fd784128121ef12e02' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/user.edit.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7b9225239b27_87504066 (Smarty_Internal_Template $_smarty_tpl) {
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
		<h3>编辑<?php if ($_REQUEST['view'] == 1) {?>商家
			<?php } elseif ($_REQUEST['view'] == 2) {?>代理商
			<?php } elseif ($_REQUEST['view'] == 3) {?>合伙人<?php } else { ?>会员<?php }?></h3><ol class="breadcrumb"><li>当前位置</li><li>会员管理</li><li> <strong>编辑<?php if ($_REQUEST['view'] == 1) {?>商家
		<?php } elseif ($_REQUEST['view'] == 2) {?>代理商
		<?php } elseif ($_REQUEST['view'] == 3) {?>合伙人<?php } else { ?>会员<?php }?></strong></li></ol>
	</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
	<p>
		<a href="index.php?a=more&c=user&view=<?php echo $_REQUEST['view'];?>
" class="btn btn-primary">返回上一步</a>
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
					<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['username'];?>
" class="form-control" id="username" readonly></td>
					<td>不可修改</td>
				</tr>
				<tr>
					<td>手机号 <span class="text-danger">*</span></td>
					<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['mobile'];?>
" name="mobile" id="mobile" class="form-control" readonly></td>
					<td>不可修改</td>
				</tr>
				<tr>
					<td>姓名 <span class="text-danger">*</span></td>
					<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['realname'];?>
"   name="realname" class="form-control" id="realname" ></td>
					<td></td>
				</tr>
				<tr>
					<td>代理商联系方式 <span class="text-danger">*</span></td>
					<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['tel'];?>
" name="tel" id="tel" class="form-control"></td>
					<td>可以多个会员重复(是代理商则填写 否则请填写0)</td>
				</tr>
				<tr>
					<td width="120">会员级别<span class="text-danger">*</span></td>
					<td width="300"><select name="typeid" id="typeid" class="form-control" onchange="usertype(this.value)">
						<option value="">选择会员级别</option>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['usertype']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
						<option value="<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['userinfo']->value['groupid'] == $_smarty_tpl->tpl_vars['key']->value) {?> selected<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value;?>
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
					<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['email'];?>
" name="email" id="email"  class="form-control"></td>
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
				<tr id="arear" style="table-row}">
					<td><?php if ($_smarty_tpl->tpl_vars['userinfo']->value['groupid'] == 2) {?>代理区域<?php }
if ($_smarty_tpl->tpl_vars['userinfo']->value['groupid'] != 2) {?>所在区域<?php }?><span class="text-danger">*</span></td>
					<td colspan="2" id="areararr" class="form-inline">
						<div class="form-group"><select name="level1" id="level1" class="form-control arear" data-value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['level1'];?>
" ><?php echo $_smarty_tpl->tpl_vars['option_list_1']->value;?>
</select></div>
						<div class="form-group"><select name="level2" id="level2" class="form-control arear" data-value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['level2'];?>
" ></select></div>
						<div class="form-group"><select name="level3" id="level3" class="form-control arear" data-value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['level3'];?>
" ></select></div>
						<!--<div class="form-group"><select name="level4" id="level4" class="form-control arear" data-value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['level4'];?>
" ></select></div>
						<div class="form-group"><select name="level5" id="level5" class="form-control arear" data-value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['level5'];?>
" ></select></div>-->
					</td>
				</tr>
				<tr id="carry" style="display:<?php if ($_smarty_tpl->tpl_vars['userinfo']->value['groupid'] == 2 || $_smarty_tpl->tpl_vars['userinfo']->value['groupid'] == 3) {?>table-row<?php } else { ?> none<?php }?>">
					<td>提成比例<span class="text-danger">*</span></td>
					<?php if ($_smarty_tpl->tpl_vars['userinfo']->value['groupid'] == 2) {?>
					<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['agentrate'];?>
" id="trate" name="agentrate" class="form-control"></td>
					<?php } elseif ($_smarty_tpl->tpl_vars['userinfo']->value['groupid'] == 3) {?>
					<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['partnerrate'];?>
" id="trate" name="partnerrate" class="form-control"></td>
					<?php }?>
					<td>不能加% 只能写数字</td>
				</tr>
				</tbody>
			</table>
		</div>
		<input type="hidden" value="<?php echo $_REQUEST['view'];?>
" name="view"/>
		<input type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['userinfo']->value['id'];?>
" id="userid">
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
