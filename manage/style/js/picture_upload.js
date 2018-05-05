var Catalog;
var url=window.location.pathname.split('/');
Catalog=url[1];
var srcs,logo;
$(function(){
    $("input[type=file]").on("change",function() {
        var size = this.files[0].size;
        if (size > 1024*1024*2) {
            Message.showMessage("用户头像不大于2M正方形");
            return false;
        }
        var file=this.files[0];
        srcs = new FileReader();
        srcs.readAsDataURL(file);
        srcs.onload = function(e) {
           logo= this.result;
            logoimg(logo);
        }
    })
})
function logoimg(){
    $('#showLoading').show();
$.ajax({
        url: 'index.php?a=logo&c='+Catalog,
        type: 'POST',
        dataType:'json',
        data: {logo:logo},
    })
    .done(function(o) {
        $('#showLoading').hide();
        if(o.isok=='true'){
         $('.usericon').find('img').attr('src','/'+o.data);
        }else {
            Message.showMessage(o.data);
        }
    })
    .fail(function() {

    })
    .always(function() {
        $('#showLoading').hide();
    });
}

var layer_bg=$('.layer_bg'),
    sf_layer=$('.sf_layer');
$(function(){
$('#info .before').on('click',function(){
    var htmlid=$(this).find('div').attr('class');
  if(htmlid){
    var title=$(this).find('span').text();
    var str=$('#'+htmlid).html();
    layer_bg.addClass('show');
    sf_layer.addClass('show').width('100%').html(str);
    $('#user-basic').find('h1').html(title.substring(0,title.length-1))
  }
  if(htmlid=='gender'){
      var val=$(this).find('div').text();
      if(val=='保密'){
          $('.eidtgender li').eq(0).find('label').append('<span>√</span>');
          $('.eidtgender li').eq(0).find('input[type="radio"]').attr('checked','checked');
      }else if(val=='男'){
          $('.eidtgender li').eq(1).find('label').append('<span>√</span>');
          $('.eidtgender li').eq(1).find('input[type="radio"]').attr('checked','checked');
      }else if(val=='女'){
          $('.eidtgender li').eq(2).find('label').append('<span>√</span>');
          $('.eidtgender li').eq(2).find('input[type="radio"]').attr('checked','checked');
      }
      $('.eidtgender>ul>li').on('click',function(){
          $(this).find('label').append('<span>√</span>').parent().siblings().find('span').remove();
      })
  }
    $('.return').on('click',function(){
        $('.layer_bg').removeClass('show');
        $('.sf_layer').removeClass('show')
    })
})

})
var text;
var mobileExp= new RegExp("^[1][3-8]\\d{9}$"),
    passwordExp=new RegExp("^[a-zA-Z0-9]{6,20}$");
var newpwd_isok = 0;
var vmobile_isok = 0;
var code_isok = 0;
function eidt(txt){
if(txt=='tel'){
    text=$('input[type="text"]').val();
    if(!text){
        Message.showMessage('什么都没填,不能提交');
        return false;
    }
    eidtuser(txt,text);
}
if(txt=='address'){
    text=$('input[type="text"]').val();
    if(!text){
        Message.showMessage('什么都没填,不能提交');
        return false;
    }
    eidtuser(txt,text);
}
if(txt=='gender'){
var radio = $('input:radio[name="gender"]:checked').val();
if (radio==null) {
    Message.showMessage('什么都没选,不能提交');
    return false;
}
eidtuser(txt,radio)
}
if(txt=='realname'){
    var realname = $('input[name="realname"]').val();
    if (!realname) {
        Message.showMessage('什么都没填,不能提交');
        return false;
    }
    eidtuser(txt,realname)
}

if(txt=='editpassword'){
    var mobile = $('input[name="mobile"]').val();
    if (!mobile) {
        Message.showMessage('输入手机号');
        return false;
    }
    var yanzheng = $('input[name="Code"]').val();
    if (!yanzheng) {
        Message.showMessage('输入验证码');
        return false;
    }
    verify(mobile,yanzheng);
    if (!code_isok) {
        return false;
    }
    var password = $('input[name="password"]').val();
    var psd = $('input[name="psd"]').val();
    chknewpwd(password);
    if (!newpwd_isok) {
        return false;
    }

    /*if (/^\d+$/.test(password) || /^[a-zA-Z]+$/.test(password)) {
        Message.showMessage("建议使用英文字母+数字组合");
        return false;
    }*/
    if (password != psd) {
        Message.showMessage("两次密码填写不一致!");
        return false;
    }

    eidtuser(txt, password)
}
}

