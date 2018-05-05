<?php
/* Smarty version 3.1.30, created on 2018-02-08 07:21:04
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/singleinformation.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7b89e03ee427_09906518',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'a60b48d5aeb7da57c4abb47f7c1dcbb10fdd0882' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/singleinformation.more.html',
      1 => 1515758203,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7b89e03ee427_09906518 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/localhost.com/vendor/smarty/plugins/modifier.date_format.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>活动列表</title>
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
        <h3>活动管理</h3><ol class="breadcrumb"><li><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
位置</li><li><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
管理</li><li> <strong><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
列表</strong></li></ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <p>
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['searchType']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <a href="index.php?a=more&c=<?php echo $_smarty_tpl->tpl_vars['c']->value;?>
&view=<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
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
            <div class="ibox-title"><b>店铺列表</b>
            </div>
        </div>
        <div class="ibox-content ">
            <div class="table-responsive">
                <table class="table table-hover m-b-none">
                    <thead>
                    <tr>
                        <th><span onclick="CheckAll(document.FORM,'')">全选</span></th>
                        <th>活动名称</th>
                        <th>店铺名</th>
                        <th>是否上架</th>
                        <th>推荐/热门设置</th>
                        <th>省市区</th>
                        <th>活动状态</th>
                        <th>添加时间</th>
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
                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</td>
                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['shop_name'];?>
</td>
                        <td><?php if ($_smarty_tpl->tpl_vars['item']->value['buddhastatus'] == 0) {?><span class="text-info">上架</span><?php } else { ?><span
                                class="text-success">下架</span><?php }?>
                        </td>
                        <td>
                            <?php if ($_smarty_tpl->tpl_vars['item']->value['is_rec'] == 1) {?>
                            推荐
                            <?php }?>
                            <?php if ($_smarty_tpl->tpl_vars['item']->value['is_hot'] == 1) {?>
                            热门
                            <?php }?>

                        </td>
                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['regionale'];?>
</td>
                        <td><?php if ($_smarty_tpl->tpl_vars['item']->value['is_sure'] == 1) {?><span class="text-success">审核通过</span><?php } elseif ($_smarty_tpl->tpl_vars['item']->value['is_sure'] == 0) {?><span
                                class="text-info">待审核</span><?php } elseif ($_smarty_tpl->tpl_vars['item']->value['is_sure'] == 4 && 'state' == 0) {?> <span
                                class="text-warning">审核未通过</span> <?php } elseif ($_smarty_tpl->tpl_vars['item']->value['is_sure'] == 4 && $_smarty_tpl->tpl_vars['item']->value['state'] == 1) {?>
                            <span class="text-danger">已停用</span><?php }?>
                        </td>
                        <td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['item']->value['add_time'],'%Y-%m-%d');?>
</td>
                        <td><a href="index.php?a=edit&c=<?php echo $_smarty_tpl->tpl_vars['c']->value;?>
&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
&view=<?php echo $_smarty_tpl->tpl_vars['params']->value['view'];?>
">[审核]</a>
                            |<a href="index.php?a=del&c=<?php echo $_smarty_tpl->tpl_vars['c']->value;?>
&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
&view=<?php echo $_smarty_tpl->tpl_vars['params']->value['view'];?>
">[删除]</a>
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

        <?php if ($_smarty_tpl->tpl_vars['params']->value['view'] == 2 || $_smarty_tpl->tpl_vars['params']->value['view'] == 3 || $_smarty_tpl->tpl_vars['params']->value['view'] == 4 || $_smarty_tpl->tpl_vars['params']->value['view'] == 5) {?>
        <div class="text-center m-t-sm"><span>选中操作：</span>
            <?php if ($_smarty_tpl->tpl_vars['params']->value['view'] == 2) {?>
            <input type="radio" value="is_sure" name="job" class="i-checks" checked>批量审核
            <?php } elseif ($_smarty_tpl->tpl_vars['params']->value['view'] == 3) {?>
            <input type="radio" value="stop" name="job" class="i-checks" checked>批量下架
            <input type="radio" value="is_hot" name="job" class="i-checks" checked>批量设置热门
            <input type="radio" value="is_rec" name="job" class="i-checks" checked>批量设置推荐
            <?php } elseif ($_smarty_tpl->tpl_vars['params']->value['view'] == 4) {?>
            <input type="radio" value="sure" name="adopt" class="i-checks" checked>批量审核
            <?php } elseif ($_smarty_tpl->tpl_vars['params']->value['view'] == 5) {?>
            <input type="radio" value="enable" name="job" class="i-checks" checked>批量上架
            <?php }?>
        </div>
        <div class="text-center m-t-sm">
            <button type="button" onclick="CheckAll(document.FORM,'')" class="btn btn-primary">全 选</button>
            <button type="submit" class="btn btn-primary">提 交</button>
        </div>
        <?php }?>
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
