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
        var html = '';
        for (var i = 0; i< json.length; i++) {
            html+='<div class="supplyitem" id="lease_'+json[i].id+'" >';
            html+='<div class="goods_img"><img src="/'+json[i].images+'" alt=""></div>';
            html+='<div class="supply_c">';
            html+='<h2>'+json[i].title+'</h2>';
            html+='<div class="Price"><i></i>'+json[i].price+'</div>';
            html+='<div class="goods_sn"><i></i>'+json[i].name+'</div>';
            html+='</div>';
            html+='<div class="eidt"><b data-href="index.php?a=edit&c=supply&id='+json[i].id+'">审核</b><b>下架</b></div>';
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
         s='';
        if(txt=='启 用'){
            s="现在店铺为 [停 用] 状态,您确定要 ["+txt+"] 店铺吗？";
        }else {
           s="现在店铺为 [启 用] 状态,您确定要 ["+txt+"] 店铺吗？</hr>店铺一旦停用,店铺所发信息将被清除到回收站中";
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
            }else{
                Message.showNotify(""+ o.data+"", 1500);
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