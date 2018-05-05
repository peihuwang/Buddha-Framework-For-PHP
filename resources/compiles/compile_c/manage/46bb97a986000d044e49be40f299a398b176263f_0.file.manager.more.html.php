<?php
/* Smarty version 3.1.30, created on 2018-02-03 08:33:51
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/manager.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a75036f982b48_63881316',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '46bb97a986000d044e49be40f299a398b176263f' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/manager.more.html',
      1 => 1517617602,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a75036f982b48_63881316 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/localhost.com/vendor/smarty/plugins/modifier.date_format.php';
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>管理员帐号</title>
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
        $(document).ready(function () {
            $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
        });
    <?php echo '</script'; ?>
>
</head>
<body class="gray-bg">
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <h3>系统配置</h3>
        <ol class="breadcrumb">
            <li>当前位置</li>
            <li>系统配置</li>
            <li><strong>操作员列表</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <p><a href="index.php?a=add&c=manager" class="btn btn-primary">添加操作员</a></p>

    <div class="float-e-margins">
        <div class="ibox-title"><b>操作员列表</b>
        </div>
    </div>
    <form name="FORM" method="post" action="">
        <div class="ibox-content">
            <table class="table table-hover m-b-none">
                <thead>
                <tr>
                    <td width="70"><span onclick="CheckAll(document.FORM,'')">全部选择</span></td>
                    <td>操作员编号</td>
                    <td>操作员昵称</td>
                    <td>操作员名称</td>
                    <td>部门</td>
                    <td>手机号</td>
                    <td>邮箱</td>
                    <td>用户状态</td>
                    <td>建立日期</td>
                    <td width="*">操作</td>
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
" name="ids[<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
]" class="i-checks"></td>
                    <td><?php echo $_smarty_tpl->tpl_vars['item']->value['memberid'];?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['item']->value['nickname'];?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['item']->value['username'];?>
</td>
                    <td>
                        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['utypearr']->value, 'utype');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['utype']->value) {
?>
                        <?php if ($_smarty_tpl->tpl_vars['item']->value['utype'] == $_smarty_tpl->tpl_vars['utype']->value['id']) {?>
                        <?php echo $_smarty_tpl->tpl_vars['utype']->value['name'];?>

                        <?php }?>
                        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

                    </td>
                    <td><?php echo $_smarty_tpl->tpl_vars['item']->value['mobile'];?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['item']->value['email'];?>
</td>
                    <td><?php if ($_smarty_tpl->tpl_vars['item']->value['state'] == 0) {?>
                        未审核
                        <?php } elseif ($_smarty_tpl->tpl_vars['item']->value['state'] == 1) {?>
                        未激活
                        <?php } elseif ($_smarty_tpl->tpl_vars['item']->value['state'] == 2) {?>
                        正常
                        <?php } elseif ($_smarty_tpl->tpl_vars['item']->value['state'] == 3) {?>
                        已停用
                        <?php }?>
                    </td>
                    <td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['item']->value['regtime'],"%Y-%m-%d");?>
</td>
                    <td>
                        <a href="index.php?a=edit&c=manager&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
" title="编辑">[编辑]</a>
                        <a href="index.php?a=edit&c=manager&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
&err=4
" title="编辑">[密码重置]</a>
                        <a href="index.php?a=del&c=manager&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
"
                           onClick="return confirm('您确认要删除吗?');">[删除]</a>
                        <a href="index.php?a=edit&c=permissions&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
" title="编辑">[权限列表]</a>
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
            <?php echo $_smarty_tpl->tpl_vars['strPages']->value;?>

        </div>

        <div class="text-center m-t-sm">
            <span>选中操作：</span>
            <input type="radio" value="del" name="job" class="i-checks" checked>删除
        </div>
        <div class="text-center m-t-sm">
            <button onClick="CheckAll(document.FORM,'')" type="button" class="btn btn-success">全 选</button>
            <button onClick="return confirm('您确认要进行此操作吗?');" type="submit" class="btn btn-primary">提 交</button>
        </div>
    </form>

</body>
</html>
<?php }
}
