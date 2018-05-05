<?php
/* Smarty version 3.1.30, created on 2017-06-19 15:00:22
  from "/home/bendishangjia.com/www/resources/views/templates/front/account.login.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_59477686cefd51_46321423',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '98496c6d0c403d86c273eca3c301f47a2582e327' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/account.login.html',
      1 => 1497855578,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_59477686cefd51_46321423 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>本地商家</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta content="telephone=no" name="format-detection">
    <link href="/style/css/web.style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="login">
    <ul>
        <?php if ($_REQUEST['forgot']) {?>
        <li class="cur"><a >密码设置成功，进行登录吧!</a></li>
        <?php } else { ?>
        <li class="cur"><a href="index.php?a=login&c=account">会员登录</a></li>
        <li><a href="index.php?a=register&c=account">注册新账号</a></li>
        <?php }?>
    </ul>
</div>
<form action="" id="login">
    <div class="loginr">
        <ul>
            <li><input type="text" name="lg_username" id="lg_username" placeholder="手机号"></li>
            <li><input type="password" name="lg_pwd" id="lg_pwd" placeholder="请输入您的密码"></li>
        </ul>
    </div>
    <div class="remember"><label><span class="checkbox"><input type="checkbox" name="isok" class="checked-switch" value="1" checked="checked"><b></b></span>记住密码</label> <a href="/index.php?a=forgottenpwd&c=account">忘记密码</a></div>
    <button type="button" class="loginlg" onclick="_user.Login()">登录</button>
</form>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/user.js"><?php echo '</script'; ?>
>
</body>
</html><?php }
}
