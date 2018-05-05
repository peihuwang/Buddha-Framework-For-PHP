var chkLoginChkcode = 0;
var oldpwd_isok = 0;
var newpwd_isok = 0;
var vmobile_isok = 0;
var username_isok = 0;
var code_isok = 0;
var vemail_isok = 0;
var Verification_isok=0;
var _user = {
    usernameExp: new RegExp("^[A-Za-z0-9]+$"),//(\u4e00-\u9fa5)
    passwordExp: new RegExp("^[A-Za-z0-9]+$"),
	mobileExp: new RegExp("^[1][3-8]\\d{9}$"),
    valicatmExp:new RegExp("^[0-9]\\d{5}$"),
    //用户名验证
		chkusername: function(n) {

		if (!n) {
			Message.showMessage("输入用户名");
			username_isok = 0;
		} else {
			if (this.usernameExp.test(n)) {
				var url = 'index.php?a=existnickname&c=account&t='+Math.random();
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
							Message.showMessage("此用户名已被使用");
						}
					},
					complete: function() {},
					error: function() {}
				});
			} else {
				username_isok = 0;
				Message.showMessage("用户名由数字或英文字母，不能有特殊字符");
			}
		}
	},

	//密码
	chknewpwd : function(n){
		if(!n){
			Message.showMessage("请输入密码！");
			newpwd_isok = 0;
		}else{
			if(this.passwordExp.test(n)){
				newpwd_isok = 1;
			}else{
				Message.showMessage("您输入的密码格式不正确");
				newpwd_isok = 0;
				return false;
			}
		}
	},
	
	yanzheng : function(n){
		//获取手机验证码
		var reg_mobile = $('#reg_mobile').val();
		var judge = 0;
		if(!reg_mobile){
			Message.showMessage('手机号不能空！');
			return false;
		}
		if(!this.mobileExp.test(reg_mobile)){
			Message.showMessage('您填写的手机号码格式不正确！');
			return false;
		}else{
			var url = 'index.php?a=verifyrequest&c=account&t='+Math.random();
			var jsonarr = {'mobile':reg_mobile};
			var jsonstr = JSON.stringify(jsonarr);
			$.ajax({
				type: 'get',
				url:url ,
				data:{json:jsonstr},
				async:false,
				timeout: 90000,
				beforeSend: function() {
				},
				dataType: 'json',
				success: function(o) {
					if (o.isok == 'true') {
						Message.showMessage('发送成功');
					  judge = 1;
					} else {
						Message.showMessage('发送失败');
					}
				},
				complete: function() {
				},
				error: function() {
				}
			});
			if(judge==1){
				return true;
			}else{
				return false;
			}
		}
	},

     Reg : function(){
		var reg_username = $("#reg_username").val();
		var reg_mobile = $('#reg_mobile').val();
		var reg_pwd = $("#reg_pwd").val();
		var reg_newpwd = $("#reg_newpwd").val();
		var isok_ = $("input[name='isok']:checked").val();
		
		this.chkusername(reg_username);
		if(!username_isok){
			$("#reg_username").focus();
			return false;
		}
		if(!reg_mobile){
			Message.showMessage("手机号不能空！");
			return false;
		}
		this.chknewpwd(reg_pwd);
		if(!newpwd_isok){
			$("#reg_pwd").focus();
			return false;
		}
		
		if(/^\d+$/.test(reg_pwd) || /^[a-zA-Z]+$/.test(reg_pwd)){
			Message.showMessage("密码建议使用英文字母或数字组合");
			return false;
		}	
		if(reg_pwd!=reg_newpwd){
			Message.showMessage("两次密码填写不一致！");
			$("#reg_newpwd").focus();
			return false;
		}
		if(isok_!='1'){
			Message.showMessage("请阅读并同意用户服务协议！");
			return false;
		}
		$('#showLoading').show();
        $.ajax({
        	url: 'index.php?a=register&c=account',
        	type: 'POST',
        	dataType: 'json',
        	data: $('#reg').serialize(),
        })
        .done(function(txt) {
        	$('#showLoading').hide();
        	if (txt == 1) {
					Message.showMessage("恭喜您！注册成功！");
					window.location.href='/';
				} else if(txt == 2) {
					Message.showMessage("非常抱歉！验证码错误！");
				} else if(txt == 3) {
					Message.showMessage("非常抱歉！您的用户名已经被注册！");
				} else if(txt == 4) {
					Message.showMessage("非常抱歉！您的手机号已经被注册！");
				}else{
					Message.showMessage("非常抱歉！注册失败！");
					setTimeout("window.location.href='index.php?a=register&c=account'",1000);
				}
        })
        .fail(function() {
        })
        .always(function() {
        	$('#showLoading').hide();
        });
        
},
     
     Login: function() {
        var loginname = $("#lg_username").val();
		var password = $("#lg_pwd").val();
		//帐号
		if (!loginname) {
            Message.showMessage("请输入登录用户名！");
			return false;
        } else {
            if (!this.usernameExp.test(loginname)) {
				Message.showMessage("用户名由字母或数字，不能包含特殊字符");
				return false;
			}
		}
		//密码
        if (!password) {
            Message.showMessage("请输入登录密码！");
			return false;
        } else {
            if (!this.passwordExp.test(password)) {
				Message.showMessage("密码格式有误！");
				return false;
			}
		}
		$('#showLoading').show();
		$.ajax({
			url: 'index.php?a=login&c=account',
			type: 'POST',
			dataType: 'json',
			data:$('#login').serialize(),
		})
		.done(function(txt) {

			$('#showLoading').hide();
			if(txt==0){
				Message.showMessage("账号或密码错误!");
				return false;
			}else if(txt==2){
				Message.showMessage("账号已停用!");
				return false;
			}else if(txt==4){
				Message.showMessage("账号已停用!");
				return false;
			}else{
				window.location.href="../../../index.php";
			}
		})
		.fail(function() {
		
		})
		.always(function() {
			$('#showLoading').hide();
		});
		
     },
	Forgottenpwd: function() {
		var password = $("#reg_pwd").val();
		var reg_newpwd = $("#reg_newpwd").val();
		//密码
		if (!password) {
			Message.showMessage("请输入登录密码！");
			return false;
		} else {
			if (!this.passwordExp.test(password)) {
				Message.showMessage("密码格式有误！");
				return false;
			}
		}

		if(password!=reg_newpwd){
			Message.showMessage("两次密码输入不一致！");
			return false;
		}

		$('#showLoading').show();
		$.ajax({
					url: 'index.php?a=forgottenpwd&c=account',
					type: 'POST',
					dataType: 'json',
					data:$('#forgottenpwdform').serialize(),
				})
				.done(function(txt) {

					$('#showLoading').hide();
					Message.showMessage(txt);
					if(txt==0){
						Message.showMessage("验证码输入不正确!");
						return false;
					}else{
						//Message.showMessage("密码设置正确，可以进行登录了!");
						window.location.href="../../../index.php";
					}
				})
				.fail(function() {

				})
				.always(function() {
					$('#showLoading').hide();
				});

	},

}
//验证倒计时
var timing = 120;
var fixedtime;
var decide = 0;
function tacticsm(){
	if(decide==0){
		if(_user.yanzheng()){
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

$(function(){
$('.role label').on('click', function(event) {
	event.preventDefault();
	$(this).addClass('on').siblings().removeClass('on');
	/* Act on the event */
});
})