$("#demo_input").on("change",function() {
    var file = this.files[0];

    var size=this.files[0].size;
    if(size>1024*1024*5){
        Message.showMessage("图片不能大于5M");
        return false;
    }
    srcs = new FileReader();
    srcs.readAsDataURL(file);
    srcs.onload = function(e) {
        $('#shopz').attr('src',''+this.result+'').show();
        $('#Image ').val(this.result);
    }
});

$('#storetype').change(function () {
    var storetype=$(this).find('option:selected').val();

    if(storetype!=1 && storetype!=5){
        $('.property').fadeIn();
    }else {
        $('.property').hide();
    }
});



var shop={
    add:function(){
      var name = $("#name").val(),
            shopcat = $('#shopcat_id').val(),
            realname = $('#realname').val(),
            mobile = $('#mobile').val(),
            regionstr=$('#regionstr').val(),
            specticloc=$('#specticloc').val(),
            property=$('#property').val(),
            storetype=$('#storetype').find('option:selected').val(),
          Image=$("[name='Image']").val();
        if(!name){
            Message.showMessage('店铺名称不能为空');
            return false;
        };
        if(!shopcat || shopcat==0){
            Message.showMessage('选择店铺所属分类');
            return false;
        };
        if(!realname){
            Message.showMessage('店铺联系人');
            return false;
        };
        if(!mobile){
            Message.showMessage('联系电话');
            return false;
        };
        if(!regionstr){
            Message.showMessage('选择店铺所在区域,以便客户准确的找到您');
            return false;
        };
        if(!specticloc || specticloc==0){
            Message.showMessage('详细地址不能为空');
            return false;
        }
        if(!storetype || storetype==''){
            Message.showMessage('选择店铺性质');
            return false;
        }
        if(storetype!=1 && storetype!=5){
            if(!property){
                Message.showMessage('物业名称不能为空');
                return false;
            }
        }
        if(!Image){
            Message.showMessage('店招不能为空');
            return false;
        }
        var options={
            beforeSubmit: function () {
                $('#showLoading').show();
                $("button[type='button']").text("添加中...").attr("disabled", "disabled");
            },
            success:function(o){
                $('#showLoading').hide();
                if(o.isok=='true'){
                    Message.showNotify(""+ o.data+"", 1500);
                    setTimeout("window.location.href='"+o.url+"'",1600);
                }else{
                    Message.showNotify(''+o.data+'', 1500);
                    setTimeout("window.location.href='"+o.url+"",1600);
                }


            },
        };
        $("#shopForm").ajaxSubmit(options);
        return false;//防止刷新提交
    },
    edit:function(){
        var name = $("#name").val(),
            shopcat = $('#shopcat_id').val(),
            realname = $('#realname').val(),
            mobile = $('#mobile').val(),
            regionstr=$('#regionstr').val(),
            specticloc=$('#specticloc').val(),
            property=$('#property').val(),
            storetype=$('#storetype').find('option:selected').val(),
            Image=$("#shopz").attr('src');

        if(!name){
            Message.showMessage('店铺名称不能为空');
            return false;
        };
        if(!shopcat && shopcat==0){
            Message.showMessage('选择店铺所属分类');
            return false;
        };
        if(!realname){
            Message.showMessage('店铺联系人');
            return false;
        };
        if(!mobile){
            Message.showMessage('联系电话');
            return false;
        };
        if(!specticloc || specticloc==0){
            Message.showMessage('选择店铺所在区域,以便客户准确的找到您');
            return false;
        };
        if(!specticloc){
            Message.showMessage('详细地址不能为空');
            return false;
        }
        if(!storetype || storetype==''){
            Message.showMessage('选择店铺性质');
            return false;
        }
        if(storetype!=1 && storetype!=5){

            if (!property || property==0) {
                Message.showMessage('物业名称不能为空');
                return false;
            }
        }
        if(Image==0){
            Message.showMessage('店招不能为空');
            return false;
        }
        var options={
            beforeSubmit: function () {
                $('#showLoading').show();
                $("button[type='button']").text("编辑中...").attr("disabled", "disabled");
            },
            success:function(o){
                $('#showLoading').hide();
                if(o.isok=='true'){
                    Message.showNotify(""+ o.data+"", 1500);
                    setTimeout("window.location.href='"+o.url+"'",1600);
                }else{
                    Message.showNotify(''+o.data+'', 1500);
                    setTimeout("window.location.href='"+o.url+"",1600);
                }
            },
        };
        $("#shopForm").ajaxSubmit(options);
        return false;//防止刷新提交
    },
};





