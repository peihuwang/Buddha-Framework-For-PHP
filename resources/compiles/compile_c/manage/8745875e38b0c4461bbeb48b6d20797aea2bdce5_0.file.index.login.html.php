<?php
/* Smarty version 3.1.30, created on 2018-01-30 15:01:03
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/index.login.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a70182fc044d3_03135846',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '8745875e38b0c4461bbeb48b6d20797aea2bdce5' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/index.login.html',
      1 => 1517295496,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a70182fc044d3_03135846 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>管理系统 </title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/animate.min.css" rel="stylesheet">
    <link href="css/style.min.css" rel="stylesheet">
    <link href="css/login.min.css" rel="stylesheet">
    <?php echo '<script'; ?>
>
        if (window.top !== window.self) {
            window.top.location = window.location
        }
        ;
    <?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 language="JavaScript">
        if (self.parent.frames.length != 0) {
            self.parent.location = document.location;
        }
    <?php echo '</script'; ?>
>
</head>
<body class="signin">
<div class="logo animated fadeInLeft"></div>
<div class="signinpanel animated fadeInRight">
    <h1>管理系统</h1>

    <div class="loginsup">
        <form class="form-inline" method="post" name="userLogin" action="index.php?a=dologin&c=index">

            <div class="form-group">
                用户名：<input type="text" class="form-control " name="admin_user" placeholder="用户名">
            </div>
            <div class="form-group">
                密户码：<input type="password" class="form-control " name="admin_pw" placeholder="密码">
            </div>
            <div class="form-group">
                验证码：<input type="text" class="form-control " autocomplete="off" name="checkcode" maxlength="6"
                           placeholder="验证码" style="width:70px">
                <img src="/index.php?Services=index.captcha"
                     onclick="this.src='/index.php?Services=index.captcha&t='+Math.random()"
                     style="cursor:pointer; height:30px; width:90px; vertical-align:middle">
            </div>
            <div style="text-align:center">
                <button class="btn btn-danger btn-group" type="submit">登录</button>
                <button class="btn btn-success btn-group" type="reset">重置</button>
            </div>
        </form>
    </div>
    <div class="yingyin"></div>
    <p>版权所有：Buddha Framework</p>
</div>
</body>
</html>
<?php }
}
