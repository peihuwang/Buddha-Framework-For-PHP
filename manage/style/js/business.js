
var noshop=' <div id="noshop"><h1>您还没有商家店铺！</h1><p>1、您可以<a href="index.php?a=add&c=shop">点这里添加店铺</a><br>2、去<a href="/">首页</a>随便逛逛，看看大家都在发些什么信息</p></div>';
var keyword;
var url=window.location.search;
if(url.indexOf('business')>0){
    ajaxlist(1,url);//默认加载一页
    $('#retrie').on('click',function(){
        keyword= $('#keyword').val();
        if(!keyword){
            Message.showMessage("关键词不能为空");
            return false;
        }
        $('#list').empty();
        ajaxlist(1,url)
    })
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

function ajaxlist(p,url){

    var list='list';
    $('#showLoading').show();
    $.ajax({
        type: "get",
        url:url,
        data:{p:p,list:list,keyword:keyword},
        dataType: "json",
        //cache:false,
        success: function (o) {
            $('#showLoading').hide();
            if(o.isok=='true'){
                $('#noshop').hide();
                if (o.data.length > 0){
                    var jsonListObj = o.data;
                    insertListDiv(jsonListObj);
                }else{
                    $(".div_null").html(''+o.data+'').show();
                    return false;
                }
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
function insertListDiv(json){
    var $mainDiv = $("#list");
    var html='';
    for (var i=0; i<json.length; i++){
        html+='<div class="business_list"> ';
        html+='<div>账号：'+json[i].username+'<span> 时间：'+json[i].onlineregtime+'</span></div>';
        html+='<div>姓名： '+json[i].realname+' <span> 手机：'+json[i].mobile+'</span></div>'
        html+='<div>状态：'+json[i].state+' <span>性质：'+json[i].groupid+'</span></div>';
        html+='</div>';
    }
    $mainDiv.append(html);
    links()
}
}
function links(){
    $('[data-href]').on('click',function(e){
        var url=$(this).attr('data-href');
        loadurl(url);//加载跳转链接
        return false;
    });
}
function state(id){
$.ajax({
    url: 'index.php?a=state&c=shop&id='+id,
    type: 'POST',
    dataType: 'json',
    data: {},
})
.done(function(txt) {
    if(txt==0){
        window.location.reload();
    }
})
.fail(function() {
})
.always(function() {
});
}