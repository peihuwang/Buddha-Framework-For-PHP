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

var username_isok = 0;
var vmobile_isok = 0;

var shop={
        usernameExp: new RegExp("^[A-Za-z0-9]+$"),
        passwordExp: new RegExp("^[A-Za-z0-9]+$"),
        mobileExp: new RegExp("^[1][3-8]\\d{9}$"),
    chkusername:function(n){
    if (!n) {
        username_isok = 0;
    } else {
        if (this.usernameExp.test(n)) {
            var url = 'index.php?a=existnickname&c=shop&t='+Math.random();
            $.ajax({
                type: 'get',
                url:url,
                data:{'username':n},
                timeout: 90000,
                async: false,
                beforeSend: function() {},
                dataType: 'json',
                success: function(o) {
                    if (o.isok == 'true') {
                        username_isok = 1;
                    } else {
                        username_isok = 0;
                        Message.showMessage("此用户名已被使用");
                    }
                },
                complete: function() {},
                error: function() {}
            });
        } else {
            username_isok = 0;
            Message.showMessage("用户名由5-20数字或英文字母，不能有特殊字符");
        }
    }
},
    chkobile:function(n){
    if(!n){
        vmobile_isok=0;
    }else {
        if(this.mobileExp.test(n)){
            var url = 'index.php?a=existmobile&c=shop&t='+Math.random();

            $.ajax({
                type: 'get',
                url:url,
                data:{'Mobile':n},
                timeout: 90000,
                async: false,
                beforeSend: function() {},
                dataType: 'json',
                success: function(o) {
                    if (o.isok == 'true') {
                        vmobile_isok = 1;
                    } else {
                        vmobile_isok = 0;
                        Message.showMessage("手机号已被注册");
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
},
    add:function(){
        var name = $("#name").val(),
            shopcat = $('#shopcat_id').val(),
            realname = $('#realname').val(),
            mobile = $('#mobile').val(),
            regionstr=$('#regionstr').val(),
            username = $("#username").val(),
            password = $("#password").val(),
            specticloc=$('#specticloc').val(),
            property=$('#property').val(),
            storetype=$('#storetype').find('option:selected').val(),
            Image=$("[name='Image']").val();
      if (username) {
            this.chkusername(username);
            if (!username_isok) {
                return false;
            }
        }
        if (!name) {
            Message.showMessage('店铺名称不能为空');
            return false;
        }
        if(!shopcat || shopcat==0){
            Message.showMessage('选择店铺所属分类');
            return false;
        }
        if (!realname) {
            Message.showMessage('店铺联系人');
            return false;
        }
        if (!mobile) {
            Message.showMessage('联系电话');
            return false;
        }
        this.chkobile(mobile);
        if (!vmobile_isok) {
            return false;
        }
        if(!regionstr || regionstr==0){
            Message.showMessage('选择店铺所在区域,以便客户准确的找到您');
            return false;
        }
        if(!specticloc){
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
    edit:function(){
        var name = $("#name").val(),
            shopcat = $('#shopcat_id').val(),
            realname = $('#realname').val(),
            regionstr=$('#regionstr').val(),
            specticloc=$('#specticloc').val(),
            property=$('#property').val(),
            storetype=$('#storetype').find('option:selected').val(),
            Image=$("#shopz").attr('src');

        if (!name) {
            Message.showMessage('店铺名称不能为空');
            return false;
        }
        if(!shopcat && shopcat=='0'){
            Message.showMessage('选择店铺所属分类');
            return false;
        }
        if (!realname) {
            Message.showMessage('店铺联系人');
            return false;
        }
        if (!regionstr && regionstr == '0') {
            Message.showMessage('选择店铺所在区域,以便客户准确的找到您');
            return false;
        }
        if (!specticloc) {
            Message.showMessage('详细地址不能为空');
            return false;
        }
        if (!storetype && storetype == '') {
            Message.showMessage('选择店铺性质');
            return false;
        }

        if (storetype != 1 && storetype != 5) {
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
}



var url=window.location.search;
if(url.indexOf('index')>0){
    ajaxlist(1,url);//默认加载一页

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
        data:{PageSize:PageSize,p:p,list:list},
        dataType: "json",
        cache:false,
        success: function (o) {
            $('#showLoading').hide();
            if(o.isok=='true'){
                    var jsonListObj = o.data;
                    insertListDiv(jsonListObj);
            }else{
                $('.div_null').html(o.data).show();
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
    var html = '';
    for (var i = 0; i< json.length; i++) {
        var state='启用';
        if(json[i].state==1){
            state='停用';
        }
        html+='<div class="item" id="list_'+json[i].id+'">';
        // html+='<div class="itemimg"><img src="/'+json[i].small+'"></div>';
        html+='<div class="itemimg"><span class="'+json[i].is_sure+'"></span><img src="/'+json[i].small+'"></div>';
        html+='<div class="iteminfo">';
        html+='<h1>'+json[i].name+'</h1>';
        html+='<div class="number">编号:'+json[i].number+'</div>';
        html+='<p>创建时间:'+json[i].createtime+'</p>';
        html+='</div>';
        html+='  <div class="oper">';
        if(json[i].isdel==4){
            html+='店铺被代理商冻结';
        }else{
            html+='<span data-href="index.php?a=edit&c=shop&id='+json[i].id+'">编辑</span>';
           /* html+='  <span class="stop" id="state_'+json[i].id+'" data-value="'+json[i].id+'" data-a="state" data-c="shop">'+state+'</span>';
            html+='  <span class="del" data-value="'+json[i].id+'" data-a="del" data-c="shop">删除</span>';*/
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
        var s=''
        if(txt=='启 用'){
            s='停 用';
        }else {
            s='启 用';
        }
        var url='index.php?a='+a+'&c='+c+'&id='+value;
        if(a=='del'){
            Message.showConfirm("您确定要删除该店铺吗？一旦删除将不能找回。", "我在想想", "我要删除", function () {
                console.log('不删除');
            }, function () {
                delshop(url);
            });

        }else{
            Message.showConfirm("现在店铺为 ["+txt+"] 状态,您确定要 ["+s+"] 店铺吗？","取消", "确定", function () {
                console.log('取消');
            }, function () {
                eidtshop(url);
            });

        }
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
                $('#state_'+o.data.id).html(o.data.state);
            }
        },
        complete: function() {},
        error: function() {}
    });
}

function delshop(url){
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
                $('#list_'+o.data).remove();
            }else{
                Message.showMessage(o.data)
            }
        },
        complete: function() {},
        error: function() {}
    });
}