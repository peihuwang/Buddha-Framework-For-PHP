<?php
/* Smarty version 3.1.30, created on 2018-02-08 09:09:50
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/message.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7ba35eec8eb5_97002476',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '002b5bf3d540155b3549bc37ae39cadd4593f6bd' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/message.more.html',
      1 => 1504520248,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7ba35eec8eb5_97002476 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/localhost.com/vendor/smarty/plugins/modifier.date_format.php';
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>留言管理</title>
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
		<h3>订单管理</h3><ol class="breadcrumb"><li>当前位置</li><li>留言管理</li><li> <strong>列表</strong></li></ol>
	</div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
	<p>
		<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['searchType']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
		<a href="index.php?a=more&c=message&view=<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
" class="btn<?php if ($_smarty_tpl->tpl_vars['params']->value['view'] == $_smarty_tpl->tpl_vars['key']->value) {?> btn-primary<?php } else { ?> btn-danger<?php }?>"><?php echo $_smarty_tpl->tpl_vars['item']->value;?>
</a>
		<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

	</p>
	<div class="panel panel-danger">
		<div class="panel-heading"><b>搜索</b></div>
		<form method="post" action="" class="form-inline">
			<div class="panel-body">
				<div class="form-group">
					<select id="option" name="view" class="form-control">
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['searchType']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
						<option value="<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
"  <?php if ($_smarty_tpl->tpl_vars['params']->value['view'] == $_smarty_tpl->tpl_vars['key']->value) {?> selected<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value;?>
</option>
						<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

					</select>

				</div>
				<div class="form-group"><input type="text" value="" name="keyword" id="keyword" class="form-control" placeholder="请选择方式后输入搜索内容"></div>
				<button value="搜 索" type="submit" class="btn btn-danger" >搜 索</button>
			</div>
		</form>
	</div>
	<div class="float-e-margins">
		<div class="ibox-title"><b>订单列表</b></div>
	</div>

	<form method="post" action="" onsubmit="return checkpost(document.checkform);"  name="FORM" >
		<div class="ibox-content">
			<div class="table-responsive">
				<table class="table table-hover m-b-none">
					<thead>
	<tr>
		<th><span onclick="CheckAll(document.FORM,'')">全选</span></th>
		<th>类别</th>
		<th>是否已读</th>
		<th>标题</th>
		<th>称呼</th>
		<th>联系方式</th>
		<th>电子邮件</th>
		<th>日期</th>
		<th>操作</th>
	</tr>
</thead>
<tbody>
<?php if (count($_smarty_tpl->tpl_vars['list']->value) != 0) {
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
	<tr>
		<td><input type="checkbox" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" name="ids[<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
]" class="i-checks"/></td>

		<td>
			<?php if ($_smarty_tpl->tpl_vars['item']->value['type'] == '1') {?>留言咨询
			<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['type'] == '2') {?>投诉建议
			<?php }?>

		</td>

		<td>
			<?php if ($_smarty_tpl->tpl_vars['item']->value['is_view'] == '1') {?>已读
			<?php } else { ?><font color="red">未读</font>
			<?php }?>

		</td>

		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['title'];?>
</td>
		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['realname'];?>
</td>
		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['mobile'];?>
</td>
		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['email'];?>
</td>
		<td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['item']->value['createtime'],"%Y-%m-%d");?>
</td>
		<td><a href="index.php?a=edit&c=message&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
&type=<?php echo $_smarty_tpl->tpl_vars['view']->value;?>
">[查看]</a></td>
	</tr>
<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

<?php } else { ?>
<tr><td colspan="10">你所查询的数据不存在！<td></td>
<?php }?>
</tbody>
</table>
</div>
</div>
<div class="text-center m-t-sm">
	<?php echo $_smarty_tpl->tpl_vars['strPages']->value;?>

</div>


		<div class="text-center m-t-sm"><span>选中操作：</span>
			<input type="radio" value="is_view" name="job" class="i-checks">已读<input type="radio" value="del" name="job" class="i-checks">删除
		</div>
		<div class="text-center m-t-sm">
			<button type="button" onclick="CheckAll(document.FORM,'')" class="btn btn-primary">全 选</button>
			<button type="submit" class="btn btn-primary">提 交</button>
		</div>
	</form>
</div>

<?php echo '<script'; ?>
 type="text/javascript" language="JavaScript">
	function delnav(){
		return confirm("确认要删除此吗？");
	}
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
