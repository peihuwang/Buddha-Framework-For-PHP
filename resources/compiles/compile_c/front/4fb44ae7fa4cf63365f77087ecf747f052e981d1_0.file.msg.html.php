<?php
/* Smarty version 3.1.30, created on 2017-07-07 14:44:11
  from "/home/bendishangjia.com/www/resources/views/templates/public/msg.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_595f2dbba35616_33934767',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4fb44ae7fa4cf63365f77087ecf747f052e981d1' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/public/msg.html',
      1 => 1495845345,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_595f2dbba35616_33934767 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
<title>提示信息</title>
<link href="/manage/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
<link href="/manage/css/animate.min.css" rel="stylesheet">
<link href="/manage/css/style.min.css?v=4.1.0" rel="stylesheet">
</head>
<body>
<div class="wrapper wrapper-content animated fadeInRight">
<div class="panel panel-success">
<div class="panel-heading">提示信息</div>
<div class="panel-body">
<?php echo $_smarty_tpl->tpl_vars['msg']->value;?>

		<p>
<?php if ($_smarty_tpl->tpl_vars['redirect']->value) {?>
<a href="<?php echo $_smarty_tpl->tpl_vars['url']->value;?>
">如果您的浏览器没有自动跳转，请点击这里</a>
<?php echo '<script'; ?>
 type="text/javascript">
	setTimeout("window.location.href ='<?php echo $_smarty_tpl->tpl_vars['url']->value;?>
';", <?php if ($_smarty_tpl->tpl_vars['time']->value) {
echo $_smarty_tpl->tpl_vars['time']->value;
} else { ?>500<?php }?>);
<?php echo '</script'; ?>
>
<?php } else { ?>
<a href="<?php if ($_smarty_tpl->tpl_vars['url']->value) {
echo $_smarty_tpl->tpl_vars['url']->value;
} else { ?>javascript:history.go(-1)<?php }?>">返回继续操作</a>
<?php }?>
</p>
</div>
</div>
</div>
</body>
</html><?php }
}
