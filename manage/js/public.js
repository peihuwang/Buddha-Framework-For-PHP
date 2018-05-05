var agt = navigator.userAgent.toLowerCase();
var is_ie = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
var is_gecko= (navigator.product == "Gecko");
var is_webkit=agt.indexOf('webkit')>-1;
var is_safari = (agt.indexOf('chrome')==-1)&&is_webkit;

//json2.js
if(!this.JSON){this.JSON={};}
(function(){function f(n){return n<10?'0'+n:n;}
if(typeof Date.prototype.toJSON!=='function'){Date.prototype.toJSON=function(key){return isFinite(this.valueOf())?this.getUTCFullYear()+'-'+
f(this.getUTCMonth()+1)+'-'+
f(this.getUTCDate())+'T'+
f(this.getUTCHours())+':'+
f(this.getUTCMinutes())+':'+
f(this.getUTCSeconds())+'Z':null;};String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(key){return this.valueOf();};}
var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'},rep;function quote(string){escapable.lastIndex=0;return escapable.test(string)?'"'+string.replace(escapable,function(a){var c=meta[a];return typeof c==='string'?c:'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4);})+'"':'"'+string+'"';}
function str(key,holder){var i,k,v,length,mind=gap,partial,value=holder[key];if(value&&typeof value==='object'&&typeof value.toJSON==='function'){value=value.toJSON(key);}
if(typeof rep==='function'){value=rep.call(holder,key,value);}
switch(typeof value){case'string':return quote(value);case'number':return isFinite(value)?String(value):'null';case'boolean':case'null':return String(value);case'object':if(!value){return'null';}
gap+=indent;partial=[];if(Object.prototype.toString.apply(value)==='[object Array]'){length=value.length;for(i=0;i<length;i+=1){partial[i]=str(i,value)||'null';}
v=partial.length===0?'[]':gap?'[\n'+gap+
partial.join(',\n'+gap)+'\n'+
mind+']':'['+partial.join(',')+']';gap=mind;return v;}
if(rep&&typeof rep==='object'){length=rep.length;for(i=0;i<length;i+=1){k=rep[i];if(typeof k==='string'){v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v);}}}}else{for(k in value){if(Object.hasOwnProperty.call(value,k)){v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v);}}}}
v=partial.length===0?'{}':gap?'{\n'+gap+partial.join(',\n'+gap)+'\n'+
mind+'}':'{'+partial.join(',')+'}';gap=mind;return v;}}
if(typeof JSON.stringify!=='function'){JSON.stringify=function(value,replacer,space){var i;gap='';indent='';if(typeof space==='number'){for(i=0;i<space;i+=1){indent+=' ';}}else if(typeof space==='string'){indent=space;}
rep=replacer;if(replacer&&typeof replacer!=='function'&&(typeof replacer!=='object'||typeof replacer.length!=='number')){throw new Error('JSON.stringify');}
return str('',{'':value});};}
if(typeof JSON.parse!=='function'){JSON.parse=function(text,reviver){var j;function walk(holder,key){var k,v,value=holder[key];if(value&&typeof value==='object'){for(k in value){if(Object.hasOwnProperty.call(value,k)){v=walk(value,k);if(v!==undefined){value[k]=v;}else{delete value[k];}}}}
return reviver.call(holder,key,value);}
cx.lastIndex=0;if(cx.test(text)){text=text.replace(cx,function(a){return'\\u'+
('0000'+a.charCodeAt(0).toString(16)).slice(-4);});}
if(/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,'@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']').replace(/(?:^|:|,)(?:\s*\[)+/g,''))){j=eval('('+text+')');return typeof reviver==='function'?walk({'':j},''):j;}
throw new SyntaxError('JSON.parse');};}}());
//end of json2.js

window.onload=function(){
	for(var ii=0; ii<document.links.length; ii++){
		document.links[ii].onfocus=function(){
			this.blur();
		}
	}
}


// ajax loading
function show_loading_body() {
	if ($("#layer_loading").length > 0) {
		$("#layer_loading").css("display") == 'none' ? $("#layer_loading").css(
				'display', '') : $("#layer_loading").css('display', 'none');
	} else {
		var yScroll = document.documentElement.scrollTop;
		var screenheight = document.documentElement.clientHeight;
		var t = yScroll + screenheight - 240;
		//alert(t);
		//if (t > document.body.clientHeight) {
		//	t = document.body.clientHeight;
		//}
		$("body").append('<div id="layer_loading" style="position:absolute;z-index:1001;font-size:16px;font-weight:bold;" id="layer_loading"><img src="/images/load2.gif" align="absmiddle" /> loading……</div>');
		$("#layer_loading").css("left",
				(($(document).width()) / 2 - (parseInt(100) / 2)) + "px").css(
				"top", t + "px");
		$("#layer_loading").show();
	}
}
//显示loading iconsize 图片尺寸大小 1 20*20 2 50*50
function ShowLoading(obj, iconsize) {
	if (iconsize == 2) {
		$(obj).html("<center><img src='/images/public/loading2.gif'></center>");
	} else
		$(obj).html("<center><img src='/images/public/loading1.gif'></center>");
}

