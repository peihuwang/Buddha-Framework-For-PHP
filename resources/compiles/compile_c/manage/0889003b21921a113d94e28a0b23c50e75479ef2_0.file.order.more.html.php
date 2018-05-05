<?php
/* Smarty version 3.1.30, created on 2018-01-14 15:05:39
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/order.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a5b0143eb3881_56315252',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0889003b21921a113d94e28a0b23c50e75479ef2' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/order.more.html',
      1 => 1506521597,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a5b0143eb3881_56315252 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/localhost.com/vendor/smarty/plugins/modifier.date_format.php';
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>财富管理</title>
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
		<h3>财富管理</h3><ol class="breadcrumb"><li>当前位置</li><li>财富管理</li><li> <strong>财富列表</strong></li></ol>
	</div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
	<p>
		<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['searchType']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
		<a href="index.php?a=more&c=order&view=<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
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
		<th>订单号</th>
		<th>订单类型</th>
		<th>用户名</th>
		<th>提现人</th>
		<th>提现金额</th>
		<th>账户总额</th>
		<th>提现总额</th>
		<th>手机号</th>
		<th>付款状态</th>
		<th>订单日期</th>
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
		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['order_sn'];?>
</td>
		<td>

			<?php if ($_smarty_tpl->tpl_vars['item']->value['order_type'] == 'money.out') {?>
			会员提现
			<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['order_type'] == 'shop.v') {?>
			店铺认证
			<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['order_type'] == 'info.top') {?>
			信息置顶
			<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['order_type'] == 'info.market') {?>
			跨区域信息推广
			<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['order_type'] == 'info.see') {?>
			信息查看
			<?php }?>
		</td>

		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['username'];?>
</td>
		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['realname'];?>
</td>
		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['final_amt'];?>
</td>
		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['banlance'];?>
</td>
		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['Withamount'];?>
</td>
		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['mobile'];?>
</td>
		<td><?php if ($_smarty_tpl->tpl_vars['item']->value['pay_status'] == 0) {?>
				未支付
			<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['pay_status'] == 1) {?>
				已支付
			<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['pay_status'] == 2) {?>
				已退款
			<?php }?></td>
		<td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['item']->value['createtime'],"%Y-%m-%d");?>
</td>
		<td><a href="index.php?a=edit&c=order&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
&view=<?php echo $_smarty_tpl->tpl_vars['view']->value;?>
">[编辑]</a><a href="" onclick="surepay(<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
,<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
,<?php echo $_smarty_tpl->tpl_vars['view']->value;?>
)">[确认付款]</a></td>
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


<div class="text-center m-t-sm">

</div>
</form>

</div>
<?php echo '<script'; ?>
 type="text/javascript" language="JavaScript">
function delnav(){
	return confirm("确实要删除商品类别吗？");
}
function surepay(order_id,page,view){
	if(confirm('确认付款到用户？')){
		$.ajax({
			type:'post',
			url:'index.php?a=surepay&c=order',
			data:{order_id:order_id,page:page,view:view},
			dataType:'json',
			success:function(o){

			}
		});

	}
}
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
