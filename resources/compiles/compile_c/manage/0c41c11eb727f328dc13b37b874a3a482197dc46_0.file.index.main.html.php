<?php
/* Smarty version 3.1.30, created on 2018-01-30 15:16:20
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/index.main.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a701bc45a8604_36366346',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0c41c11eb727f328dc13b37b874a3a482197dc46' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/index.main.html',
      1 => 1517296578,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a701bc45a8604_36366346 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/localhost.com/vendor/smarty/plugins/modifier.date_format.php';
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>系统欢迎页</title>
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
        $(document).ready(function () {
            $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
        });
    <?php echo '</script'; ?>
>
</head>
<body>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <h3>系统首页</h3>
        <ol class="breadcrumb">
            <li>当前位置</li>
            <li><strong>系统首页</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">


            <!-- 安全提示 -->
            <div class="panel panel-primary">
                <div class="panel-heading">安全提示</div>
                <div class="panel-body">
                    <p>强烈建议您将(bootstrap/Buddha/config/config.ini.php)文件属性设置为644（linux/unix）或只读（NT） </p>

                    <p>强烈建议您使用 data目录隐藏功能，此功能可大大提高网站安全性，设置方法请参照相关帮助文档或联系官方寻求帮助 </p></div>
            </div>
            <!-- 您的资料 -->
            <div class="panel panel-success">
                <div class="panel-heading">您的资料</div>
                <div class="panel-body">
                    <p>用户名：<?php echo $_smarty_tpl->tpl_vars['member']->value['username'];?>
</p>

                    <p>级别：<?php if ($_smarty_tpl->tpl_vars['member']->value['id'] == 1) {?>超级管理员<?php } else {
}?></p>

                    <p>上次登录时间：<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['member']->value['lasttime'],'%Y-%m-%d %H:%M:%S');?>
</p>

                    <p>上次登录IP：<?php echo $_smarty_tpl->tpl_vars['member']->value['loginip'];?>
</p>
                </div>
            </div>
            <!-- 系统信息 -->
            <div class="panel panel-info">
                <div class="panel-heading">系统信息</div>
                <div class="panel-body">
                    <p>服务器软件：<?php echo $_smarty_tpl->tpl_vars['sys']->value['serverSoft'];?>
</p>

                    <p>PHP版本：<?php echo $_smarty_tpl->tpl_vars['sys']->value['PHPVersion'];?>
</p>

                    <p>PHP内存限制: <?php echo $_smarty_tpl->tpl_vars['sys']->value['php_memory_limit'];?>
</p>

                    <p>域名地址：<?php echo $_smarty_tpl->tpl_vars['sys']->value['domain'];?>
</p>
                </div>
            </div>
            <!-- 系统配置 -->
            <div class="panel panel-warning">
                <div class="panel-heading">系统配置</div>
                <div class="panel-body">
                    <p>操作系统：<?php echo $_smarty_tpl->tpl_vars['sys']->value['serverOS'];?>
</p>

                    <p>MySQL版本：5.5.40</p>

                    <p>上传文件：<?php echo $_smarty_tpl->tpl_vars['sys']->value['uploadFile'];?>
</p>

                    <p>最大执行时间:<?php echo $_smarty_tpl->tpl_vars['sys']->value['max_execution_time'];?>
</p>

                    <p>当前脚本占用内存:<?php echo $_smarty_tpl->tpl_vars['sys']->value['current_memory'];?>
</p>

                    <p>服务器时间:<?php echo $_smarty_tpl->tpl_vars['sys']->value['serverTime'];?>
</p>
                </div>
            </div>
            <!--  -->

            <!-- end -->
        </div>

    </div>
</body>
</html>
<?php }
}
