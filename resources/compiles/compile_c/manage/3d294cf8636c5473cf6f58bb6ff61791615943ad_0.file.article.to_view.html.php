<?php
/* Smarty version 3.1.30, created on 2018-02-08 07:08:03
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/article.to_view.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7b86d3dbd581_55171879',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3d294cf8636c5473cf6f58bb6ff61791615943ad' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/article.to_view.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7b86d3dbd581_55171879 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>留言详情</title>
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
    <?php echo $_smarty_tpl->tpl_vars['editorjs']->value;?>

    <?php echo $_smarty_tpl->tpl_vars['editorjstxt']->value;?>

</head>
<body class="gray-bg">
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <h3>文章管理</h3><ol class="breadcrumb"><li>当前位置</li><li>文章管理</li><li> <strong>留言详情</strong></li></ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <p class="m-t-sm" id="goods_tabs">
        <a href="index.php?a=message&c=article" class="btn btn-primary">返回上一步</a>
    </p>
        <div id="goods_main">
            <div class="goods_tab">
                <div class="ibox-content">
                    <table class="table table-hover m-b-none">
                        <tbody>
                        <tr>
                            <td width="120">姓名：</span></td>
                            <td><?php echo $_smarty_tpl->tpl_vars['messageInfo']->value['username'];?>
</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td width="120">电话：</span></td>
                            <td><?php echo $_smarty_tpl->tpl_vars['messageInfo']->value['mobile'];?>
</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>留言详情：</td>
                            <td height="200" vertical-align="top"><?php echo $_smarty_tpl->tpl_vars['messageInfo']->value['question'];?>
</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td width="120">所属分类：</span></td>
                            <td width="800"><?php if ($_smarty_tpl->tpl_vars['messageInfo']->value['type'] == '1') {?>建议<?php } elseif ($_smarty_tpl->tpl_vars['messageInfo']->value['type'] == '2') {?>技术<?php } else { ?>其他<?php }?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>留言时间：</td>
                            <td><?php echo $_smarty_tpl->tpl_vars['messageInfo']->value['createtimestr'];?>
</td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php }
}
