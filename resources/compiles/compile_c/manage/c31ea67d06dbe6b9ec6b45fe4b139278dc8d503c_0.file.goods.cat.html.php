<?php
/* Smarty version 3.1.30, created on 2018-01-14 15:06:19
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/goods.cat.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a5b016b4ca467_01368365',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c31ea67d06dbe6b9ec6b45fe4b139278dc8d503c' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/goods.cat.html',
      1 => 1511946836,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a5b016b4ca467_01368365 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>商品分类</title>
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
        <h3>商品设置</h3><ol class="breadcrumb"><li>当前位置</li><li>商品设置</li><li> <strong>商品分类</strong></li></ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <p><a href="index.php?a=addcat&c=goods" class="btn btn-primary">添加分类</a></p>
    <form method="post" action="" name="FORM" class="form-inline">
    <div class="ibox-content ">
        <div class="table-responsive">
            <table class="table table-hover m-b-none" id="cat_tree">
                <thead>
                <tr>
                    <th onclick="CheckAll(document.FORM,'')" width="60">全选</th>
                    <th>[顺序]菜单名称</th>
                    <th width="100">是否显示</th>
                    <th width="160">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php echo $_smarty_tpl->tpl_vars['getcatTable']->value;?>

                </tbody>
                </table>
            </div>
        </div>
        <div class="text-center m-t-sm">
            <button type="button" class="btn btn-primary" onclick="CheckAll(document.FORM,'')">全 选</button>
            <button type="submit" class="btn btn-primary">提 交</button>
        </div>
    </form>
    </div>
<?php echo '<script'; ?>
 type="text/javascript" language="JavaScript">
    function delnav(){
        return confirm("确定要删除文章类别吗？");
    }
    function goods_cateopen(id){
        try{
            var o = $("#cat_tree [pid='"+id+"']");
            var display='';
            if (o == null) return;
            if (o.css('display') == 'none') {
                o.css('display','');
                $('#bt_' + id).attr('class', 'fa fa-chevron-down');
            } else {
                o.css('display','none');
                display='none';
                $('#bt_' + id).attr('class','fa fa-chevron-up');
            }
            flode(o,display);
        } catch(e){}
    }
    function flode(o,display){
        if(o == null) return;
        o.each(function(){
            var cid = $(this).attr('cid');
            var pid = $(this).attr('pid');
            var sub = $("#cat_tree [pid='"+cid+"']");
            sub.css('display',display);
            flode(sub,display);
        });
    }
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
