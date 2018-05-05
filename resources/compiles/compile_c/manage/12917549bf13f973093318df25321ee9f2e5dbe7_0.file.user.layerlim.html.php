<?php
/* Smarty version 3.1.30, created on 2018-02-07 07:00:32
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/user.layerlim.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7a3390b28e01_26704303',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '12917549bf13f973093318df25321ee9f2e5dbe7' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/user.layerlim.html',
      1 => 1512121103,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7a3390b28e01_26704303 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>会员层级关系</title>
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
<style type="text/css">
	.table-set{ border:1px solid #666; color:#666; width:60%; margin:0 auto;}
	.table-set td{text-align: center;height:60px;border-bottom: 1px solid #666;border-right: 1px solid #666;}
	.table-set td img{width: 40px;height: 40px;border-radius: 100%}
</style>
</head>
<body class="gray-bg">
<div class="row wrapper border-bottom white-bg page-heading">
<div class="col-sm-12">
<h3>会员管理</h3><ol class="breadcrumb"><li>当前位置</li><li>会员管理</li><li> <strong>
	会员层级关系</strong></li></ol>
</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
	<div class="panel panel-danger">
		<div class="panel-heading"><b>搜索</b></div>
		<form method="post" action="" class="form-inline" style="text-align: center">
			<div class="panel-body">
				<div class="form-group">
					<select id="option" name="option" class="form-control">
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['searchType']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
						<option value="<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
"  <?php if ($_smarty_tpl->tpl_vars['params']->value['option'] == $_smarty_tpl->tpl_vars['key']->value) {?> selected<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value;?>
</option>
						<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

					</select>

				</div>
				<div class="form-group"><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['keyword']->value;?>
" name="keyword" id="keyword" class="form-control" style="width: 300px;"></div>
				<button value="搜 索" type="submit" class="btn btn-danger">搜 索</button>
			</div>
		</form>
	</div>
<form method="post" action="" onsubmit="return checkpost(document.checkform);"  name="FORM" >
<div class="float-e-margins">
<div class="ibox-title" style="text-align: center;"><b>会员层级关系</b>
</div>
</div>
<div class="ibox-content ">
<div class="table-responsive">
<table class="table-set">
	<?php if (count($_smarty_tpl->tpl_vars['list']->value) != 0) {?>
		<tr>
			<td>头像</td>
			<td>昵称</td>
			<td>手机</td>
			<td>关系</td>
		</tr>
		<tr>
			<td width="15%"><img src="/<?php echo $_smarty_tpl->tpl_vars['list']->value['logo'];?>
"></td>
			<td width="30%"><?php if ($_smarty_tpl->tpl_vars['list']->value['realname']) {
echo $_smarty_tpl->tpl_vars['list']->value['realname'];
} else {
echo $_smarty_tpl->tpl_vars['list']->value['username'];
}?></td>
			<td width="40%"><?php echo $_smarty_tpl->tpl_vars['list']->value['mobile'];?>
</td>
			<td width="15%">上级</td>
		</tr>
		<?php if (count($_smarty_tpl->tpl_vars['userInfos']->value) != 0) {?>
			<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['userInfos']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
			<tr>
				<td><img src="/<?php echo $_smarty_tpl->tpl_vars['item']->value['logo'];?>
"></td>
				<td><?php if ($_smarty_tpl->tpl_vars['item']->value['realname']) {
echo $_smarty_tpl->tpl_vars['item']->value['realname'];
} else {
echo $_smarty_tpl->tpl_vars['item']->value['username'];
}?></td>
				<td><?php echo $_smarty_tpl->tpl_vars['item']->value['mobile'];?>
</td>
				<td>下级</td>
			</tr>
			<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

		<?php } else { ?>
			<tr>
				<td colspan="4">他还没有团队</td>
			</tr>
		<?php }?>
		<?php } else { ?>
			<tr>
				<td colspan="4">请输入手机号或用户名进行搜索</td>
			</tr>
		<?php }?>
</table>
</div>
</div>
</form>
</div>

<link rel="stylesheet" href="js/datetimepicke/datetimepicker.css">
<?php echo '<script'; ?>
 type="text/javascript" src="js/datetimepicke/datetimepicker.full.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
	//日期时间
	$('.some_class').datetimepicker({
		lang: 'ch',
		timepicker: false,
		format: 'Y-m-d',
		//minDate: '-1970/01/02',
	});

	function abcv(){

		var start=$("#start").val();
		var end=$("#end").val();
		var url = 'index.php?a=exporta&c=statistics&t='+Math.random();
		alert(url)
		$.ajax({
			url: url,
			type: 'GET',
			dataType: 'json',
			data: {start:start,end:end},
		})
		.done(function(o) {
		})
		.fail(function() {
		})
		.always(function() {

		});

	}

<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
