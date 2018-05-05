

function usertype(obj){
   var usertype=obj;
    if(usertype==2 ){
     $('#arear,#carry').show();
        $('#trate').attr('name','agentrate');
    }else{
        $('#arear,#carry').hide();
    }
    if(usertype==3){
        $('#trate').attr('name','partnerrate');
        $('#arear,#carry').show();
    }
}

$(function(){
    var jsonarr;
    var len = $ (".arear").length;
    $('.arear').each(function(index){
        var selectlength =$(this).find('option').length;
         var option= $(this).data('value');
        if(selectlength==0){
            $(this).addClass('hide');
        }
        if(option){
        var nextlevel = 'level'+(parseInt(index)+2);
         var value=$('#'+nextlevel).data('value');
        if(index<len)
        jsonarr = {'father':option};
        cityamin(jsonarr, value,nextlevel)
        }
    })

$('.arear').change(function(){
    var optionsel = $(this).find('option:selected').val();
    if(!optionsel){
        $(this).parent().nextAll().addClass('hide');
    }else {
        $(this).parent().next().removeClass('hide');
    }
    var whichchange =  $(this).attr('id');
    var level= whichchange.charAt(whichchange.length-1);
    var nextlevel = 'level'+(parseInt(level)+1);
    jsonarr = {'father':optionsel};
    citya_min(jsonarr,nextlevel);

})

});

function cityamin(jsonarr,value,nextlevel) {
    var url = 'index.php?a=ajax&c=region&t='+Math.random();
    var jsonstr = JSON.stringify(jsonarr);
    $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            data:{json:jsonstr},
        })
        .done(function(json) {
            var html='';
            var createareas=$('#'+nextlevel);
            if(json.isok=='true'){
                var json=json.data;
                var  selected='';
                html+='<option value="">请选择</option>';
                for (var i = 0; i < json.length; i++) {
                    var   selected = '';
                    if(json[i].id==value) {
                        selected = 'selected';
                    }
                    html+='<option value="'+json[i].id+'" '+selected+'>'+json[i].name+'</option>';

                }
                createareas.html(html).removeClass('hide');
            }
        })
        .fail(function() {
        })
        .always(function() {});
}




function citya_min(jsonarr,nextlevel) {
    var url = 'index.php?a=ajax&c=region&t='+Math.random();
    var jsonstr = JSON.stringify(jsonarr);
    $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            data:{json:jsonstr},
        })
        .done(function(json) {
            var html='';
            var createareas=$('#'+nextlevel);
            if(json.isok=='true'){
                var json=json.data;
                html+='<option value=" ">请选择</option>';
                for (var i = 0; i < json.length; i++) {
                    html+='<option value="'+json[i].id+'">'+json[i].name+'</option>';
                }
                createareas.html(html).removeClass('hide');
            }
        })
        .fail(function() {
        })
        .always(function() {});
}


var username_isok = 0;
var vmobile_isok = 0;
var region_isok=0;

var usernameExp= new RegExp("^[A-Za-z0-9]+$");
var mobileExp=new RegExp("^[1][3-8]\\d{9}$");
var trateExp=new RegExp("^[0-9]{2}$");
var passwordExp= new RegExp("^[a-zA-Z0-9]{6,20}$");
function chkusername(n){
    if (!n) {
        username_isok = 0;
    } else {
        if (this.usernameExp.test(n)) {
            var url = 'index.php?a=existnickname&c=user&t='+Math.random();
            var jsonarr = {'username':n};
            var jsonstr = JSON.stringify(jsonarr);
            $.ajax({
                type: 'get',
                url:url,
                data:{json:jsonstr},
                timeout: 90000,
                async: false,
                beforeSend: function() {},
                dataType: 'json',
                success: function(o) {
                    if (o.isok == 'true') {
                        username_isok = 1;
                    } else {
                        username_isok = 0;
                      alert(' 用户名已被注册')
                    }
                },
                complete: function() {},
                error: function() {}
            });
        } else {
            username_isok = 0;
            alert("用户名数字或英文字母，不能有特殊字符");
        }
    }
}