/**
 * 获取验证码
 */
function changeYZM(emId) {
	if (typeof (emId) == "string") {
		$("#" + emId).attr('src', "/checkcode.php?t=" + Math.random());
		return;
	} else {
		$("#img_yzm").attr('src', "/checkcode.php?t=" + Math.random());
		return;
	}
}

// 字符替换
function tpl_replace(str, obj) {
	if (!(Object.prototype.toString.call(str) === '[object String]')) {
		return '';
	}
	// {}, new Object(), new Class()
	// Object.prototype.toString.call(node=document.getElementById("xx")) :
	// ie678 == '[object Object]', other =='[object HTMLElement]'
	// 'isPrototypeOf' in node : ie678 === false , other === true
	if (!(Object.prototype.toString.call(obj) === '[object Object]' && 'isPrototypeOf' in obj)) {
		return str;
	}
	// https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/String/replace
	return str.replace(/\{([^{}]+)\}/g, function(match, key) {
		var value = obj[key];
		return (value !== undefined) ? '' + value : '';
	});
}
Array.prototype.in_array = function(e) {
	for (i = 0; i < this.length; i++) {
		if (this[i] == e)
			return true;
	}
	return false;
}

function flatten(ac) {
	var array = [];
	var group = this.arr;
	if (ac)
		group = ac;
	for ( var i = 0; i < group.length; i++) {
		if (group[i] instanceof Array) {
			array = array.concat(this.flatten(group[i]));
		} else {
			array = array.concat(group[i]);
		}
	}
	return array;
}
// check used
function getObj(id) {
	return document.getElementById(id);
}

// 检查 email 格式
function IsEmail(strg) {
	var patrn = new RegExp(
			'^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$');
	if (!patrn.test(strg))
		return false;
	return true;
}

// 验证电话
function IsTel(strg) {
	var patrn = new RegExp(
			'^(([0\\+]\\d{2,3}-)?(0\\d{2,3})-)?(\\d{7,8})(-(\\d{3,}))?$');
	if (!patrn.test(strg))
		return false;
	return true;
}

// 验证手机
function IsMobile(strg) {
	var patrn = new RegExp('^(13|15|18)[0-9]{9}$');
	if (!patrn.test(strg))
		return false;
	return true;
}

// 验证邮编
function IsZip(strg) {
	var patrn = new RegExp('^\\d{6}$');
	if (!patrn.test(strg))
		return false;
	return true;
}

// 是否是用户名
function IsUserName(strg) {
	var patrn = new RegExp('^\\w+$');
	if (!patrn.test(strg))
		return false;
	return true;
}

function addFavorite() {
	var title = document.title;
	var url = document.location.href;
	if (window.sidebar)
		window.sidebar.addPanel(title, url, "");
	else if (window.opera && window.print) {
		var mbm = document.createElement('a');
		mbm.setAttribute('rel', 'sidebar');
		mbm.setAttribute('href', url);
		mbm.setAttribute('title', title);
		mbm.click();
	} else if (document.all)
		window.external.addFavorite(url, title);
}

function selectAll(name, ob) {
	if (typeof (ob) == 'object') {
		$("input[name='" + name + "']").attr("checked", ob.checked);
	} else {
		$("input[name='" + name + "']").attr("checked",
				$("#" + ob).attr('checked'));
	}
}

function countStringLength(str) {
	var str_length = 0;
	for ( var i = 0; i < str.length; i++) {
		if (str.charCodeAt(i) <= 256) {
			str_length += 1;
		} else {
			str_length += 2;
		}
	}
	return str_length;
}

function checkInputLength(obj, num, subject) {
	var str = $("#"+obj).val();
	var len = Math.floor((num - countStringLength(str)) / 2);
	if (len < 0) {
		(subject == "title_num") ? $("#w_c_len").val(0) : $("#w_r_len").val(0);
		$("#bloglongsize").html('请文明发言，已经超过<span id="sizeNums">'+(len*-1)+'</span>字');
	} else {
		(subject == "title_num") ? $("#w_c_len").val(1) : $("#w_r_len").val(1);
		$("#bloglongsize").html('请文明发言，您还可以输入<span id="sizeNum">'+len+'</span>字');
	}
}

