<?php
/* Smarty version 3.1.30, created on 2018-02-07 07:00:20
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/user.enetcom.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7a33847311e8_33710093',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2a04691467cc980f70e4f14a7e9e74561dbcc0b4' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/user.enetcom.html',
      1 => 1511946836,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7a33847311e8_33710093 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/localhost.com/vendor/smarty/plugins/modifier.date_format.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>e网通列表</title>
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
        <h3>注册会员</h3><ol class="breadcrumb"><li>当前位置</li><li>注册会员</li><li> <strong>e网通列表</strong></li></ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <p>
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['searchType']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <a href="index.php?a=milist&c=supply&view=<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
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
                    </select>
                </div>
                <div class="form-group"><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['keyword']->value;?>
" name="keyword" id="keyword" class="form-control" placeholder="输入名称" style="width: 300px;"></div>
                <button value="搜 索" type="submit" class="btn btn-danger">搜 索</button>

            </div>
        </form>
    </div>

    <form method="post" action="" onsubmit="return checkpost(document.checkform);"  name="FORM" >
        <div class="float-e-margins">
            <div class="ibox-title"><b>e网通列表</b>
            </div>
        </div>
        <div class="ibox-content ">
            <div class="table-responsive">
                <table class="table table-hover m-b-none">
                    <thead>
                    <tr>
                        <th><span onclick="CheckAll(document.FORM,'')">全选</span></th>
                        <th>用户名</th>
                        <th>付费金额</th>
                        <th>是否支付</th>
                        <th>申请时间</th>
                        <th>到期时间</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                    <tbody>
                    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
                    <tr>
                        <td><input type="checkbox" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" name="ids[<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
]" class="i-checks"/></td>
                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['username'];?>
</td>
                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['money'];?>
</td>
                        <td><?php if ($_smarty_tpl->tpl_vars['item']->value['ispay'] == 0) {?><span class="text-info">未支付</span><?php } else { ?><span
                                class="text-success">已支付</span><?php }?>
                        </td>
                        <td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['item']->value['createtime'],'%Y-%m-%d %H:%M');?>
</td>
                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['endtimestr'];?>
</td>
                        <td><?php if ($_smarty_tpl->tpl_vars['item']->value['is_sure'] == 0) {?>未审核<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['is_sure'] == 1) {?>已审核<?php } else { ?>审核未通过<?php }?></td>
                        <td><a href="index.php?a=enetcomedit&c=user&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
&view=<?php echo $_smarty_tpl->tpl_vars['params']->value['view'];?>
">[审核]</a>
                            <!-- |<a href="index.php?a=enetcomdel&c=user&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
&view=<?php echo $_smarty_tpl->tpl_vars['params']->value['view'];?>
">[删除]</a> -->
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
        </div>

</form>
    <div class="text-center m-t-sm">
        <?php echo $_smarty_tpl->tpl_vars['strPages']->value;?>

    </div>
</div>
<?php echo '<script'; ?>
 type="text/javascript" language="JavaScript">
    function delnav() {
        return confirm("确认要删除此数据吗？");
    }
<?php echo '</script'; ?>
>
</body>
</html><?php }
}
