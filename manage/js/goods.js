/*
*  #button   上传图片按钮
*/
//var Num=0;
function getElementsByClassName(n) {//获取class个数的方法
    var classElements = [],allElements = document.getElementsByTagName('div');//所有li
    for (var i=0; i< allElements.length; i++ ){
        if (allElements[i].className == n ) {
           classElements[classElements.length] = allElements[i];
        }
    }
    //alert(classElements.length);
    return classElements.length;
}

var Num = getElementsByClassName("photo");
$(function(){
    $('#buttonss').on('click',function(){//点击一次就将Num的值加一次
        Num++;
        if(Num>6){
            Num=6;
            Message.showMessage("一次最多上传六张");
            return false;
        }
        //before    在 当点击 #button 按钮时在 #button 按钮 的前面加入 加入代码
        //before 在每个匹配的元素之前插入内容。
        $(this).before('<div class="photo"><div class="img">点击上传</div><input type="file" name="Image[]" accept="image/gif,image/jpeg,image/png,image/jpg"></div>');
        upload();

    });
    $(document).on('click', '.del_temp', function(){
        $(this).parent('div').parent('div').remove();
        Num--;
    });
})

function upload(){
    $('input[type="file"]').on("change",function() {
        var file = this.files[0];
        if(!/image\/\w+/.test(file.type)){
            Message.showMessage("请确保文件为图像类型");
            return false;
        }
        var size=this.files[0].size;
        if(size>1024*1024*5){
            Message.showMessage("图片不能大于5M");
            return false;
        }
        var aa= $(this).siblings('.img');
        srcs = new FileReader();
        srcs.readAsDataURL(file);//返回Base64 编码
        srcs.onload = function(e) { // console.log(file);
        aa.html('<span class="del_temp" style="display:block;position:absolute;background:rgba(0,0,0,.5); border-radius:50%; width:20px; height:20px; color:#fff; line-height:20px; text-align: center;z-index:999;">X</span><img style="width:80px;height: 80px;" src="' + srcs.result+ '"/>');
           //$(".photo").find("input").remove();
        }
    });
}

function addInputYan(){//添加颜色框
    var i = 1;
    if(i>20){
        alert('最多只能添加20项');
        return false;
    }
    //$('#btn').css('display','block');
    $('#attr2').parent("tr").after("<tr><td></td><td id='yanse_"+i+"' class='yanse'><input type='text' name='colors[]' style='width: 70px;' value='' placeholder='象牙白'><i style='font-size: 16px;color:red;cursor:pointer' onclick='delInputYan();'> &nbsp; &nbsp;▬ </i></td></tr>");
    i++;
}
function delInputYan(){//删除颜色框
    var i = 1;
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
    var n = 1;
    if(n>20){
        alert('最多只能添加20项');
        return false;
    }
    //$('#btn').css('display','block');
    $('#attr1').parent("tr").after("<tr><td></td><td id='guige_"+n+"' class='guige'><input type='text' name='guige[]' style='width: 70px;' value='' placeholder='XL'><i style='font-size: 16px;color:red;cursor:pointer' onclick='delInputChi();'> &nbsp; &nbsp;▬ </i></td></tr>");
    n++;
}
function delInputChi(){//删除规格框
    var n = 1;
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

//获取二级分类
function getCatInfo(){
    var cat_id = $("#cat_id option:selected").val();
    $.ajax({
        type:'post',
        url:'index.php?a=getCatInfo&c=goods',
        data:{cat_id:cat_id},
        dataType:'JSON',
        success:function(o){
            if(o.isok == 1){
                var jsonListObj = o.data;
                addhtml(jsonListObj);
            }
        }
    });
}

function addhtml(json){
    var $mainDiv = $('#catid');
    html = '';
    html +='<div class="col-xs-3">';
    html +='<select name="cat_id_2" id="cat_id_2" class="form-control" style="width:200px;margin-left:-15px;">';
    for (var i = 0; i< json.length; i++) {
        html +='<option value="'+ json[i].id +'">' + json[i].cat_name + '</option>';
    }
    html +='</select>';
    html +='</div>';
    $mainDiv.after(html);
}

function getShopName(){//根据店铺编号获取店铺名称
    var num = $('#shopNum').val();
    $.ajax({
        type:'post',
        url:'index.php?a=getShopName&c=goods',
        data:{num:num},
        dataType:'JSON',
        success:function(o){
            if(o.isok == 1){
                var txt = '店铺名称：'+o.data.name+' 地址：'+o.data.reg+' 电话：'+o.data.mobile+'<input type="hidden" name="shopid" value="'+o.data.id+'">';
                $('#shopNum').after(txt);
            }
        }
    });
}
function confirms(){
    var goods_name = $("input[name='goods_name']").val(),
    cat_id = $("select[name='cat_id']").val(),
    cat_id_2 = $("input[name='cat_id_2']").val(),
    shop_price = $("input[name='shop_price']").val(),
    market_price = $("input[name='market_price']").val(),
    guige_0 = $("input[name='guige_0']").val(),
    colors_0 = $("input[name='colors_0']").val(),
    colors = $("input[name='colors']").val(),
    guige = $("input[name='guige']").val(),
    size = $("input[name='size']").val(),
    spec = $("input[name='spec']").val(),
    pic = $("input[name='pic']").val(),
    profit = $("input[name='profit']").val(),
    stock = $("input[name='stock']").val(),
    keywords = $("input[name='keywords']").val(),
    is_sure = $("input:radio[name='is_sure']:checked").val(),
    shopid = $("input[name='shopid']").val();
    var goods_remark = $("#textareas").val();
    if(!goods_name){
        Message.showMessage("商品名称不能为空");
        return false;
    }
    if(!cat_id){
        Message.showMessage("请选择类别");
        return false;
    }
    if(!shop_price){
        Message.showMessage("售价不能为空");
        return false;
    }
    if(!market_price){
        Message.showMessage("市场价不能为空");
        return false;
    }
    if(!keywords){
        Message.showMessage("请填写关键字");
        return false;
    }
    if(!is_sure){
        Message.showMessage("请选择商品上传归属");
        return false;
    }
    if(is_sure == 3){
        if(!shopid){
            Message.showMessage("请填写正确的店铺编号");
            return false;
        }
    }
    if(!goods_remark){
        Message.showMessage("商品简介不能为空");
        return false;
    }
    var content = quill.container.firstChild.innerHTML;// 插件
    $('#goods_desc').val(content);
    document.getElementById("addEditGoodsForm").submit();
    return false;
}