function setHome(obj) {
	try {
		obj.style.behavior = 'url(#default#homepage)';
		obj.setHomePage(lk_url);
	} catch (e) {
		if (window.netscape) {
			try {
				netscape.security.PrivilegeManager
						.enablePrivilege("UniversalXPConnect");
			} catch (e) {
				alert("抱歉，此操作被浏览器拒绝！\n\n请在浏览器地址栏输入“about:config”并回车然后将[signed.applets.codebase_principal_support]设置为'true'");
			}
			;
		} else {
			alert("抱歉，您所使用的浏览器无法完成此操作。\n\n您需要手动将'http://www.mct.com/'设置为首页。");
		}
		;
	}
	;
}

function resizeImg(ele){
	var a=ele;
	if(a.width<30||a.height<30){
		setTimeout(function(){
		resizeImg(a);
		},10)
		return false;
	}
	if(a.width>100){		
	a.width = 100;
		} else if(a.height>100){
	a.height=100;
		}
}

function DrawImage(ImgD,iwidth,iheight){
	//alert(iwidth);
    //参数(图片,允许的宽度,允许的高度)
    var image=new Image();
    image.src=ImgD.src;
    if(image.width>0 && image.height>0){
		if(image.width/image.height>= iwidth/iheight){
				if(image.width>iwidth){  			
				ImgD.width=iwidth;
				ImgD.height=(image.height*iwidth)/image.width;
				}else{
				ImgD.width=image.width;  
				ImgD.height=image.height;
				}
			//ImgD.alt=image.width+"×"+image.height;
		}else{
			if(image.height>iheight){  
			ImgD.height=iheight;
			ImgD.width=(image.width*iheight)/image.height;        
			}else{
			ImgD.width=image.width;  
			ImgD.height=image.height;
			}
			//ImgD.alt=image.width+"×"+image.height;
		}
    }
}

function changeFocusphoto(num,obj,width,count){
	//var nb = -(num-3)*width;
	var nb = parseInt(num/6);
	var width = count*105;
	if(nb<1){
		$('#'+obj).attr("style","left:0px;width:"+width+"px;");
	}else{
		$('#'+obj).attr("style","left:-"+nb*6*105+"px;width:"+width+"px;");
	}
}

function showGroup(total,direction,num,width,obj){
	var t_width = total*width;
	var scroll_width = num*width;
	var left_len = -parseInt($("#"+obj).position().left);
	if(direction==1){
		if((t_width-left_len)>scroll_width){
			//alert((left_len+scroll_width));
			$('#'+obj).attr("style","left:-"+(left_len+scroll_width)+"px;width:"+t_width+"px;");
		}else{
			//alert((left_len));
			$('#'+obj).attr("style","left:-"+left_len+"px;width:"+t_width+"px;");
		}
	}else{
		if(left_len>scroll_width && direction==0){
			//alert((scroll_width-left_len));
			$('#'+obj).attr("style","left:"+(scroll_width-left_len)+"px;width:"+t_width+"px;");
		}else{
			$('#'+obj).attr("style","left:0px;width:"+t_width+"px;");
		}
	}
}

function getOs(){
	var OsObject = "";
	if(navigator.userAgent.indexOf("MSIE")>0) {
		return "MSIE";
	}
	if(isFirefox=navigator.userAgent.indexOf("Firefox")>0){
		return "Firefox";
	}
	if(isSafari=navigator.userAgent.indexOf("Safari")>0) {
		return "Safari";
	} 
	if(isCamino=navigator.userAgent.indexOf("Camino")>0){
		return "Camino";
	}
	if(isMozilla=navigator.userAgent.indexOf("Gecko/")>0){
		return "Gecko";
	}
} 

function copyToClipBoard(txt) {
    if (window.clipboardData) {
        window.clipboardData.clearData();
        window.clipboardData.setData("Text", txt);
    } else if (navigator.userAgent.indexOf("Opera") != -1) {
        //do nothing      
    } else if (window.netscape) {
        try {
            netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        } catch (e) {
            alert("被浏览器拒绝！\n请在浏览器地址栏输入'about:config'并回车\n然后将 'signed.applets.codebase_principal_support'设置为'true'");
        }
        var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
        if (!clip)   return;
        var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
        if (!trans) return;
        trans.addDataFlavor('text/unicode');
        var str = new Object();
        var len = new Object();
        var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
        var copytext = txt;
        str.data = copytext;
        trans.setTransferData("text/unicode", str, copytext.length * 2);
        var clipid = Components.interfaces.nsIClipboard;
        if (!clip)   return false;
        clip.setData(trans, null, clipid.kGlobalClipboard);
    }
}

