<?php
/* Smarty version 3.1.30, created on 2018-02-06 06:39:56
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/log.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a78dd3c3cf116_26089953',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '098a322a6190f1d38844fed5473bd63f66bae314' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/log.more.html',
      1 => 1517870385,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a78dd3c3cf116_26089953 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/localhost.com/vendor/smarty/plugins/modifier.date_format.php';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>日志列表</title>
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
<h3>系统配置</h3><ol class="breadcrumb"><li>当前位置</li><li>系统配置</li><li> <strong>系统日志</strong></li></ol>
</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
<div class="panel panel-danger">
<div class="panel-heading"><b>搜索</b></div>
<form method="post" action="" class="form-inline" onsubmit="return checkpost(document.checkform);" name="checkform" enctype="multipart/form-data">
<div class="panel-body">
<div class="form-group">
<select id="job" name="job" class="form-control">
<option value="admin" name="job">操作员</option>
</select>
<div class="form-group"><input type="text" id="keys" name="keys"  class="form-control" style="width: 300px"></div>
</div>
<button value="搜 索" type="submit" class="btn btn-primary">搜 索</button>
</div>
</form>
</div>

<div class="float-e-margins">
<div class="ibox-title"><b>系统日志</b>
</div>
</div>
	<form method="post" action="" name="FORM">
<div class="ibox-content">
<table class="table table-hover m-b-none">
<thead>
<tr>
	<th><span onclick="CheckAll(document.FORM,'')">全选</span></th>
	<th>操作员标号</th>
	<th>操作员名称</th>
	<th>操作功能</th>
	<th>操作内容</th>
	<th>原内容</th>
	<th>操作日期</th>
	<th>登录IP</th>
	<th>操作</th>
</tr>
</thead>
<tbody>
<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
<tr>
	<td><input type="checkbox" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
"  name="ids[<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
]" class="i-checks"></td>
	<td><?php echo $_smarty_tpl->tpl_vars['item']->value['uid'];?>
</td>
	<td><?php echo $_smarty_tpl->tpl_vars['item']->value['username'];?>
</td>
	<td><?php echo $_smarty_tpl->tpl_vars['item']->value['operateuse'];?>
</td>
	<td><?php echo $_smarty_tpl->tpl_vars['item']->value['operatedesc'];?>
</td>
	<td><?php echo $_smarty_tpl->tpl_vars['item']->value['operateolddesc'];?>
</td>
	<td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['item']->value['logdate'],"%Y-%m-%d");?>
</td>
	<td><?php echo $_smarty_tpl->tpl_vars['item']->value['ip'];?>
</td>
	<td><a href="index.php?a=edit&c=log&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
">[编辑]</a> <a href="index.php?a=del&c=log&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
">[删除]</a></td>
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

		<div class="text-center m-t-sm"><span>选中操作：
			<input type="radio" name="jb"  value="logsexport"  class="btn btn-primary i-checks" >导出日志
		</div>


	<div class="text-center m-t-sm">
		<button type="button"  onclick="CheckAll(document.FORM,'')" class="btn btn-danger">全选</button>
		<button type="submit" class="btn btn-primary">提 交</button>
	</div>
		</form>
</div>
<?php echo '<script'; ?>
 type="text/javascript">
	function checkpost(){
		if($('#keys').val()==''){
			alert('输入检索关键词');
			$('#keys').focus();
			return false;
		}
	}
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
