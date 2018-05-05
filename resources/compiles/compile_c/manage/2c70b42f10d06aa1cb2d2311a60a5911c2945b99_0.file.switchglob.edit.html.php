<?php
/* Smarty version 3.1.30, created on 2018-02-08 09:06:50
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/switchglob.edit.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7ba2aaa92e25_96328619',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2c70b42f10d06aa1cb2d2311a60a5911c2945b99' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/switchglob.edit.html',
      1 => 1517701756,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7ba2aaa92e25_96328619 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>编辑开关全局</title>
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
        $(document).ready(function () {
            $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
        });
    <?php echo '</script'; ?>
>
</head>
<body class="gray-bg">
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <h3>编辑开关全局</h3>
        <ol class="breadcrumb">
            <li>当前位置</li>
            <li>会员管理</li>
            <li><strong>编辑开关全局</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <p>
        <a href="index.php?a=edit&c=userassomoney" class="btn btn-primary">返回上一步</a>
    </p>

    <form method="post" action="" onsubmit="return checkpost(document.checkform);" name="checkform">
        <div class="float-e-margins">
            <div class="ibox-title"><b>编辑开关全局</b></div>
        </div>
        <div class="ibox-content">
            <table class="table table-hover m-b-none">
                <tbody>
                <tr>
                    <td>是否打开商信 <span class="text-danger">*</span></td>
                    <td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Data']->value['is_openworldchat'];?>
" name="is_openworldchat"
                               id="is_openworldchat" class="form-control"></td>
                    <td></td>
                </tr>

                <tr>
                    <td>状态</td>
                    <td>
                        <input type="radio" value="1" <?php if ($_smarty_tpl->tpl_vars['Db_Data']->value['is_openworldchat'] == 1) {?>checked="checked" <?php }?>
                        name="is_openworldchat" class="i-checks">开启
                        <input type="radio" value="0" <?php if ($_smarty_tpl->tpl_vars['Db_Data']->value['is_openworldchat'] == 0) {?>checked="checked" <?php }?>
                        name="is_openworldchat" class="i-checks">关闭
                    <td>
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


</body>
</html>
<?php }
}