$.fn.shoplist = function(url){
    var PageSize=15,p=1;
    var scrollHandler = function () {
        var scrollT = $(document).scrollTop(); //滚动条滚动高度
        var pageH = $(document).height();  //滚动条高度
        var winH= $(window).height(); //页面可视区域高度
        var aa = (pageH-winH-scrollT)/winH;
        if(aa<=0.001){
            if(p>=1){
                p++;
            }
            ajaxlist(p,url);
        }
    }
    $(window).scroll(scrollHandler);//执行滚动
    ajaxlist(1,url);//默认加载一页
    function ajaxlist(p,url){
        $('#showLoading').show();
        var act='list';
        $.ajax({
            type: "get",
            url:url,
            data:{PageSize:PageSize,p:p,act:act},
            dataType: "json",
            cache:false,
            success: function (o) {
                $('#showLoading').hide();
                if(o.isok=='true'){
                        var jsonListObj = o.data;
                        insertListDiv(jsonListObj);
                }else{
                    $(".div_null").html(''+o.data+'').show();
                }
            },
            beforeSend: function () {
            },
            error: function () {$('#showLoading').hide();
            }
        });
    }
    //生成数据html,append到div中
    function insertListDiv(json) {
        var $mainDiv = $("#list");
        var html = '';
        for (var i = 0; i< json.length; i++) {
            var state='停用';
            if(json[i].state==0){
                state='启用';
            }
            html+='<div class="item" id="list_'+json[i].id+'">';
            //html+='<div class="itemimg"><img src="/'+json[i].small+'"></div>';
            html+='<div class="itemimg"><span class="'+json[i].is_sure+'"></span><img src="/'+json[i].small+'"></div>';
            html+='<div class="iteminfo">';
            html+='<h1>'+json[i].name+'</h1>';
            html+='<div class="number">编号:'+json[i].number+'</div>';
            html+='<div class="aut">'+json[i].is_verify+'</div>';
            html+='<p>创建时间:'+json[i].createtime+'</p>';
            html+='</div>';
            html+='  <div class="oper">';
            if(json[i].isdel==4){
                html+='店铺被代理商冻结';
            }else {
                html += '<span data-href="index.php?a=edit&c=shop&id=' + json[i].id + '">编辑</span>';
                html += '  <span class="stop" id="state_' + json[i].id + '" data-value="' + json[i].id + '" data-a="state" data-c="shop">' + json[i].state + '</span>';
                if (json[i].is_sure == 4){
                    html += '<span onclick="fail('+json[i].id+')">审核失败</span>';
            }else {
                    html+='<span data-href="/index.php?a=index&c=shop&id='+json[i].id +'">进入店铺</span>'
                }
            html+='  </div>';
            html+=' </div>';
            }
        }
        $mainDiv.append(html);
        links()
    }
}

function links(){
$('.stop,.del').on('click',function(){
    var value=$(this).data('value');
    var a=$(this).data('a');
    var c=$(this).data('c');
    var txt=$(this).text();
    var s=text_1='';
    if(txt=='启 用'){
        s='停用';text_1='启 用';
    }else {
        s='启用';text_1='停用';
    }
    var url='index.php?a='+a+'&c='+c+'&id='+value;
        Message.showConfirm("现在店铺为 ["+s+"] 状态,您确定要 ["+text_1+"] 店铺吗？","取消", "确定", function () {
            console.log('取消');
        }, function () {
            eidtshop(url);
        });

})
$('[data-href]').on('click',function(e){
    var url=$(this).attr('data-href');
    loadurl(url);//加载跳转链接
    return false;
});
}
function eidtshop(url){
    $.ajax({
        type: 'get',
        url:url,
        data:{},
        timeout: 90000,
        async: false,
        beforeSend: function() {},
        dataType: 'json',
        success: function(o) {
            if(o.isok=='true'){
                Message.showNotify(o.data,1000);
                $('#state_'+o.list.id).text(o.list.state);
                // window.location.reload();
            }else{
                Message.showMessage(o.data);
            }
        },
        complete: function() {},
        error: function() {}
    });
}



function fail(id) {
$.get('index.php?a=fail&c=shop',{id:id},function (re) {
    if(re.isok==0){
        Message.showMessage(''+re.remarks+'');
    }else {
        Message.showMessage(''+re.data+'');
    }
})
}

//店铺认证
function verify(id){
Message.showConfirm("店铺认证费为 680元/年，现在认证吗？", "我在想想", "立即认证", function () {
    console.log('不认证');
}, function () {
    $.ajax({
        url:'index.php?a=verifya&c=shop',
        type: 'get',
        data:{id:id},
        timeout: 90000,
        dataType: 'json',
        async: false,
        beforeSend: function() {},
        success: function(o) {
            if(o.isok=='true'){
         window.location.href=''+ o.data+'';
            }else{
                Message.showMessage(o.data)
            }
        },
        complete: function() {},
        error: function() {}
    });
});


}
