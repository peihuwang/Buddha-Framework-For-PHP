<?php
/* Smarty version 3.1.30, created on 2018-02-08 09:13:27
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/menu.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7ba437655e08_03312199',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '704e7f619921bbc716c5577ed8e8eba900867de9' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/menu.more.html',
      1 => 1517508969,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7ba437655e08_03312199 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>后台权限列表</title>
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
        <h3>菜单管理</h3>
        <ol class="breadcrumb">
            <li>当前位置</li>
            <li>菜单管理</li>
            <li><strong>菜单列表</strong></li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <p><a href="index.php?a=add&c=menu" class="btn btn-primary">添加菜单</a></p>

    <div class="float-e-margins">
        <div class="ibox-title"><b>商品分类</b>
        </div>
    </div>

    <form method="post" action="" name="FORM" class="form-inline">
        <div class="ibox-content">
            <table class="table table-hover m-b-none" id="cat_tree">
                <thead>
                <tr>
                    <th width="60"><span onclick="CheckAll(document.FORM,'')">全选</span></th>
                    <th>菜单名称</th>
                    <th width="150">标示</th>
                    <th>地址</th>
                    <th width="100">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php echo $_smarty_tpl->tpl_vars['menucatTable']->value;?>

        </div>
    </form>
</div>

<?php echo '<script'; ?>
 type="text/javascript" language="JavaScript">
    function delnav() {
        return confirm("确实要删除商品类别吗？");
    }
    function goods_cateopen(id) {
        try {
            var o = $("#cat_tree [pid='" + id + "']");
            var display = '';
            if (o == null) return;
            if (o.css('display') == 'none') {
                o.css('display', '');
                $('#bt_' + id).attr('class', 'fa fa-chevron-down');
            } else {
                o.css('display', 'none');
                display = 'none';
                $('#bt_' + id).attr('class', 'fa fa-chevron-up');
            }
            flode(o, display);
        } catch (e) {
        }
    }
    function flode(o, display) {
        if (o == null) return;
        o.each(function () {
            var cid = $(this).attr('cid');
            var pid = $(this).attr('pid');
            var sub = $("#cat_tree [pid='" + cid + "']");
            sub.css('display', display);
            flode(sub, display);
        });
    }
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
