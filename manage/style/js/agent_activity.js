$(function(){
    $('.radio-switch').on('click',function(){
        var checked= $(this).val();
        if(checked==4){
            $('#contenteditable').parent().removeClass('hide');
        }else{
            $('#contenteditable').parent().addClass('hide');
        }
       $('#contenteditable').blur(function(){
           var remarks=$(this).html();
          $('[name="remarks"]').html(remarks)
       })
    })
})

$.fn.ajaxshiolist = function(url){
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
            //cache:false,
            success: function (o) {
                $('#showLoading').hide();
                if(o.isok=='true'){
                    var jsonListObj = o.data;
                    insertListDiv(jsonListObj);
                }else{
                    $('.div_null').html(o.data).removeClass('hide');
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
        var c=$('form').data('c');
        var html = '';
        for (var i = 0; i< json.length; i++) {
            html+='<div class="supplyitem" id="lease_'+json[i].id+'" >';
            // html+='<div class="goods_img"><img src="/'+json[i].images+'" alt=""></div>';
            html+='<div class="goods_img"><span class="'+json[i].is_sure+'"></span><img src="'+json[i].images+'"></div>';
            html+='<div class="supply_c">';
            html+='<h2>'+json[i].title+'</h2>';
            html+='<div class="Price">'+json[i].brief+'</div>';
            html+='<div class="goods_sn"><i></i>'+json[i].name+'</div>';
            html+='</div>';
            // html+='<div class="eidt"><b data-href="index.php?a=edit&c=demand&id='+json[i].id+'">审核</b><b>下架</b></div>';
            //////////////////////////////
            html+='<div class="eidt">';//oper
            if(json[i].is_sure == "not" ){
                html+='  <span  data-href="index.php?a=edit&c='+c+'&id='+json[i].id+'">审 核 </span>';
            }else if(json[i].is_sure == "no" ){
                html+='  <span  data-href="index.php?a=edit&c='+c+'&id='+json[i].id+'">审 核 </span>';
            }else if (json[i].is_sure == "yes"){
                html+='  <span class="stop" id="state_'+json[i].id+'" data-u="'+json[i].user_id+'" data-value="'+json[i].id+'" data-a="isdel" data-c="'+c+'">'+json[i].state+'</span>';
            }
            html+='  </div>';
            //////////////////////////////////
            html+='</div>';
        }
        $mainDiv.append(html);
        links()
    }
}

function links(){
    $('.stop').on('click',function(){
    var value=$(this).data('value'),
        a=$(this).data('a'),
        c=$(this).data('c'),
        u=$(this).data('u'),
        txt=$(this).text(),
        title=$('form').data('title');
         s='';
        if(txt=='上 架'){
            s="现在"+title+"为 [下 架] 状态,您确定要 ["+txt+"]"+title+"吗？";
        }else {
            s="现在"+title+"为 [上 架] 状态,您确定要["+txt+"]"+title+"吗？";
        }
        var url='index.php?a='+a+'&c='+c+'&id='+value+'&user_id='+u;
            Message.showConfirm(""+s+"","取消", "确定", function () {
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
        type: 'POST',
        url:url,
        data:{},
        timeout: 90000,
        async: false,
        beforeSend: function() {},
        dataType: 'json',
        success: function(o) {
            if(o.isok=='true'){
                $('#state_'+o.data.id).html(o.data.state);
                Message.showNotify(""+ o.is_msg+"", 2000);
            }else{
                Message.showNotify(""+ o.is_msg+"", 2000);
            }
        },
        complete: function() {},
        error: function() {}
    });
}
function auditor(){
    var url=window.location.href;
    var
        is_sure=$('input:radio[name="is_sure"]:checked').val(),
         remarks=$('textarea[name="remarks"]').val();
       if(is_sure==4){
        if(!remarks){
            Message.showMessage("写入不通过原因!");
            return false
        }
       }
    $('#showLoading').show();
    $.ajax({
        type: "POST",
        url:url,
        data:$('form').serialize(),
        dataType: "json",
        //cache:false,
        success: function (o) {
            if(o.isok=='true'){
                $('#showLoading').hide();
                Message.showNotify(""+ o.data+"", 1500);
                setTimeout("window.location.href='"+o.url+"'",1600);
            }else {
                Message.showNotify(""+ o.data+"", 1500);
                setTimeout("window.location.href='"+o.url+"'",1600);
            }
        },
        beforeSend: function () {
        },
        error: function () {$('#showLoading').hide();
        }
    });
}