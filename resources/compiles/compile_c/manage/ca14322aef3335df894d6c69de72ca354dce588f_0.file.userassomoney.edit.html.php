<?php
/* Smarty version 3.1.30, created on 2018-02-08 07:50:32
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/userassomoney.edit.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7b90c885e2b8_48744340',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ca14322aef3335df894d6c69de72ca354dce588f' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/userassomoney.edit.html',
      1 => 1511946836,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7b90c885e2b8_48744340 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>编辑人脉关系分润</title>
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
$(document).ready(function(){$(".i-checks").iCheck({checkboxClass:"icheckbox_square-green",radioClass:"iradio_square-green"})});
<?php echo '</script'; ?>
>
</head>
<body class="gray-bg">
<div class="row wrapper border-bottom white-bg page-heading">
<div class="col-sm-12">
<h3>编辑人脉关系分润</h3><ol class="breadcrumb"><li>当前位置</li><li>会员管理</li><li> <strong>编辑人脉关系分润</strong></li></ol>
</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
<p>
<a href="index.php?a=edit&c=userassomoney" class="btn btn-primary">返回上一步</a>
</p>

<form method="post" action="" onsubmit="return checkpost(document.checkform);" name="checkform">
<div class="float-e-margins">
<div class="ibox-title"><b>编辑人脉关系分润</b></div>
</div>
<div class="ibox-content">
<table class="table table-hover m-b-none">
<tbody>
<tr>
	<td>分润层级设置为 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Userassomoney']->value['layerlim'];?>
" name="layerlim" id="layerlim"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>上1代(例如父亲分润) <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Userassomoney']->value['layer_money1'];?>
" name="layer_money1" id="layer_money1"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>上2代(例如爷爷分润) <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Userassomoney']->value['layer_money2'];?>
" name="layer_money2" id="layer_money2"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>上3代(例如老爷爷分润) <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Userassomoney']->value['layer_money3'];?>
" name="layer_money3" id="layer_money3"  class="form-control"></td>
	<td></td>
</tr>

<tr>
	<td>上4代 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Userassomoney']->value['layer_money4'];?>
" name="layer_money4" id="layer_money4"  class="form-control"></td>
	<td></td>
</tr>

<tr>
	<td>上5代 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Userassomoney']->value['layer_money5'];?>
" name="layer_money5" id="layer_money5"  class="form-control"></td>
	<td></td>
</tr>

<tr>
	<td>上6代 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Userassomoney']->value['layer_money6'];?>
" name="layer_money6" id="layer_money6"  class="form-control"></td>
	<td></td>
</tr>

<tr>
	<td>上7代 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Userassomoney']->value['layer_money7'];?>
" name="layer_money7" id="layer_money7"  class="form-control"></td>
	<td></td>
</tr>

<tr>
	<td>上8代 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Userassomoney']->value['layer_money8'];?>
" name="layer_money8" id="layer_money8"  class="form-control"></td>
	<td></td>
</tr>


<tr>
	<td>上9代 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Userassomoney']->value['layer_money9'];?>
" name="layer_money9" id="layer_money9"  class="form-control"></td>
	<td></td>
</tr>

<tr>
	<td>上10代 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Db_Userassomoney']->value['layer_money10'];?>
" name="layer_money10" id="layer_money10"  class="form-control"></td>
	<td></td>
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

<link rel="stylesheet" href="js/datetimepicke/datetimepicker.css">
<?php echo '<script'; ?>
 src="js/datetimepicke/datetimepicker.full.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>


	$(function() {
         //日期时间
		$('.some_class').datetimepicker({
			lang: 'ch',
			timepicker: false,
			format: 'Y-m-d',
			minDate: '-1970/01/02',
		});


	})
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
