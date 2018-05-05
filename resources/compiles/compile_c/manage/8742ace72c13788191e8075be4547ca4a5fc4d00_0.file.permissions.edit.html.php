<?php
/* Smarty version 3.1.30, created on 2018-02-02 01:39:55
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/permissions.edit.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7350eb611fe2_96272181',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '8742ace72c13788191e8075be4547ca4a5fc4d00' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/permissions.edit.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7350eb611fe2_96272181 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>无标题文档</title>
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
<body  class="gray-bg">
<div class="row wrapper border-bottom white-bg page-heading">
<div class="col-sm-12">
<h3>系统配置</h3><ol class="breadcrumb"><li>当前位置</li><li>系统配置</li><li> <strong>权限管理</strong></li></ol>
</div>
</div>
	<div class="wrapper wrapper-content animated fadeInRight">
	<div class="float-e-margins">
	<div class="ibox-title"><h5>权限管理</h5>
	</div>
        <p>
            <a href="index.php?a=more&c=manager" class="btn btn-primary">返回上一步</a>
        </p>
	</div>
<form name="FORM" method="post" action="">
<div class="ibox-content">
<table class="table table-hover m-b-none">
<tbody>
<!--<input type="hidden" name="id" value="<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
">
<input type="hidden" name="page" value="<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
">-->
<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
<tr>
<td width="150">
<label><span class="checkbox">
<input type="checkbox" name="action_code[<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
]" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" class="c-checked checkedone"
       <?php if ($_smarty_tpl->tpl_vars['item']->value['state'] == 1) {?> checked <?php }?>
       /><b></b></span><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</label>
</td>
<td>
    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['item']->value['child'], 'val', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['val']->value) {
?>
         <label>
             <span class="checkbox">
             <input type="checkbox"  name="action_code[<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
][<?php echo $_smarty_tpl->tpl_vars['val']->value['id'];?>
]" value="<?php echo $_smarty_tpl->tpl_vars['val']->value['id'];?>
" class="c-checked child"
            <?php if ($_smarty_tpl->tpl_vars['val']->value['state'] == 1) {?> checked <?php }?>
         /><b></b></span> <?php echo $_smarty_tpl->tpl_vars['val']->value['name'];?>
 </label>
    <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

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
<button type="button" onclick="CheckAll(document.FORM,'')" class="btn btn-primary">全 选</button>
<button type="submit" class="btn btn-success">提 交</button>
</div>
</from>
</div>
<?php echo '<script'; ?>
 type="text/javascript">
$(function(){
    $('.checkedone').on('click',function(){
        var isChecked = $(this).prop('checked');
        if(isChecked==true){
            $(this).parent().parent().parent().siblings().find('input[type="checkbox"]').attr('checked','checked')
        }else {
            $(this).parent().parent().parent().siblings().find('input[type="checkbox"]').prop('checked',false)
        }
    })
    $('.child').on('click',function(){
        var isChecked = $(this).parent().parent().parent().siblings().find('.checkedone').prop('checked');

       if(isChecked==false){
           $(this).parent().parent().parent().siblings().find('input[type="checkbox"]').prop('checked','checked')
       }

    })

})



<?php echo '</script'; ?>
>
</body>
</html><?php }
}
