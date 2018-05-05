<?php
/* Smarty version 3.1.30, created on 2018-02-03 16:00:41
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/payment.edit.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a756c2945b510_86104566',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '45bf05fdee63faa63d1f7172507c2617891454c1' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/payment.edit.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a756c2945b510_86104566 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_html_options')) require_once '/Users/mac/workspace/web/localhost.com/vendor/smarty/plugins/function.html_options.php';
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>支付方式配置</title>
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
        <h3>支付方式配置</h3><ol class="breadcrumb"><li>当前位置</li><li>支付方式管理</li><li> <strong>支付方式配置</strong></li></ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <p class="m-t-sm">
        <a href="index.php?a=more&c=payment" class="btn btn-primary">返回上一步</a>
    </p>
    <form method="post" action=""  onsubmit="return checkpost(document.checkform);" name="checkform" enctype="multipart/form-data">

                <div class="float-e-margins">
                    <div class="ibox-title"><b>基本属性</b></div>
                </div>
                <div class="ibox-content">
                    <table class="table table-hover m-b-none">
                        <tbody>

                        <tr>
                            <td width="120">名称</td>
                            <td width="300"><?php echo $_smarty_tpl->tpl_vars['payInfo']->value['name'];?>

                                <input type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['payInfo']->value['code'];?>
" name="code"/>
                                <input type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['payInfo']->value['name'];?>
" name="name"/></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>状态</td>
                            <td>
                                    <input type="radio"   value="1"  <?php echo $_smarty_tpl->tpl_vars['ifopen_1']->value;?>
  name="ifopen" class="i-checks">开启<input type="radio" value="0"  <?php echo $_smarty_tpl->tpl_vars['ifopen_0']->value;?>
  name="ifopen" class="i-checks">关闭></td>
                            <td></td>
                        </tr>
                        <tr>

                            <?php if ($_smarty_tpl->tpl_vars['payInfo']->value['config']) {?>
                            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['payInfo']->value['config'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>


                            <td><?php echo $_smarty_tpl->tpl_vars['item']->value['text'];?>
</td>
                            <td><?php if ($_smarty_tpl->tpl_vars['item']->value['type'] == 'text') {?>
                                <input type="text" class="form-control" name="config[<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
]" id="txt_title" value="<?php echo $_smarty_tpl->tpl_vars['info']->value[$_smarty_tpl->tpl_vars['key']->value];?>
">
                                <?php } elseif ($_smarty_tpl->tpl_vars['item']->value['type'] == 'select') {?>
                                <select name="config[<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
]"  class="form-control">
                                    <?php echo smarty_function_html_options(array('options'=>$_smarty_tpl->tpl_vars['item']->value['items'],'selected'=>$_smarty_tpl->tpl_vars['info']->value[$_smarty_tpl->tpl_vars['key']->value]),$_smarty_tpl);?>

                                </select>
                                <?php }?></td>
                            <td><?php echo $_smarty_tpl->tpl_vars['item']->value['desc'];?>
</td>
                        </tr>
                        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

                        <?php }?>


                        </tbody>
                    </table>
                </div>


        <div class="text-center m-t-sm">
            <input type="hidden" name="pid" value="<?php echo $_smarty_tpl->tpl_vars['info']->value['payment_id'];?>
" />
            <button type="submit" class="btn btn-primary">提 交</button>
        </div>
    </form>
</div>
</div>



</body>
</html>
<?php }
}