function eidtCity(){
    var number = $('#getcity').val() +','+ $('#getarea').val() +','+ $('#getareass').val();
    if(number == '0,0,0'){
        Message.showMessage('什么都没填,不能提交');
        return false;
    }else{
        $.ajax({
            type: 'POST',
            url:'index.php?a=exitCity&c='+Catalog,
            data:{fid:number},
            timeout: 90000,
            async: false,
            beforeSend: function() {},
            dataType: 'json',
            success: function(o) {
                if (o.isok == 'true') {
                    $('.'+number).html(o.data);
                    $('.layer_bg').removeClass('show');
                    $('.sf_layer').removeClass('show');
                    location.reload();
                } else {
                    Message.showMessage(o.data);
                }
            },
            complete: function() {},
            error: function() {}
        });
    }
}

function eidtuser(txt,text){
    var url = 'index.php?a='+txt+'&c='+Catalog+'&t='+Math.random();
    $.ajax({
        type: 'get',
        url:url,
        data:{fid:text},
        timeout: 90000,
        async: false,
        beforeSend: function() {},
        dataType: 'json',
        success: function(o) {
            if (o.isok == 'true') {
                $('.'+txt).html(o.data);
                $('.layer_bg').removeClass('show');
                $('.sf_layer').removeClass('show');
                location.reload();
            } else {
                Message.showMessage(o.data);
            }
        },
        complete: function() {},
        error: function() {}
    });
}

function chknewpwd(n){
    if(!n){
        Message.showMessage("请输入密码！");
        newpwd_isok = 0;
    }else{
        if(this.passwordExp.test(n)){
            newpwd_isok = 1;
        }else{
            Message.showMessage("您输入的密码格式不正确！（6-20字母或数字）");
            newpwd_isok = 0;
            return false;
        }
    }
}

function chkmobile(n){
    if(!n){
        vmobile_isok=0;
    }else {
        if(this.mobileExp.test(n)){
            var url = 'index.php?a=verifyrequest&c='+Catalog+'&t='+Math.random();
            $.ajax({
                type: 'get',
                url:url,
                data:{mobile:n},
                timeout: 90000,
                async: false,
                beforeSend: function() {},
                dataType: 'json',
                success: function(o) {
                    if (o.isok == 'true') {
                        var username_isok = 1;
                        Message.showMessage('发送成功');
                    }else{
                        var username_isok = 0;
                        Message.showMessage("您今日的次数已用完或输入的手机号与账号不符");
                    }
                },
                complete: function() {},
                error: function() {}
            });
        }else {
            vmobile_isok=0;
            Message.showMessage("您填写的手机号码格式不正确");
        }
    }
}

function verify(mobile,yanzheng){
    var url = 'index.php?a=verify&c='+Catalog+'&t='+Math.random();
    $.ajax({
        type: 'get',
        url:url,
        data:{mobile:mobile,Code:yanzheng},
        timeout: 90000,
        async: false,
        beforeSend: function() {},
        dataType: 'json',
        success: function(o) {
            if (o.isok == 'true') {
                code_isok = 1;
            } else {
                code_isok = 0;
                Message.showMessage("验证码错误重新获取验证码");
            }
        },
        complete: function() {},
        error: function() {}
    });
}

//验证倒计时
var timing = 10;
var fixedtime;
var decide = 0;
function tacticsm(){
    var mobile = $('input[name="mobile"]').val();
    if (!mobile) {
        Message.showMessage('输入手机号');
        return false;
    }
    if(decide==0){
        chkmobile(mobile);
        if(username_isok == 1){
            $('.cover').text(timing+'秒后重发').addClass('on').attr('onclick','');
            decide = 1;
            fixedtime = setInterval(cover,1000);
        }
    }
}
function cover(){
    timing = timing-1;
    if(timing<=0){
        decide = 0;
        timing = 120;
        clearInterval(fixedtime);
        $('.cover').text('重新获取').attr('onclick','tacticsm()').removeClass('on');
    }else{
        $('.cover').text(timing+'秒后重发');
    }
}


//区域设置

var
    address=$('#aearess'),
    list=$('.mod_list');
