<?php
/* Smarty version 3.1.30, created on 2018-01-14 15:05:33
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/payment.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a5b013daddca5_98488793',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '993c17e45048f45eb185a61f762beb552d7cd2b5' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/payment.more.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a5b013daddca5_98488793 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>支付方式列表</title>
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
        <h3>支付方式管理</h3><ol class="breadcrumb"><li>当前位置</li><li>支付方式管理</li><li> <strong>支付方式列表</strong></li></ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">



        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-hover m-b-none">
                    <thead>
                    <tr>
                        <th>支付方式名称</th>
                        <th width="40%">支付方式描述</th>
                        <th>启用</th>
                        <th>支持的货币</th>
                        <th>版本</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['payments']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
                    <tr>

                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</td>
                        <td>
                            <?php echo $_smarty_tpl->tpl_vars['item']->value['desc'];?>

                        </td>
                        <td><?php if ($_smarty_tpl->tpl_vars['item']->value['system_enabled'] == '1') {?><span class="s3">开启</span><?php } else { ?>关闭<?php }?></td>
                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['currency'];?>
</td>
                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['version'];?>
</td>
                        <td><?php if ($_smarty_tpl->tpl_vars['item']->value['system_enabled'] == '1') {?>

                            <?php if ($_smarty_tpl->tpl_vars['item']->value['installed'] == '1') {?>
                            <a href="index.php?a=edit&c=payment&code=<?php echo $_smarty_tpl->tpl_vars['item']->value['code'];?>
&pid=<?php echo $_smarty_tpl->tpl_vars['item']->value['payment_id'];?>
">[配置]</a>
                            <a href="index.php?a=del&c=payment&pid=<?php echo $_smarty_tpl->tpl_vars['item']->value['payment_id'];?>
" onClick="if(!confirm('您确认要卸载吗?'))return false;">[卸载]</a>
                            <?php } else { ?>
                            <a href="index.php?a=add&c=payment&code=<?php echo $_smarty_tpl->tpl_vars['item']->value['code'];?>
">[安装]</a>
                            <?php }?>
                            <?php } else { ?>

                            <?php }?>            </td>
                    </tr>
<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

                    </tbody>
                </table>
            </div>
        </div>



</div>
</div>
<?php echo '<script'; ?>
 type="text/javascript" language="JavaScript">
    function delnav(){
        return confirm("您确认进行此操作码？");
    }
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
