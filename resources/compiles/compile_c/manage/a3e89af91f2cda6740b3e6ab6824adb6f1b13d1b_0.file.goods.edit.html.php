<?php
/* Smarty version 3.1.30, created on 2018-03-12 10:03:54
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/goods.edit.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5aa5e00a5fc466_09271476',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'a3e89af91f2cda6740b3e6ab6824adb6f1b13d1b' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/goods.edit.html',
      1 => 1515758203,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5aa5e00a5fc466_09271476 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/localhost.com/vendor/smarty/plugins/modifier.date_format.php';
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>商品审核</title>
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
    <?php echo $_smarty_tpl->tpl_vars['editorjs']->value;?>

    <?php echo $_smarty_tpl->tpl_vars['editorjstxt']->value;?>

</head>
<body class="gray-bg">
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <h3>商品管理</h3><ol class="breadcrumb"><li>当前位置</li><li>商品管理</li><li> <strong>商品审核</strong></li></ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <p class="m-t-sm" id="goods_tabs">
        <span class="btn btn-primary">基本信息</span>
        <span class="btn btn-danger">商品详情</span>
        <span class="btn btn-danger">商品相册</span>
        <a href="index.php?a=milist&c=supply" class="btn btn-danger">返回上一步</a></p>

    <div id="goods_main">
        <div class="goods_tab">
        <div class="float-e-margins">
            <div class="ibox-title"><b>基本信息</b></div>
        </div>
    <div class="ibox-content  ">
        <div class="table-responsive">
            <table class="table table-hover m-b-none">
                <tr>
                    <td width="120">商品名称</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['Goods']->value['goods_name'];?>
</td>
                </tr>
                <tr>
                    <td width="120">商品编号</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['Goods']->value['goods_sn'];?>
</td>
                </tr>
                <tr>
                    <td width="120">商品分类</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['Goods']->value['cat_name'];?>
</td>
                </tr>
                <tr>
                    <td width="120">发布店铺</td>
                    <td><?php if ($_smarty_tpl->tpl_vars['Goods']->value['shop_name']) {
echo $_smarty_tpl->tpl_vars['Goods']->value['shop_name'];
} else { ?>自营<?php }?></td>
                </tr>
                <tr>
                    <td width="120">所在地区</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['Goods']->value['addres'];?>
</td>
                </tr>
                <tr>
                    <td width="120">数量单位</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['Goods']->value['goods_unit'];?>
</td>
                </tr>
                <tr>
                    <td width="120">销售价格</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['Goods']->value['market_price'];?>
</td>
                </tr>
                <?php if ($_smarty_tpl->tpl_vars['Goods']->value['is_promote'] == 1) {?>
                <tr>
                    <td width="120">促销价格</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['Goods']->value['promote_price'];?>
</td>
                </tr>
                <tr>
                    <td width="120">促销时间</td>
                    <td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['Goods']->value['promote_start_date'],"%Y-%m-%d");?>
至<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['Goods']->value['promote_start_date'],"%Y-%m-%d");?>
</td>
                </tr>
                <?php }?>
                <tr>
                    <td width="120">关键词</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['Goods']->value['keywords'];?>
</td>
                </tr>
                <tr>
                    <td width="120">添加时间</td>
                    <td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['Goods']->value['add_time'],"%Y-%m-%d");?>
</td>
                </tr>
            </table>
        </div>
    </div>
        </div>
        <div class=" goods_tab" style="display: none">
            <div class="float-e-margins">
                <div class="ibox-title"><b>商品详情</b></div>
            </div>
          <div class="ibox-content">
              <div class="table-responsive">
                  <table class="table table-hover m-b-none">
                      <tr>
                          <td width="120">描述</td>
                          <td width="300"><textarea class="form-control" style="height:100px"><?php echo $_smarty_tpl->tpl_vars['Goods']->value['goods_brief'];?>
</textarea></td>
                          <td></td>
                      </tr>
                      <tr>
                          <td>详情</td>
                          <td colspan="2"><?php echo $_smarty_tpl->tpl_vars['editor']->value['content'];?>
</td>
                      </tr>
                      </table>
                  </div>
          </div>
        </div>
        <div class=" goods_tab"  style="display: none">
            <div class="float-e-margins">
                <div class="ibox-title"><b>商品相册</b></div>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['galleryimg']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
                    <div class="col-sm-3 col-md-3 col-lg-2 text-center">
                        <div class="img-thumbnail">
                            <img border="0" src="/<?php echo $_smarty_tpl->tpl_vars['item']->value['goods_thumb'];?>
">
                            </div>
                        </div>
                            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

                </div>

            </div>
        </div>
        </div>
    <div class="float-e-margins m-t-sm">
        <div class="ibox-title"><b>商品信息审核</b>
        </div>
    </div>
    <form  method="post" action="" onsubmit="return checkpost(document.checkform);"  name="checkform" >
        <div class="ibox-content ">
            <div class="table-responsive">
                <table class="table table-hover m-b-none">
                    <?php if ($_smarty_tpl->tpl_vars['Goods']->value['is_sure'] == 1) {?>
                    <tr>
                        <td width="120">上架/下架</td>
                        <td width="300"><label><input type="checkbox" value="1" name="buddhastatus" class="i-checks" <?php if ($_smarty_tpl->tpl_vars['Goods']->value['buddhastatus'] == 0) {?>checked<?php }?>>上架</label></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td width="120">设置商品为热门</td>
                        <td width="300"><label><input type="checkbox" value="1" name="is_hot" class="i-checks" <?php if ($_smarty_tpl->tpl_vars['Goods']->value['is_hot'] == 1) {?>checked<?php }?>>是否设置为热门商品</label></td>
                        <td></td>
                    </tr>
                    <?php }?>
                    <tr>
                        <td width="120">审核</td>
                        <td width="300"><input type="radio" value="1" name="is_sure" class="i-checks" <?php if ($_smarty_tpl->tpl_vars['Goods']->value['is_sure'] == 1) {?>checked<?php }?>>审核通过
                            <input type="radio" value="4" name="is_sure" class="i-checks" <?php if ($_smarty_tpl->tpl_vars['Goods']->value['is_sure'] == 4 || $_smarty_tpl->tpl_vars['Goods']->value['is_sure'] == 0) {?> checked<?php }?>>审核不通过</td>
                        <td></td>
                    </tr>
                    <tr id="remarks" <?php if ($_smarty_tpl->tpl_vars['Goods']->value['is_sure'] == 1) {?> style="display: none"<?php }?>>
                        <td>备注</td>
                        <td><textarea name="remarks" id="remarksval" class="form-control" style="height: 100px;"><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['remarks'];?>
</textarea></td>
                        <td></td>
                    </tr>
                    </table>
                </div>
            </div>
       <div class="text-center m-t-sm"><button type="submit" class="btn btn-primary">审 核</button></div>
    </form>
    </div>
<?php echo '<script'; ?>
>
    $(function(){
        var main=$('#goods_main > .goods_tab');
        $('#goods_tabs span').bind('click', function() {
            var index=$(this).index();
            $(this).attr('class','btn btn-primary').siblings('span').attr('class','btn btn-danger');
            main.eq(index).show().siblings('.goods_tab').hide();
        });

    $('.iCheck-helper').click(function(){
        var is_sure=$(this).siblings('input[type="radio"]:checked ').val();
       if(is_sure!=4){
         $('#remarks').hide()
       }else {
           $('#remarks').show()
       }
    })
    });
function checkpost(){
   var is_sure=$('.iradio_square-green').find('input[type="radio"]:checked ').val(),
           remarksval=$('#remarksval').val();
    if(is_sure!=1){
      if(remarksval==''){
          alert('填写不通过原因！');
          return false;
      }
    }
}
<?php echo '</script'; ?>
>
</body>
</html><?php }
}