function chkobile(n){
    if(!n){
        vmobile_isok=0;
    }else {
        if(this.mobileExp.test(n)){
            var url = 'index.php?a=existmobile&c=user&t='+Math.random();
            var jsonarr = {'mobile':n};
            var jsonstr = JSON.stringify(jsonarr);
            $.ajax({
                type: 'get',
                url:url,
                data:{json:jsonstr},
                timeout: 90000,
                async: false,
                beforeSend: function() {},
                dataType: 'json',
                success: function(o) {
                    if (o.isok == 'true') {
                        vmobile_isok = 1;
                    } else {
                        vmobile_isok = 0;
                    }
                },
                complete: function() {},
                error: function() {}
            });
        }else {
            vmobile_isok=0;
           alert("手机号格式错误!");
        }
    }
}
function region(n,uid){
if(!n){
    region_isok=0;
}else {
    var url = 'index.php?a=chregion&c=user&t='+Math.random();
    var jsonarr = {'level3':n,'user_id':uid};
    var jsonstr = JSON.stringify(jsonarr);
    $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            async:false,
            data:{json:jsonstr},
        })
        .done(function(o) {
            if(o.isok == 'true'){
                region_isok=1;
            }else{
                region_isok =0;
            }
        })
        .fail(function() {
        })
        .always(function(){});
}
}


$(function(){
    $('#level3').change(function(){
        var level3= $(this).find('option:selected').val(),
        typeid=$('#typeid').find('option:selected').val();
        if(typeid==2){
        if(!level3){
            alert('区县不能为空');
            return false
        }
        region(level3);
        if(!region_isok){
            alert('该区域已有代理商');
            $("#level3").focus();
            return false
        }
        }
    })
})


function checkpost() {
    var userid = $('#userid').val(),
        username = $('#username').val(),
        realname = $('#realname').val(),
        typeid = $('#typeid').find('option:selected').val(),
        mobile = $('#mobile').val(),
        trate = $('#trate').val(),
        password = $('#password').val(),
        pasw = $('#pasw').val(),
        level1 = $('#level1').find('option:selected').val(),
        level2 = $('#level2').find('option:selected').val(),
        level3 = $('#level3').find('option:selected').val(),
        level4 = $('#level4').find('option:selected').val(),
        level5 = $('#level5').find('option:selected').val();

    //编辑时不验证
    if (!userid) {
        if (!username) {
            alert("用户名不能为空");
            $("#username").focus();
            return false;
        }
        chkusername(username);
        if (!username_isok) {
            $("#username").focus();
            return false;
        }
    }
    if (!realname) {
        alert('姓名不能为空');
        $('#realname').focus()
        return false;
    }
    if (!typeid) {
        alert('选择会员级别');
        $('#typeid').focus();
        return false;
    }

    if (!userid) {
        if (!password) {
            alert('设置会员登录密码');
            $('#password').focus();
            return false;
        }
        if (!passwordExp.test(password)) {
            alert('英文字母或数字组合(6-20个字符');
            $('#password').focus();
            return false;
        }
        if (password != pasw) {
            alert("两次密码填写不一致！");
            $("#pasw").focus();
            return false;
        }
    }
    if (typeid == 2 || typeid == 3){
        if (!level1 && !level2 && !level3) {
            alert('选择区域');
            $('#level1').focus();
            return false;
        }
}
        if(typeid==2){
           region(level3,userid);
            if(!region_isok){
               alert('该区域已有代理商');
                return false;
            }

            if(!trate){
                alert('代理商提成比例不能为空');
                $('#trate').focus();
                return false;
            }
            if(!trateExp.test(trate)){
                alert('只能输入0-9的两位数');
                $('#trate').focus();
                return false;
            }

        }else {
            if(!userid) {
                if (!mobile) {
                    alert('手机号不能为空');
                    $('#mobile').focus();
                    return false;
                }
                chkobile(mobile);
                if (!vmobile_isok) {
                    alert('手机号已被注册');
                    $("#mobile").focus();
                    return false;
                }
            }
        }
    if(typeid==3){
        if(!trate){
            alert('代理商提出不能为空');
            $('#trate').focus();
            return false;
        }
        if(!trateExp.test(trate)){
            alert('只能输入0-9的两位数');
            $('#trate').focus();
            return false;
        }
    }

}
