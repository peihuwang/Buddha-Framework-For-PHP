<?php
/* Smarty version 3.1.30, created on 2017-08-02 08:03:34
  from "/home/bendishangjia.com/www/resources/views/templates/front/account.forgottenpwd.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_598116d61449d8_82216964',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '73348a955b7cbd638ca9bbf9045ec13b3ed191b3' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/account.forgottenpwd.html',
      1 => 1501323278,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_598116d61449d8_82216964 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div class="login">
    <ul>
        <li><a >找回密码</a></li>

    </ul>
</div>
<form action="" id="forgottenpwdform">
    <div class="loginr">
        <ul>
            <li><input type="tel" name="reg_mobile" id="reg_mobile" placeholder="手机号"></li>
            <li class="Verification"><input type="text" name="reg_yanzheng" id="reg_yanzheng" placeholder="短信验证码">
                <button type="button" class="cover" onclick="tacticsm()">获取验证码</button></li>
            <li><input type="password" name="reg_pwd" id="reg_pwd" placeholder="请输入设置的新密码"></li>
            <li><input type="password" name="reg_newpwd" id="reg_newpwd" placeholder="再次输入设置的新密码"></li>

        </ul>
    </div>
    <button type="button" class="loginlg" onclick="_user.Forgottenpwd()">设置密码</button>
</form>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/user.js"><?php echo '</script'; ?>
>
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