function CheckAll(form,type){
	for (var i=0;i<form.elements.length;i++){
		var e = form.elements[i];
		if(e.type=="checkbox"){
			if(typeof(type) != 'undefined'){
				e.checked == true ? e.checked = false : e.checked = true;
			}else{
				e.checked = true;
			}
		}
	}
}

function searchkeyclick(e,txts) {
	if ($(e).val() == txts) {
		$(e).val('');
	}else{
		$(e).focus();	
	}
}

function searchkeydblclick(e,txts) {
	if ($(e).val() == txts) {
		$(e).val('');
	}else{
		$(e).select();	
	}
}

function blursearchkey(e,txts) {
	var value = $(e).val();
	var txt = new RegExp("[,\\`,\\~,\\!,\\@,\#,\\$,\\%,\\^,\\+,\\*,\\&,\\\\,\\/,\\?,\\|,\\:,\\<,\\>,\\{,\\},\\(,\\),\\',\\;,\\=,\"]");
	if (txt.test(value)) {
		alert("关键字中请不要输入特殊字符！");
		$(e).val(txts);
	}
	if ($(e).val() == "" || $(e).val() == undefined) {
		$(e).val(txts);
	}
}

//提示窗口
function showContent(msg,title,width){
	art.dialog({
		content:msg,
		lock:true,
		fixed:'false',
		drag: false,
		resize: false,
		id:'msg1',
		width:width?width:500,
		background:'#000',
		opacity:0.01,
		title:title
	});
}

//文章页字体大小设置
function showContentFontSize(type){
	if(type==1){
		$("#contentShowBoxs").css("font-size",16)
	}else{
		$("#contentShowBoxs").css("font-size",12)
	}
}

function CheckAll(form,type){
	for (var i=0;i<form.elements.length;i++){
		var e = form.elements[i];
		if(e.type=="checkbox"){
			if(typeof(type) != 'undefined'){
				e.checked == true ? e.checked = false : e.checked = true;
			}else{
				e.checked = true;
			}
		}
	}
}

function login(){
	var return_url = $("#return_url").val();
	window.location.href = '/signin.html&backurl='+escape(return_url);	
}

function register(){
	var return_url = $("#return_url").val();
	window.location.href = '/register.html&backurl='+escape(return_url);	
}

//获取系统时间
function getSystemTime(){
	var bool = '';
	$.ajax({
		type:'get',
		url:'/ajax/promo.html&act=getDate',
		data:'',
		timeout:90000,
		beforeSend:function(){},
		dataType : 'json',
		async: false, 
		success:function(o){
			if(o.isok==true){
				bool = o.data;
			}
		},
		complete:function(){},
		error:function(){}
	});
	return bool;
}

//倒计时函数
function updateEndTime(){
	//var date = new Date();
	//var time = date.getTime()/1000;
	var time = getSystemTime();
	var text_str = "剩余";
	
	$(".timeleft_label").each(function(i){
		var endTime=this.getAttribute("end"); //结束时间秒数
		var lag = (endTime - time) //当前时间和结束时间之间的秒数
		if(lag > 0){
			var second = Math.floor(lag % 60);    
			var minite = Math.floor((lag / 60) % 60);
			var hour = Math.floor((lag / 3600) % 24);
			var day = Math.floor((lag / 3600) / 24);
			if(day<3){
				$(this).html(text_str+"：<span class=\"day\">"+day+"</span>天<span class=\"hour\">"+hour+"</span>小时<span class=\"minute\">"+minite+"</span>分<span class=\"second\">"+second+"</span>秒");
			}else{
				$(this).html(text_str+"：3天以上");
			}
		}else{
			window.location.reload();
		}
	});
	setTimeout("updateEndTime()",1000);
}

