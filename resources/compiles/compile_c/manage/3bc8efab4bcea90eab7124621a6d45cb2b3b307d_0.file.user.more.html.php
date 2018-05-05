<?php
/* Smarty version 3.1.30, created on 2018-02-10 07:36:10
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/user.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7e306a9c40c8_13645243',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3bc8efab4bcea90eab7124621a6d45cb2b3b307d' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/user.more.html',
      1 => 1518219368,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7e306a9c40c8_13645243 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/localhost.com/vendor/smarty/plugins/modifier.date_format.php';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>会员列表</title>
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
<h3>会员管理</h3><ol class="breadcrumb"><li>当前位置</li><li>会员管理</li><li> <strong>
	普通会员 列表</strong></li></ol>
</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">

<p>
	<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['usertype']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
	<a href="index.php?a=more&c=user&view=<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
" class="btn<?php if ($_smarty_tpl->tpl_vars['params']->value['view'] == $_smarty_tpl->tpl_vars['key']->value) {?> btn-primary<?php } else { ?> btn-danger<?php }?>"><?php echo $_smarty_tpl->tpl_vars['item']->value;?>
</a>
	<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

	<a href="index.php?a=add&c=user" class="btn btn-danger">添加会员</a>

</p>
	<div class="panel panel-danger">
		<div class="panel-heading"><b>搜索</b></div>
		<form method="post" action="" class="form-inline">
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

				录入店铺数检索条件：
					<div class="form-group">
						<input type="text" name="start" id="start" value="<?php echo $_smarty_tpl->tpl_vars['start']->value;?>
" class="form-control some_class" placeholder="起始时间" readonly style="cursor:pointer; background: none">
					</div> —
					<div class="form-group"><input type="text" name="end" id="end" value="<?php echo $_smarty_tpl->tpl_vars['end']->value;?>
" class="form-control some_class" placeholder="结束时间" readonly style="cursor:pointer; background: none"></div>

				<button value="搜 索" type="submit" class="btn btn-danger">搜 索</button>
			</div>
		</form>
	</div>
<form method="post" action="" onsubmit="return checkpost(document.checkform);"  name="FORM" >
<div class="float-e-margins">
<div class="ibox-title"><b>会员列表</b>
</div>
</div>
<div class="ibox-content ">
<div class="table-responsive">
<table class="table table-hover m-b-none">

<thead>
<tr>
     <th><span onclick="CheckAll(document.FORM,'')">全选</span></th>
	<th>用户名</th>
	<th>会员级别</th>
	<th>会员姓名</th>

	<th>手机号</th>
	<th>会员状态</th>

	<th>注册时间</th>
	<?php if ($_smarty_tpl->tpl_vars['utype']->value == 1) {?>
	<th>操作</th>
	<?php }?>
</tr>
<tbody>
<?php if ($_smarty_tpl->tpl_vars['list']->value) {
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list']->value, 'item', false, NULL, 'list', array (
));
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
<tr>
	<td><input type="checkbox" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" name="ids[<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
]" class="i-checks"/></td>
	<td><?php echo $_smarty_tpl->tpl_vars['item']->value['username'];?>
</td>
	<td><?php if ($_smarty_tpl->tpl_vars['item']->value['groupid'] == 1) {?>商家<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['groupid'] == 2) {?>代理商<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['groupid'] == 3) {?>合伙人<?php } else { ?>普通会员<?php }?></td>
	<td><?php echo $_smarty_tpl->tpl_vars['item']->value['realname'];?>
</td>
		<td><?php echo $_smarty_tpl->tpl_vars['item']->value['mobile'];?>
</td>
	<td><?php if ($_smarty_tpl->tpl_vars['item']->value['state'] == 1) {?>正常<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['state'] == 0) {?>停用<?php } else { ?>注销<?php }?></td>
	
	<td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['item']->value['onlineregtime'],'%Y-%m-%d');?>
</td>
	<?php if ($_smarty_tpl->tpl_vars['utype']->value == 1) {?>
	<td><a href="index.php?a=edit&c=user&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
&view=<?php echo $_smarty_tpl->tpl_vars['item']->value['groupid'];?>
">[编辑]</a> <?php if ($_smarty_tpl->tpl_vars['dels']->value == 1) {?><a href="index.php?a=del&c=user&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
&view=<?php echo $_smarty_tpl->tpl_vars['item']->value['groupid'];?>
" onclick="delnav()">[删除]</a><?php }?></td>
	<?php }?>
</tr>
<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

<?php } else { ?>
<tr>
	<td class="text-center" colspan="11">暂无数据</td>
</tr>
<?php }?>
</tbody>

</table>
</div>
</div>
<div class="text-center m-t-sm">
<?php echo $_smarty_tpl->tpl_vars['strPages']->value;?>

</div>
<div class="text-center m-t-sm"><span>选中操作：</span>
<input type="radio" value="" name="job" class="i-checks">停用
<input type="radio" name="jb"  value="<?php echo $_smarty_tpl->tpl_vars['groupid']->value;?>
"  class="btn btn-primary i-checks" >导出
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
		return confirm("确认要删除此会员吗？");
	}
<?php echo '</script'; ?>
>


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
