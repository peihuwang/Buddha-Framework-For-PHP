<?php
/* Smarty version 3.1.30, created on 2018-01-14 15:05:32
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/apptoken.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a5b013c4d4616_94072920',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '66ee19e16a28c5c73dc232ef5e21bfcf5765d1c6' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/apptoken.more.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a5b013c4d4616_94072920 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>令牌管理</title>
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
<h3>系统配置</h3><ol class="breadcrumb"><li>当前位置</li><li>系统配置</li><li> <strong>令牌列表</strong></li></ol>
</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
<p><a href="index.php?a=add&c=apptoken" class="btn btn-primary">添加令牌</a></p>
<div class="float-e-margins">
<div class="ibox-title"><b>令牌列表</b>
</div>
</div>
<form name="FORM" method="post" action="">
<div class="ibox-content">
<table class="table table-hover m-b-none">
    <thead>
    <tr>
       <td>编号</td>
       <td>应该名称</td>
       <td>应用值(appvalue)</td>
       <td>密钥(key)</td>
       <td>允许IP</td>
       <td>统计</td>

      <td width="*">操作</td>
    </tr>
    </thead>
    <tbody>
    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
        <tr>
            <td><?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
</td>
            <td><?php echo $_smarty_tpl->tpl_vars['item']->value['appname'];?>
</td>
            <td><?php echo $_smarty_tpl->tpl_vars['item']->value['appvalue'];?>
</td>
            <td><?php echo $_smarty_tpl->tpl_vars['item']->value['key'];?>
</td>
            <td><?php echo $_smarty_tpl->tpl_vars['item']->value['allowip'];?>
</td>
            <td><?php echo $_smarty_tpl->tpl_vars['item']->value['static'];?>
</td>

            <td>
                <a href="index.php?a=edit&c=apptoken&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
" title="编辑">[编辑]</a>
                <a href="index.php?a=del&c=apptoken&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
" onClick="return confirm('您确认要删除吗?');">[删除]</a>
            </td>
        </tr>
    <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

</tbody>
</table>
</div>


<div class="text-center m-t-sm">
<?php echo $_smarty_tpl->tpl_vars['strPages']->value;?>

</div>

<div class="text-center m-t-sm">
<span>选中操作：</span>
<input type="radio" value="del" name="job" class="i-checks" checked>删除
</div>
<div class="text-center m-t-sm">
<button  type="submit" class="btn btn-primary">提 交</button>
</div>
</form>

</body>
</html>
<?php }
}