var getLength = function(str, shortUrl) {
	if (true == shortUrl) {
		if(str){
			return Math.ceil(str.replace(/((news|telnet|nttp|file|http|ftp|https):\/\/){1}(([-A-Za-z0-9]+(\.[-A-Za-z0-9]+)*(\.[-A-Za-z]{2,5}))|([0-9]{1,3}(\.[0-9]{1,3}){3}))(:[0-9]*)?(\/[-A-Za-z0-9_\$\.\+\!\*\(\),;:@&=\?\/~\#\%]*)*/ig, 'http://goo.gl/fkKB ').replace(/^\s+|\s+$/ig,'').replace(/[^\x00-\xff]/ig,'xx').length/2);
		}else{
			return 0;	
		}
	} else {
		return Math.ceil(str.replace(/^\s+|\s+$/ig,'').replace(/[^\x00-\xff]/ig,'xx').length/2);
	}
};

var subStr = function (str, len) {
    if(!str) { return ''; }
        len = len > 0 ? len*2 : 280;
    var count = 0,	//计数：中文2字节，英文1字节
        temp = '';  //临时字符串
    for (var i = 0;i < str.length;i ++) {
    	if (str.charCodeAt(i) > 255) {
        	count += 2;
        } else {
        	count ++;
        }
        //如果增加计数后长度大于限定长度，就直接返回临时字符串
        if(count > len) { return temp; }
        //将当前内容加到临时字符串
         temp += str.charAt(i);
    }
    return str;
};

function getLen( str) {
	var totallength=0;
	for (var i=0;i<str.length;i++)
	{
		var intCode=str.charCodeAt(i);
		if (intCode>=0 && intCode<=128) {
			totallength=totallength+1; //非中文单个字符长度加 1
		} else {
			totallength=totallength+2; //中文字符长度则加 2
		}
	} //end for  
	return totallength;
}

function showFloatDiv(val){
	$("#descr_box_bcon").html(val);
	$("#floatmain_1").fadeIn();
}
$(document).ready(function(e) {
    $("#close_con_box").click(function(){
		$("#floatmain_1").fadeOut();
	})
});

function checkIdcard(num)
{  
        num = num.toUpperCase(); 
        //身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X。  
        if (!(/(^\d{15}$)|(^\d{17}([0-9]|X)$)/.test(num)))  
        {
            //alert('输入的身份证号长度不对，或者号码不符合规定！\n15位号码应全为数字，18位号码末位可以为数字或X。');
            return false;
        }
        //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
        //下面分别分析出生日期和校验位
        var len, re;
        len = num.length;
        if (len == 15)
        {
            re = new RegExp(/^(\d{6})(\d{2})(\d{2})(\d{2})(\d{3})$/);
            var arrSplit = num.match(re);

            //检查生日日期是否正确
            var dtmBirth = new Date('19' + arrSplit[2] + '/' + arrSplit[3] + '/' + arrSplit[4]);
            var bGoodDay;
            bGoodDay = (dtmBirth.getYear() == Number(arrSplit[2])) && ((dtmBirth.getMonth() + 1) == Number(arrSplit[3])) && (dtmBirth.getDate() == Number(arrSplit[4]));
            if (!bGoodDay)
            {
                //alert('输入的身份证号里出生日期不对！');  
                return false;
            }
            else
            {
                    //将15位身份证转成18位
                    //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                    var arrInt = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                    var arrCh = new Array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                    var nTemp = 0, i;  
                    num = num.substr(0, 6) + '19' + num.substr(6, num.length - 6);
                    for(i = 0; i < 17; i ++)
                    {
                        nTemp += num.substr(i, 1) * arrInt[i];
                    }
                    num += arrCh[nTemp % 11];  
                    return true;  
            }  
        }
        if (len == 18)
        {
            re = new RegExp(/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/);
            var arrSplit = num.match(re);

            //检查生日日期是否正确
            var dtmBirth = new Date(arrSplit[2] + "/" + arrSplit[3] + "/" + arrSplit[4]);
            var bGoodDay;
            bGoodDay = (dtmBirth.getFullYear() == Number(arrSplit[2])) && ((dtmBirth.getMonth() + 1) == Number(arrSplit[3])) && (dtmBirth.getDate() == Number(arrSplit[4]));
            if (!bGoodDay)
            {
                //alert(dtmBirth.getYear());
                //alert(arrSplit[2]);
                //alert('输入的身份证号里出生日期不对！');
                return false;
            }
        else
        {
            //检验18位身份证的校验码是否正确。
            //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
            var valnum;
            var arrInt = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            var arrCh = new Array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            var nTemp = 0, i;
            for(i = 0; i < 17; i ++)
            {
                nTemp += num.substr(i, 1) * arrInt[i];
            }
            valnum = arrCh[nTemp % 11];
            if (valnum != num.substr(17, 1))
            {
                alert('18位身份证的校验码不正确！');
                return false;
            }
            return true;
        }
        }
        return false;
}