<?php
/* Smarty version 3.1.30, created on 2018-02-08 07:07:44
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/article.message.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7b86c0beabc6_32340331',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f5431227954ced06f7d22f00de8703566fc95227' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/article.message.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7b86c0beabc6_32340331 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>留言列表</title>
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
        <h3>文章管理</h3><ol class="breadcrumb"><li>当前位置</li><li>文章管理</li><li> <strong>留言列表</strong></li></ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="float-e-margins">
        <div class="ibox-title"><b>留言列表</b>
        </div>
    </div>
    <form method="post" action="" name="FORM" class="form-inline">
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-hover m-b-none">
                    <thead>
                    <tr>
                        <!-- <th width="50"><span onclick="CheckAll(document.FORM,'')">全选</span></th> -->
                        <th width="50">序号</th>
                        <th style="width: 60%">留言内容</th>
                        <th width="80">留言者</th>
                        <th width="80">分类</th>
                        <th>时间</th>
                        <th width="120">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
                    <tr>
                        <!-- <td><input type="checkbox" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" name="goodsID[]" class="i-checks"></td> -->
                        <td><input type="text"  value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" name="view_order[<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
]" class="form-control" style="width:60px"></td>
                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['question'];?>
</td>
                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['username'];?>
</td>
                        <td><?php if ($_smarty_tpl->tpl_vars['item']->value['type'] == '1') {?>建议<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['type'] == '2') {?>技术<?php } else { ?>其他<?php }?></td>
                        <td><?php echo $_smarty_tpl->tpl_vars['item']->value['createtimestr'];?>
</td>
                        <td><a href="index.php?a=to_view&c=article&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
">[查看]</a> <a href="index.php?a=mesdel&c=article&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
&p=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
" onclick="return delnav()">[删除]</a></td>
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
        <div class="text-center m-t-sm">
            <?php echo $_smarty_tpl->tpl_vars['strPages']->value;?>

        </div>
    </form>
</div>
</div>
<?php echo '<script'; ?>
 type="text/javascript" language="JavaScript">
    function delnav(){
        return confirm("您确认进行此操作码？");
    }
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
