<?php
/* Smarty version 3.1.30, created on 2018-01-29 21:38:31
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/articlecatalog.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a6f23d7b3db58_32870229',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '980baafb25341a079deb1c972e539d261f9403ea' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/articlecatalog.more.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a6f23d7b3db58_32870229 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>文章管理</title>
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
        <h3>文章管理</h3><ol class="breadcrumb"><li>当前位置</li><li>文章管理</li><li> <strong>文章分类</strong></li></ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <p><a href="index.php?a=add&c=articlecatalog" class="btn btn-primary">添加分类</a></p>
    <div class="float-e-margins">
        <div class="ibox-title"><h5>文章分类</h5>
        </div>
    </div>

    <form method="post" action="" name="FORM" class="form-inline">
        <div class="ibox-content">
            <table class="table table-hover m-b-none" id="cat_tree">
                <thead>
                <tr>
                    <th width="60"><span onclick="CheckAll(document.FORM,'')">全选</span></th>
                    <th >[序号]分类名称</th>
                    <th width="100">是否显示</th>
                    <th width="160">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php echo $_smarty_tpl->tpl_vars['getcatTable']->value;?>

                </tbody>
            </table>
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
