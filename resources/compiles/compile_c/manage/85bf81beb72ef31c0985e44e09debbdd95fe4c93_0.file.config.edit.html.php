<?php
/* Smarty version 3.1.30, created on 2018-01-14 09:13:46
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/config.edit.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a5aaeca3dbe95_65992754',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '85bf81beb72ef31c0985e44e09debbdd95fe4c93' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/config.edit.html',
      1 => 1504074100,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a5aaeca3dbe95_65992754 (Smarty_Internal_Template $_smarty_tpl) {
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
<h3>系统配置</h3><ol class="breadcrumb"><li>当前位置</li><li>系统配置</li><li> <strong>选项配置</strong></li></ol>
</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
<div class="float-e-margins">
<div class="ibox-title"><h5>选项配置</h5>
</div>
	<p>
		<a href="index.php?a=more&c=manager" class="btn btn-primary">返回上一步</a>
	</p>
</div>
<form method="post" action="" onsubmit="return checkpost(document.checkform);" name="checkform" enctype="multipart/form-data">
<div class="ibox-content">
<table class="table table-hover m-b-none">
<tbody>
<tr>
<td width="150">短信发送账号：</td>
<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['config']->value['hsk_smsAccount'];?>
" name="config[hsk_smsAccount]"  class="form-control"></td>
<td></td>
</tr>
<tr>
<td>短信发送密码：</td>
<td><input type="password" value="<?php echo $_smarty_tpl->tpl_vars['config']->value['hsk_smsPassword'];?>
" name="config[hsk_smsPassword]"  class="form-control"></td>
<td></td>
</tr>
<tr>
<td>短信发送接口地址：</td>
<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['config']->value['hsk_smsForwordAddress'];?>
" name="config[hsk_smsForwordAddress]"  class="form-control"></td>
<td></td>
</tr>
<tr>
<td>邮件发送接口账号：</td>
<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['config']->value['hsk_smtpuser'];?>
" name="config[hsk_smtpuser]"  class="form-control"></td>
<td></td>
</tr>
<tr>
<td>邮件发送接口密码：</td>
<td><input type="password" value="<?php echo $_smarty_tpl->tpl_vars['config']->value['hsk_smtppw'];?>
" name="config[hsk_smtppw]"  class="form-control"></td>
<td></td>
</tr>
<tr>
<td>邮件发送SMTP端口：</td>
<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['config']->value['hsk_smtpport'];?>
" name="config[hsk_smtpport]"  class="form-control"></td>
<td></td>
</tr>
<tr>
	<td>邮件SMTP主机地址：</td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['config']->value['hsk_smtphost'];?>
" name="config[hsk_smtphost]"  class="form-control"></td>
	<td></td>
</tr>


<tr>
	<td>底部版权</td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['config']->value['hsk_siteCopyRight'];?>
" name="config[hsk_siteCopyRight]"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>店铺认证</td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['config']->value['hsk_is_shop_needverify'];?>
" name="config[hsk_is_shop_needverify]"  class="form-control" placeholder="0表示免费，1是收费"></td>
	<td></td>
</tr>
<tr>
	<td>认证资料</td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['config']->value['hsk_is_shop_verify_zl'];?>
" name="config[hsk_is_shop_verify_zl]"  class="form-control" placeholder="0表示不上传，1表示必须上传"></td>
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
</body>
</html>
<?php }
}
