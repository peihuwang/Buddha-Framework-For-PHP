<?php
/* Smarty version 3.1.30, created on 2017-08-26 23:17:17
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/account.register.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_59a190fd030568_83842573',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3d1feabd43bd6dacfbee6d783ad085a9d87e0b6e' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/account.register.html',
      1 => 1503729464,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_59a190fd030568_83842573 (Smarty_Internal_Template $_smarty_tpl) {
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
        <li><a href="index.php?a=login&c=account">会员登录</a></li>
        <li class="cur"><a href="index.php?a=register&c=account">注册新账号</a></li>
    </ul>
</div>
<form action="" id="reg">
    <div class="loginr">
        <ul>
            <li style="display:none;"><input type="text" name="reg_username" id="reg_username" placeholder="用户名" value="bendisahngjia"></li>
            <li><input type="tel" name="reg_mobile" id="reg_mobile" placeholder="手机号"></li>
            <li class="Verification"><input type="text" name="reg_yanzheng" id="reg_yanzheng" placeholder="短信验证码">
                <button type="button" class="cover" onclick="tacticsm()">获取验证码</button></li>
            <li><input type="password" name="reg_pwd" id="reg_pwd" placeholder="请输入密码"></li>
            <li><input type="password" name="reg_newpwd" id="reg_newpwd" placeholder="再次输入密码"></li>
            <li class="role">角色选择：<label onclick="shangjia();" class="on"><input type="radio" name="usertype" value="1" checked="checked">商家</label>
                <label onclick="geren();"><input type="radio" name="usertype" value="4">个人</label></li>
        </ul>
    </div>
    <div class="remember"><label><span class="checkbox"><input type="checkbox" class="checked-switch" value="1" name="isok" checked="checked"><b></b></span>我已阅读并同意<em>《用户注册协议》</em></label> <a href="index.php?a=login&c=account">已有账号</a></div>
    <div id="shangjia" style="width: 90%;margin: 5px auto;font-size: 10px;color: #E36A26;">PS: 免费发布各类产品，需求，招聘，租赁，促销，活动，简介，地址，导航，名片，传单等功能。</div>
    <div id="geren" style="display: none;width: 90%;margin: 5px auto;font-size: 10px;color: #E36A26;">PS: 免费发布需求，租赁，求职等。</div>
    <button type="button" class="loginlg" onclick="_user.Reg()">注册</button>
</form>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/user.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
    function shangjia(){
        $('#geren').css('display','none');
        $('#shangjia').css('display','');
    }
    function geren(){
        $('#shangjia').css('display','none');
        $('#geren').css('display','');
    }
<?php echo '</script'; ?>
>
</body>
</html><?php }
}