var a='',c='',v='',shopcat='',regionstr;
var re=/[>]/g;
$(function(){
    $('#aearess').on('click',function(){
        var title=$(this).siblings('span').text();
        var mod_list=$('.mod_list').html();
        a=$(this).data('a');
        c=$(this).data('c');
        v=$(this).data('value');
        if(mod_list){list.empty();}
        $('.tit').html(title.substring(0,title.length-1))
        layer_bg.addClass('show');
        sf_layer.addClass('show').html(' <div class="layer_title"><div class="left btn_gray">返回</div><div class="tit">区域选择</div></div> <div class="mod_list"></div>').width('80%');
        meunlist()
        shopcat='';
        regionstr='';
        $('.btn_gray').on('click',function(){
            layer_bg.removeClass('show');
            sf_layer.removeClass('show');
        })
    })

    layer_bg.on('click',function(){
        layer_bg.removeClass('show');
        sf_layer.removeClass('show');
    })

});
function meunlist(v){
    var url = 'index.php?a='+a+'&c='+c+'&t='+Math.random();
    $.ajax({
            url:url,
            type:'GET',
            dataType:'json',
            data:{fid:v},
        })
        .done(function(o) {
            if(o.isok=='true'){
                if(o.datas.length>0){
                regionListObj= o.datas;
                 regionListDiv(regionListObj)
            }
            }
        })
        .fail(function() {
        })
        .always(function() {
        });
}

function regionListDiv(json){
    var html='';
    var mydiv=$('.mod_list');
    html+='<ul>';
    for(var i=0;i<json.length;i++){
        var onclick='',child='';
        if(json[i].immchildnum>0){
            child='class="child"';
            onclick='onclick=meunlist('+json[i].id+')';
        }else{
         onclick='data-links='+json[i].immchildnum+'';
        }
        html+='<li '+onclick+' '+child+' data-value="'+json[i].id+'">'+json[i].name+'</li>';
    }
    html+='</div>';
    mydiv.html(html);
    region();
}
function region(){
    $('.mod_list li').on('click',function() {
        var txt = $(this).text();
        var val=$(this).data('value');
        var links=$(this).data('links');
        if (!shopcat) {
            regionstr+=val;
            shopcat += txt;
        }else{
            regionstr+=','+val;
            shopcat += '>' + txt;
        }
        $('#aearess').html(shopcat);
        $('#regionstr').val(regionstr);
        if(links==0){
            layer_bg.removeClass('show');
            sf_layer.removeClass('show');
            ajaxregion()
        }
    })
}

//区域编辑添加
function ajaxregion(){
 var regionstr=$('#regionstr').val(),
         c=$('#aearess').data('c');
    var url = 'index.php?a=ajaxregion&c='+c+'&t='+Math.random();

    $.ajax({
            url:url,
            type:'GET',
            dataType:'json',
            data:{regionstr:regionstr},
        })
        .done(function(o) {
          if(o.isok=='true'){
              $('#aearess').html(o.data);
          }else {
              Message.showMessage(o.data);
          }
        })
        .fail(function() {
        })
        .always(function() {
        });
}
function getcity(){//获取市
        var getcity = $('#getcity option:selected').val();
        $.ajax({
            type:'GET',
            url:'index.php?a=city&c='+Catalog+'&pro='+getcity,
            dataType:'json',
            success:function(o){
                if(o.state == 'success'){
                    var jsonListObj = o.data;
                    if(jsonListObj){
                        insertListOption(jsonListObj);
                    }
                }                              
            }
        });
    }
    function insertListOption(datas){//添加数据到页面中
        var html = '';
        for(var i = 0; i< datas.length; i++){
            html +="<option value="+datas[i].value+">"+datas[i].name+"</option>";
        }
        $('#getarea').html(html); 
    }
    function getarea(){//获取区县
        var getarea = $('#getarea option:selected').val();
        $.ajax({
            type:'GET',
            url:'index.php?a=area&c='+Catalog+'&city='+getarea,
            dataType:'json',
            success:function(o){
                if(o.state == 'success'){
                    var jsonListObj = o.data;
                    if(jsonListObj){
                        insertListOptionss(jsonListObj);
                    }
                }                              
            }
        });
    }
    function insertListOptionss(datas){//添加数据到页面中
        var html = '';
        for(var i = 0; i< datas.length; i++){
            html +="<option value="+datas[i].value+">"+datas[i].name+"</option>";
        }
        $('#getareass').html(html); 
    }