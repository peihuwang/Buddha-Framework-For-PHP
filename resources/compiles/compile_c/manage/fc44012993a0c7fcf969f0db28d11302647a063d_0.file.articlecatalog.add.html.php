<?php
/* Smarty version 3.1.30, created on 2018-01-31 14:12:15
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/articlecatalog.add.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a715e3fc10187_26236472',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'fc44012993a0c7fcf969f0db28d11302647a063d' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/articlecatalog.add.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a715e3fc10187_26236472 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加分类</title>
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
        <h3>文章管理</h3><ol class="breadcrumb"><li>当前位置</li><li>文章管理</li><li> <strong>添加分类</strong></li></ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="m-b-sm">
        <a href="index.php?a=more&c=articlecatalog" class="btn btn-primary">返回上一步</a>
    </div>
    <div class="float-e-margins">
        <div class="ibox-title"><b>添加分类</b>
        </div>
    </div>
    <form method="post" action="" onsubmit="return checkpost(document.checkform);" name="checkform" enctype="multipart/form-data">

        <div class="ibox-content">
            <table class="table table-hover m-b-none">
                <tbody>
                <tr>
                    <td width="120">上级分类 </td>
                    <td width="300"><select class="form-control" name="sub">
                        <option value="0">顶级分类</option>
                        <?php echo $_smarty_tpl->tpl_vars['optionList']->value;?>

                    </select></td>
                    <td></td>
                </tr>
                <tr>
                    <td>分类名称 <span class="text-danger">*</span></td>
                    <td><input type="text"  name="name" id="name" class="form-control"></td>
                    <td></td>
                </tr>
                <tr>
                    <td>位置序号</td>
                    <td><input type="text" value="0" name="view_order" class="form-control"></td>
                    <td></td>
                </tr>
                <tr>
                    <td>是否显示</td>
                    <td><input type="checkbox" value="1" name="buddhastatus" class="form-control i-checks" checked></td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="text-center m-t-sm">
            <button type="submit" class="btn btn-primary">提 交</button>
        </div>
    </form>
</div>
</div>
<?php echo '<script'; ?>
>
    function checkpost(obj){

        if($('#name').val()==''){
            alert("输入分类名称");
            $("#goodsname").focus();
            return false;
        }

    }
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
