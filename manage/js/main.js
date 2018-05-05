/*
*
* 后台公用函数
*/
function CheckAll(form,type){
	for (var i=0;i<form.elements.length;i++){
		var e = form.elements[i];
		if(e.type=="checkbox"){
			if(typeof(type) != 'undefined'){
				e.checked == true ? e.checked = false : e.checked = true;
				e.checked == true ? $('.icheckbox_square-green').addClass('checked'):$('.icheckbox_square-green').removeClass('checked');
			}else{
				e.checked = true;
				 $('.icheckbox_square-green').addClass('checked');
			}
		}
	}
}
//删除确认
function delconfirm(txt){
	var msg = txt?txt:"确实要删除此记录吗？";
	return confirm(msg);
}
//隐藏子菜单
function cateopen(id) {
	try{
		var o = $('#cate_' + id);
		if (o == null) return;
		if (o.css('display') == 'none') {
			o.css('display','');
			$('#bt_' + id).attr('class', 'fa fa-chevron-down');
		} else {
			o.css('display','none');
			$('#bt_' + id).attr('class','fa fa-chevron-up');
		}
	} catch(e){}
}
function suggestKey(field, len)
{
	var key = 'abcdefhijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWYXZ~!@$^*()+-,.;[]{}|/';
	var i = 0;
	var suggestKey = '';
	if (!len) len = 16;
	while (i ++ < len)
	{
		suggestKey += key.charAt(Math.random() * key.length);
	}
	$("#"+field).val(suggestKey);
}
/**
* 含文字的模块
*/
function radioWithWords(self)
{
var ele = self.getElementsByTagName('input')[0];
var nm = ele.name;
ele.click();
//ele.checked=true;
//取消其它单选框的选择
var otherInputs = document.getElementsByName(nm);
var l = otherInputs.length;
for(var i=0;i<l;i++)
if(otherInputs[i].parentNode.className=='current')
otherInputs[i].parentNode.className='';
self.className = 'current';
}
