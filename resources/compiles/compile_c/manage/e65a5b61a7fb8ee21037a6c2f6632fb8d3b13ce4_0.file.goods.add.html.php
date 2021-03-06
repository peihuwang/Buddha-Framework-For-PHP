<?php
/* Smarty version 3.1.30, created on 2018-01-14 15:07:07
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/goods.add.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a5b019b788746_21138856',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e65a5b61a7fb8ee21037a6c2f6632fb8d3b13ce4' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/goods.add.html',
      1 => 1515758203,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a5b019b788746_21138856 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加商品</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/animate.min.css" rel="stylesheet">
    <link href="css/style.min.css" rel="stylesheet">
    <link href="css/plugins/iCheck/custom.css" rel="stylesheet">
    <?php echo '<script'; ?>
 src="js/jquery.js"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
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
    <link href="/style/css/quill.snow.css" rel="stylesheet">
    <?php echo '<script'; ?>
 src="/style/js/quill.js"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="/manage/js/goods.js"><?php echo '</script'; ?>
>
    <style type="text/css">
    #Album>#buttonss{display: block; margin: 0; width: 80px; height: 80px; border: 2px solid #ccc; float: left; position: relative; overflow: hidden;}
    #Album>#buttonss::after {content: '';position: absolute;height: 2px;background: #ccc;left: 5px;right: 5px;top: 38px;}
    #Album>#buttonss::before {content: '';position: absolute;width: 2px;background: #ccc;top: 5px;bottom: 5px;left: 38px;}
    #Album>.photo{display: block;width: 80px;height: 80px;overflow: hidden;float: left;margin: 0 5px 2px; border: 2px solid #ccc;text-align: center;line-height: 80px;position: relative;}
    #Album>.photo>input[type="file"]{font-size: 55px;position: absolute;left: 0;top: 0;opacity: 0;}
    </style>
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
        <h3>商城设置</h3><ol class="breadcrumb"><li>当前位置</li><li>商城设置</li><li> <strong>添加商品</strong></li></ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <p class="m-t-sm" id="goods_tabs">
        <span class="btn btn-primary">基本信息</span>
        <a href="index.php?a=lists&c=goods" class="btn btn-danger">返回上一步</a></p>

    <div id="goods_main">
        <div class="goods_tab">
        <div class="float-e-margins">
            <div class="ibox-title"><b>基本信息</b></div>
        </div>
    <form method="post" id="addEditGoodsForm" enctype="multipart/form-data">
            
            <!--通用信息-->
            <div class="tab-content">
              <div class="tab-pane active" id="tab_tongyong">
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <td>商品名称:</td>
                      <td><input type="text" value="" name="goods_name" class="form-control" style="width:550px;"/>
                        <span id="err_goods_name" style="color:#F00; display:none;"></span></td>
                    </tr>
                    
                    <tr>
                      <td>商品分类:</td>
                      <td><div class="col-xs-3" id="catid">
                          <select onchange="getCatInfo();" name="cat_id" id="cat_id" class="form-control" style="width:200px;margin-left:-15px;">
                            <option value="0">请选择商品分类</option>
                            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['goods_cat']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
                            <option value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['item']->value['cat_name'];?>
</option>
                            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

                          </select>
                        </div>
                        <span id="err_cat_id" style="color:#F00; display:none;"></span></td>
                    </tr>
                    <tr>
                        <td>选择上传方式:</td>
                        <td>
                            <input type="radio" value="1" name="is_sure" class="i-checks" >商城自营
                            <input type="radio" value="2" name="is_sure" class="i-checks">积分换购
                            <input type="radio" value="3" name="is_sure" class="i-checks">选择店铺
                        </td>
                    </tr>
                    <tr id="remarks" style="display: none">
                        <td>选择店铺:</td>
                        <td>
                            <span><input type="text" id="shopNum" class="form-control" style="width:350px;" value="" name="shopNum" placeholder="输入店铺编号" onblur="getShopName();"/></span>
                        </td>
                    </tr>
                    <tr>
                        <td>本店售价:</td>
                        <td><input type="text" value="" name="shop_price" class="form-control" style="width:150px;" onkeyup="this.value=this.value.replace(/[^\d.]/g,'')" onpaste="this.value=this.value.replace(/[^\d.]/g,'')" />
                            <span id="err_shop_price" style="color:#F00; display:none;"></span>
                        </td>
                    </tr>
                    <tr>
                        <td>市场价:</td>
                        <td><input type="text" value="" name="market_price" class="form-control" style="width:150px;" onkeyup="this.value=this.value.replace(/[^\d.]/g,'')" onpaste="this.value=this.value.replace(/[^\d.]/g,'')" />
                            <span id="err_market_price" style="color:#F00; display:none;"></span>
                        </td>
                    </tr>
                    <tr>
                        <!-- 商品属性开始-->
                        <td id="attr1" class="yanse">
                            <span>自定义属性：</span>
                        </td>
                        <td id="attr2" class="guige">
                            <span><input type="text" name="guige_0" style="width: 70px;" value="规格"><span style="font-size: 16px;color:red;cursor:pointer" onclick="addInputChi();"> &nbsp; &nbsp;✚ </span></span>
                            <span style="margin-left: 50px;"><input type="text" name="colors_0" style="width: 70px;" value="颜色"><span style="font-size: 16px;color:red;cursor:pointer;" onclick="addInputYan();"> &nbsp; &nbsp;✚ </span></span>
                            
                            <span id="btn" style="margin-left: 50px; width: 50px;">
                                <input type="button" style="width: 50px; height: 30px;background-color: #f60; color: #fff;text-align:center;border-radius: 3px;" value="确认" onclick="determinetwo();">
                            </span>
                        </td>
                        <!--商品属性结束-->
                    </tr>
                    <tr>
                      <td>商品关键词:</td>
                      <td><input type="text" class="form-control" style="width:550px;" value="" name="keywords" placeholder="关键词用空格分开" /></td>
                    </tr>
                    <tr>
                      <td>选择区域:</td>
                      <td><div class="col-xs-3" id="por">
                          <select onchange="por();" name="por_id" id="por_id" class="form-control" style="width:200px;margin-left:-15px;">
                            <option value="0">请选择</option>
                            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['proName']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
                            <option value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</option>
                            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

                          </select>
                        </div>
                        <div class="col-xs-3" id="city">
                          <select onchange="city();"  name="city_id" id="city_id" class="form-control" style="width:200px;margin-left:-15px;">
                            <option value="0">请选择</option>
                          </select>
                        </div>
                        <div class="col-xs-3" id="area">
                          <select name="area_id" id="area_id" class="form-control" style="width:200px;margin-left:-15px;">
                            <option value="0">请选择</option>
                          </select>
                        </div>
                        <span id="err_cat_id" style="color:#F00; display:none;"></span></td>
                    </tr>
                    <tr>
                        <td>是否推荐:</td>
                        <td>
                            <input type="radio" value="1" name="is_rec" class="i-checks" >推荐
                            <input type="radio" value="2" name="is_rec" class="i-checks" >取消推荐
                        </td>
                    </tr>
                    <tr>
                        <td>上传商品图片:</td>
                        <td>
                            <div id="Album">
                                <div id="buttonss"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                      <td>商品简介:</td>
                      <td><textarea id="textareas" rows="5" cols="80" name="goods_remark"></textarea>
                        <span id="err_goods_remark" style="color:#F00; display:none;"></span></td>
                    </tr>
                      <tr>
                          <td>详情</td>
                          <td colspan="2">
                            <div id="editor" name="shopdesc" style="width: 100%;height: 400px;background-color:#f1f3f8;">
                                <p  contenteditable="true" id="contenteditable"></p>
                            </div>
                          </td>
                      </tr>
                      </table>
                  </div>
          </div>
        </div>
        <textarea name="goods_desc" id="goods_desc" class="hide"></textarea>
        </tbody>
        </table>
        <div class="text-center m-t-sm"><button type="button" class="btn btn-primary" onclick="confirms();">确 认</button></div>
    </div>
    </div>
    </form>
<?php echo '<script'; ?>
>
    var usaerfeenum = $('#usaerfeenum').val();
    var toolbarOptions = [
        ['bold', 'underline'],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'align': [] }],
        ['image','video'],
    ]; 
    var quill = new Quill('#editor', {
      modules: {
        toolbar: toolbarOptions
      },
      theme: 'snow'
});
<?php echo '</script'; ?>
>
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
            var is_sure = $(this).siblings('input[type="radio"]:checked').val();
            if(is_sure !=3 ){
                $('#remarks').hide();
            }else{
                $('#remarks').show();
            }
        })
    });
    function por(){
        var por_id = $("#por option:selected").val();
        $.ajax({
            type:'post',
            url:'index.php?a=por&c=goods',
            data:{por_id:por_id},
            dataType:'json',
            success:function(o){
                if(o.isok == 1){
                    $('#city_id').find("option").remove();
                    addhtmls(o.data);
                }
                
            }
        });
    }
    function addhtmls(json){
        var $city_id = $('#city_id');
        html = '';
        for (var i = 0; i< json.length; i++) {
            html +='<option value="'+ json[i].id +'">' + json[i].name + '</option>';
        }
        $city_id.append(html);
    }
    function city(){
        var por_id = $("#city option:selected").val();
        $.ajax({
            type:'post',
            url:'index.php?a=por&c=goods',
            data:{por_id:por_id},
            dataType:'json',
            success:function(o){
                if(o.isok == 1){
                    $('#area_id').find("option").remove();
                    addhtml(o.data);
                }
                
            }
        });
    }
    function addhtml(json){
        var $mainDiv = $('#area_id');
        html = '';
        for (var i = 0; i< json.length; i++) {
            html +='<option value="'+ json[i].id +'">' + json[i].name + '</option>';
        }
        $mainDiv.append(html);
    }
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
//商品属性相关
var i = 1;
var n = 1;
function addInputYan(){//添加颜色框
    if(i>20){
        alert('最多只能添加20项');
        return false;
    }
    //$('#btn').css('display','block');
    $('#attr2').parent("tr").after("<tr><td></td><td id='yanse_"+i+"' class='yanse'><input type='text' name='colors[]' style='width: 70px;' value='' placeholder='象牙白'><i style='font-size: 16px;color:red;cursor:pointer' onclick='delInputYan();'> &nbsp; &nbsp;▬ </i></td></tr>");
    i++;
}
function delInputYan(){//删除颜色框
    i = i-1;
    $('#yanse_'+i).parent("tr").remove();
    $('#yanse_'+i).siblings("td").remove();
    //$('#btn').css('display','block');
}

function delInputYanTwo(id){//删除颜色框
    $('#yanse_'+id).parent("tr").remove();
    $('#yanse_'+id).siblings("td").remove();
    //$('#btn').css('display','block');
}

function addInputChi(){//添加规格框
    if(n>20){
        alert('最多只能添加20项');
        return false;
    }
    //$('#btn').css('display','block');
    $('#attr1').parent("tr").after("<tr><td></td><td id='guige_"+n+"' class='guige'><input type='text' name='guige[]' style='width: 70px;' value='' placeholder='XL'><i style='font-size: 16px;color:red;cursor:pointer' onclick='delInputChi();'> &nbsp; &nbsp;▬ </i></td></tr>");
    n++;
}
function delInputChi(){//删除规格框
    n = n-1;
    $("#guige_"+n).parent("tr").remove();
    $('#guige_'+n).siblings("td").remove();

    //$('#btn').css('display','block');
}
function delInputChiTwo(id){//删除规格框
    $("#guige_"+id).parent("tr").remove();
    $('#guige_'+id).siblings("td").remove();

    //$('#btn').css('display','block');
}


function determinetwo(){//确认生成组合规格表  添加时
    var yansearr = new Array();
    var guigearr = new Array();
    $(function(){
        $("input[name='colors[]']").each(function(i=0){
            yansearr[i] = $(this).val();
            i++;
        });
    });

    $(function(){
        $("input[name='guige[]']").each(function(i=0){
            guigearr[i] = $(this).val();
            i++;
        });
    });
    //var total = yanseNum * guigeNum;//生成的总条数
    $('.zuhe').remove();
    for(var w=0;w<yansearr.length;w++){
        for(var x=0;x<guigearr.length;x++){
            $('#guige_1').parent("tr").after('<tr><td></td><td class="zuhe"><input type="text" name="size[]" style="width: 65px;margin:2px 2px;" value="'+yansearr[w]+'" placeholder="颜色"><input type="text" name="spec[]" style="width: 65px;margin:2px 2px;" value="'+guigearr[x]+'" placeholder="规格"><input type="text" name="pic[]" style="width: 65px;margin:2px 2px;" value="" placeholder="成本"><input type="text" name="profit[]" style="width: 65px;margin:2px 2px;" value="" placeholder="利润"><input type="text" name="stock[]" style="width: 65px;margin:2px 2px;" value="" placeholder="库存"></td></tr>');
        }
    }
    $('.yanse i').css('display','none');
    $('.guige i').css('display','none');
    $('#btn').css('display','none');//隐藏确认按钮
    $('#titles').css('display','none');
}

function determine(){//确认生成组合规格表   编辑时
    var yansearr = new Array();
    var guigearr = new Array();
    $(function(){
        $("input[name='colors[]']").each(function(i=0){
            yansearr[i] = $(this).val();
            i++;
        });
    });

    $(function(){
        $("input[name='guige[]']").each(function(i=0){
            guigearr[i] = $(this).val();
            i++;
        });
    });
    //var total = yanseNum * guigeNum;//生成的总条数
    $('.zuhe').remove();
    if(yansearr.length>0 && guigearr.length>0){
        for(var w=0;w<yansearr.length;w++){
            for(var x=0;x<guigearr.length;x++){
                $('#titles').after('<li class="zuhe"><input type="text" name="size[]" style="width: 65px;margin:2px 2px;" value="'+yansearr[w]+'" placeholder="颜色"><input type="text" name="spec[]" style="width: 65px;margin:2px 2px;" value="'+guigearr[x]+'" placeholder="规格"><input type="text" name="pic[]" style="width: 65px;margin:2px 2px;" value="" placeholder="成本"><input type="text" name="profit[]" style="width: 65px;margin:2px 2px;" value="" placeholder="利润"><input type="text" name="stock[]" style="width: 65px;margin:2px 2px;" value="" placeholder="库存"></li>');
            }
        }
        $('.yanse i').css('display','none');
        $('.guige i').css('display','none');
        $('#btn').css('display','none');//隐藏确认按钮
    }else if(yansearr.length>0 && guigearr.length<=0){
        for(var x=0;x<yansearr.length;x++){
            $('#titles').after('<li class="zuhe"><input type="text" name="size[]" style="width: 65px;margin:2px 2px;" value="'+yansearr[x]+'" placeholder="颜色"><input type="text" name="pic[]" style="width: 65px;margin:2px 2px;" value="" placeholder="成本"><input type="text" name="profit[]" style="width: 65px;margin:2px 2px;" value="" placeholder="利润"><input type="text" name="stock[]" style="width: 65px;margin:2px 2px;" value="" placeholder="库存"></li>');
        }
        $('#attr2').remove();
        $('.yanse i').css('display','none');
        $('.guige i').css('display','none');
        //$('#btn').css('display','none');//隐藏确认按钮
    }else if(guigearr.length>0 && yansearr.length<=0){
        for(var x=0;x<guigearr.length;x++){
            $('#titles').after('<li class="zuhe"><input type="text" name="spec[]" style="width: 65px;margin:2px 2px;" value="'+guigearr[x]+'" placeholder="规格"><input type="text" name="pic[]" style="width: 65px;margin:2px 2px;" value="" placeholder="成本"><input type="text" name="profit[]" style="width: 65px;margin:2px 2px;" value="" placeholder="利润"><input type="text" name="stock[]" style="width: 65px;margin:2px 2px;" value="" placeholder="库存"></li>');
        }
        $('#attr1').remove();
        $('.yanse i').css('display','none');
        $('.guige i').css('display','none');
        //$('#btn').css('display','none');//隐藏确认按钮
    }
}

<?php echo '</script'; ?>
>
</body>
</html><?php }
}
