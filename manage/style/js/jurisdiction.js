var layer_bg=$('.layer_bg'),
    sf_layer=$('.sf_layer'),
    level1='';
var re=/[>]/g;
$(function(){
    $('#region').on('click',function(){
        layer_bg.addClass('show');
        sf_layer.addClass('show');
        if(re.test(level1)){
            var n=level1.match(re).length;
            if(n>=1 && n>=2){
                level1='';
            };
        }
            ajaxregion(1);
    })
    $(layer_bg).on('click',function(){
       $(this).removeClass('show');
        sf_layer.removeClass('show');
    })
    $('.btn_gray').on('click',function(){
        layer_bg.removeClass('show');
        sf_layer.removeClass('show');
    })
});
function ajaxregion(father){
    $('#father').val(father);
    var url = 'index.php?a=arear&c=jurisdiction&t='+Math.random();
    var jsonarr = {'father':father};
    var jsonstr = JSON.stringify(jsonarr);
    $.ajax({
            url:url,
            type:'GET',
            dataType:'json',
            data:{json:jsonstr},
        })
        .done(function(o) {
            if(o.isok=='true'){
                var json=o.data;
                var html='';
                var mydiv=$('.mod_list');
                html+='<ul>';
                for(var i=0;i<json.length;i++){
                    var onclick='',child='';
                    if(json[i].level<=3){
                        if(json[i].immchildnum>0 && json[i].level<=2){
                            child='class="child"'
                        }
                        onclick='onclick=ajaxregion('+json[i].id+')';
                    }else{
                        layer_bg.removeClass('show');
                        sf_layer.removeClass('show');
                    }
                    html+='<li '+onclick+' '+child+'>'+json[i].name+'</li>';
                }
                html+='</ul>';
                html+='</div>';
                mydiv.html(html);
                Choice()
            }
        })
        .fail(function() {
        })
        .always(function() {
        });
}
function Choice(){
$('.mod_list li').on('click',function(){
    var txt=$(this).text();
    if(!level1){
        level1+=txt;
    }else {
        level1+='>'+txt;
    }
})
$('#region').html(level1);
}
var act='',url='';
//业务添加
function addregion(){
    var level4=$('input[name="level4"]').val(),
         level5=$('input[name="level5"]').val(),
         id=$('input[name="id"]').val();
    if(!id){
        Message.showMessage('选择道路所加区域!');
        return false;
    }
    if(!level4){
        Message.showMessage('街道不能为空!');
        return false;
    }
    if(!level5){
        Message.showMessage('路名不能为空!');
        return false;
    }

    $('#showLoading').show();
    act='adderr';
    url='index.php?a=add&c=jurisdiction&act='+act;
$.ajax({
        url:url,
        type:'POST',
        dataType:'json',
        data:$('form').serialize(),
    })
    .done(function(o) {
        if(o.isok=='true'){
            $('#showLoading').hide();
            Message.showNotify("业务区域添加成功", 1000);
            setTimeout(" window.location.reload()",1100);

        }else{
            Message.showMessage('区域错误或所加内容已存在!');
        }
    })
    .fail(function() {
    })
    .always(function() {
        $('#showLoading').hide();
    });
}
function eidtadderr(){
    var level44=$('#level4').val(),
        level55=$('#level5').val();
        if(!level44){
            Message.showMessage('街道不能为空!');
            return false;
        }
        if(!level55){
            Message.showMessage('路名不能为空!');
            return false;
        }
    $('#showLoading').show();
    act='eidtadderr';
    url='index.php?a=eidt&c=jurisdiction&act='+act;
    $.ajax({
            url:url,
            type:'POST',
            dataType:'json',
            data:$('form').serialize(),
        })
        .done(function(txt) {
            if(txt==1){
                window.location.href='../../../index.php';
            }

        })
        .fail(function() {
        })
        .always(function() {
            $('#showLoading').hide();
        });
}



$.fn.jurisdiction = function(url){
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
        var act='list'
        $.ajax({
            type: "get",
            url:url,
            data:{PageSize:PageSize,p:p,act:act},
            dataType: "json",
            cache:false,
            success: function (o) {
                $('#showLoading').hide();
                if(o.isok=='true'){
                    $('#noshop').hide();
                    if (o.data.length > 0){
                        var jsonListObj = o.data;
                        insertListDiv(jsonListObj);
                    }else{
                        $('.div_null').html(o.top).removeClass('hide');
                    }
                }else{
                    $('#noshop').show();
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
         html+='<div class="address_list" data-href="index.php?a=eidt&c=jurisdiction&id='+json[i].endstep+'">'+json[i].roadname+'</div>';
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