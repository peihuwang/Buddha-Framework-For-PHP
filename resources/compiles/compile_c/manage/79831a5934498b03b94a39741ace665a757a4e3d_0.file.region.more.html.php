<?php
/* Smarty version 3.1.30, created on 2018-02-08 07:58:00
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/region.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a7b9288a8b778_74700207',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '79831a5934498b03b94a39741ace665a757a4e3d' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/region.more.html',
      1 => 1518044677,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a7b9288a8b778_74700207 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>地区管理</title>
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
<body>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <h3>区域管理</h3>
        <ol class="breadcrumb">
            <li>当前位置</li>
            <li>区域管理</li>
            <li><strong>区域列表</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="panel panel-success">
        <div class="panel-heading">功能提示</div>
        <div class="panel-body">
            <p>地区请不要随意的<b>添加</b>、 <b>修改</b> 、<b>删除</b>可能会丢失存在的区域。<br>
                默认情况区域信息已是完整的数据，一般无需更改。</p>
        </div>
    </div>
    <form action="" method="post">
        <input type="hidden" name="father" id="father" value="0"/>

        <div class="float-e-margins">
            <div class="ibox-title"><h5>地区管理</h5></div>
            <div class="ibox-content">
                <div id="global_location" class="form-inline">
                    <div class="form-group">
                        <select class="form-control areas" id="areas1"></select>
                    </div>
                    <div class="form-group">
                        <select class=" form-control areas" id="areas2"></select>
                    </div>
                    <div class="form-group">
                        <select class=" form-control areas" id="areas3"></select>
                    </div>
                    <!--  <div class="form-group">
                          <select class=" form-control areas"  id="areas4"></select>
                      </div>
                      <div class="form-group">
                          <select class=" form-control areas"  id="areas5"></select>
                      </div>-->
                </div>
            </div>
            <div class="ibox-content">
                <table class="table table-hover m-b-none">
                    <thead>
                    <tr>
                        <th width="100">ID</th>
                        <th width="200">简称</th>
                        <th width="200">全称</th>
                        <th width="200">拼音</th>
                        <th width="200">经度</th>
                        <th width="200">纬度</th>
                        <th width="*">操作</th>
                    </tr>
                    </thead>
                    <tbody id="createareas">

                    </tbody>
                </table>
                <div id="areaMode" style="display:none">
                    <table>
                        <tbody>
                        <tr>
                            <td><input type="text" class="form-control" name="ids[]" readonly="readonly"/></td>
                            <td><input name="names[]" type="text" class="form-control"/></td>
                            <td><input name="fullname[]" type="text" class="form-control"/></td>
                            <td><input name="pinyin[]" type="text" class="form-control"/></td>
                            <td><input name="lat[]" type="text" class="form-control"/></td>
                            <td><input name="lng[]" type="text" class="form-control"/></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div><a onClick="addArea()" class="btn btn-primary">添加</a></div>
            </div>
        </div>

        <div class="text-center m-t-sm">
            <button type="submit" class="btn btn-primary" id="editAreas">提 交</button>
        </div>
    </form>
</div>
<?php echo '<script'; ?>
 type="application/javascript" src="js/city.js"><?php echo '</script'; ?>
>
</body>
</html><?php }
}
