<?php
/* Smarty version 3.1.30, created on 2018-01-30 23:03:22
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/manager.edit.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a70893ac27501_56240293',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '41cb6e6fe66b7e544ecaf0e8357749584eeb619b' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/manager.edit.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a70893ac27501_56240293 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>操作员列表</title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/font-awesome.min.css" rel="stylesheet">
<link href="css/animate.min.css" rel="stylesheet">
<link href="css/style.min.css" rel="stylesheet">
<link href="css/plugins/iCheck/custom.css" rel="stylesheet">
<?php echo '<script'; ?>
 src="js/jquery.js"><?php echo '</script'; ?>
> 
<?php echo '<script'; ?>
 src="js/main.js?v=1.0"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="js/jquery.form.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="js/plugins/iCheck/icheck.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
$(document).ready(function(){$(".i-checks").iCheck({checkboxClass:"icheckbox_square-green",radioClass:"iradio_square-green"})});
<?php echo '</script'; ?>
>
</head>
<body class="gray-bg">
<div class="row wrapper border-bottom white-bg page-heading">
<div class="col-sm-12">
<h3>管理员管理</h3><ol class="breadcrumb"><li>当前位置</li><li>管理员管理</li><li> <strong>管理员修改</strong></li></ol>
</div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
<p>
<a href="index.php?a=more&c=manager" class="btn btn-primary">返回上一步</a>
</p>

<form method="post" action="" onsubmit="return checkpost(document.checkform);" name="checkform">
<div class="float-e-margins">
<div class="ibox-title"><b>管理员信息</b></div>
</div>
<div class="ibox-content">
<table class="table table-hover m-b-none">
<tbody>
<input type="hidden" name="id" value="<?php echo $_smarty_tpl->tpl_vars['optionList']->value['id'];?>
">
<tr>
	<td width="120">所属部门</td>
	<td width="300">
		<select name="typeid" class="form-control">
			<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['utypearr']->value, 'val');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['val']->value) {
?>
				<option value="<?php echo $_smarty_tpl->tpl_vars['val']->value['id'];?>
" <?php if ($_smarty_tpl->tpl_vars['val']->value['id'] == $_smarty_tpl->tpl_vars['optionList']->value['utype']) {?> selected <?php }?>><?php echo $_smarty_tpl->tpl_vars['val']->value['name'];?>
</option>
			<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

		</select>
	</td>
	<td></td>
</tr>
<tr>
	<td>用户名 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['optionList']->value['username'];?>
" name="username" id="username"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>昵&nbsp;称 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['optionList']->value['nickname'];?>
" name="name" id="username"  class="form-control"></td>
	<td></td>
</tr>
<tr>
	<td>手机号 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['optionList']->value['mobile'];?>
" name="mobile" id="mobile" class="form-control"></td>
	<td></td>
</tr>
<tr>
	
	<td>邮箱 <span class="text-danger">*</span></td>
	<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['optionList']->value['email'];?>
" name="email" id="email"  class="form-control"></td>
	<td></td>
</tr>
<tr>	<input type="hidden" name="pwd" value="<?php echo $_smarty_tpl->tpl_vars['optionList']->value['password'];?>
">
	<td>密码 <span class="text-danger">*</span></td>
	<td><input type="password" value="" name="password" id="password" class="form-control"></td>
	<td></td>
	
</tr>
<tr>
	<td>确认密码 <span class="text-danger">*</span></td>
	<td><input type="password" value="" name="pasw" id="pasw" class="form-control"></td>
	<td></td>
	
</tr>
<tr>
	<td>状态 <span class="text-danger">*</span></td>
	<td><select name="state" class="form-control">
			<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['statearr']->value, 'val');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['val']->value) {
?>
			<option value="<?php echo $_smarty_tpl->tpl_vars['val']->value['id'];?>
" <?php if ($_smarty_tpl->tpl_vars['val']->value['id'] == $_smarty_tpl->tpl_vars['optionList']->value['state']) {?> selected <?php }?>><?php echo $_smarty_tpl->tpl_vars['val']->value['name'];?>
</option>
			<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

		</select></td>
	<td></td>
</tr>
</tbody>
</table>
</div>


<div class="text-center m-t-sm">
<button type="submit" class="btn btn-primary">提 交</button>
</div>
</form>
</div>
</div>
<link rel="stylesheet" href="js/datetimepicke/datetimepicker.css">
<?php echo '<script'; ?>
 src="js/datetimepicke/datetimepicker.full.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
function checkpost(obj){
	if($('#username').val()==''){
		alert("用户名不能为空");
		$("#username").focus();
		return false;
	}
	if($('#mobile').val()==''){
		alert('输入手机');
		$('.email').focus();
		return false;
		}
	if (!$("#mobile").val().match(/^1[34578]\d{9}$/)) { 
		alert("手机号码格式不正确！"); 
		$('.mobile').focus();
		return false;
		}
//	if($('#email').val()==''){
//		alert('输入邮箱');
//		$('.email').focus();
//		return false;
//		}
	if (!$("#email").val().match(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/)) { 
		alert("邮箱格式不正确"); 
		$("#email").focus();
		return false;
	}
	if($('#password').val()!=''){
		if($('#password').val().length<6){
			alert('输入密码,不少于6位');
			$('.password').focus();
			return false;
		}
	}

	if($('#password').val()!='' &&  $('#password').val()!=$('#pasw').val()){
		alert('两次密码不一致');
		$('.password').focus();
		return false;
	}

    if($('#usercode').val()=='' || $('#usercode').val().length != 18){ 
		alert('请输入18位正确身份证号'); 
		$('#usercode').focus();
        return false;
	}
}
(function($){$.extend({ms_DatePicker:function(options){var defaults={YearSelector:"#sel_year",MonthSelector:"#sel_month",DaySelector:"#sel_day",FirstText:"--",FirstValue:0};var opts=$.extend({},defaults,options);var $YearSelector=$(opts.YearSelector);var $MonthSelector=$(opts.MonthSelector);var $DaySelector=$(opts.DaySelector);var FirstText=opts.FirstText;var FirstValue=opts.FirstValue;var str='<option value="'+FirstValue+'">'+FirstText+"</option>";$YearSelector.html(str);$MonthSelector.html(str);$DaySelector.html(str);var yearNow=new Date().getFullYear();var yearSel=$YearSelector.attr("rel");for(var i=yearNow;i>=1900;i--){var sed=yearSel==i?"selected":"";var yearStr='<option value="'+i+'" '+sed+">"+i+"</option>";$YearSelector.append(yearStr)}var monthSel=$MonthSelector.attr("rel");for(var i=1;i<=12;i++){var sed=monthSel==i?"selected":"";if(i<10){i='0'+i};var monthStr='<option value="'+i+'" '+sed+">"+i+"</option>";$MonthSelector.append(monthStr)}function BuildDay(){if($YearSelector.val()==0||$MonthSelector.val()==0){$DaySelector.html(str)}else{$DaySelector.html(str);var year=parseInt($YearSelector.val());var month=parseInt($MonthSelector.val());var dayCount=0;switch(month){case 1:case 3:case 5:case 7:case 8:case 10:case 12:dayCount=31;break;case 4:case 6:case 9:case 11:dayCount=30;break;case 2:dayCount=28;if((year%4==0)&&(year%100!=0)||(year%400==0)){dayCount=29}break;default:break}var daySel=$DaySelector.attr("rel");for(var i=1;i<=dayCount;i++){var sed=daySel==i?"selected":"";if(i<10){i='0'+i}var dayStr='<option value="'+i+'" '+sed+">"+i+"</option>";$DaySelector.append(dayStr)}}}$MonthSelector.change(function(){BuildDay()});$YearSelector.change(function(){BuildDay()});if($DaySelector.attr("rel")!=""){BuildDay()}}})})(jQuery);

$(function(){
	$.ms_DatePicker()
//日期时间
$('.some_class').datetimepicker({
	lang:'ch',
	timepicker:false,
	format:'Y-m-d',
	minDate:'-1970/01/02',
});

$('#Card').find('input[type="checkbox"]').on('click',function(){
var type=$('.c-checked').is(':checked');
if(type){
	$('.state').prop('disabled','');

	}else{
	$('.state').prop('disabled','disabled');
	}




})


$('#usercode').blur(function(){
 var usercode = $('#usercode').val();
 var date = new Date();
 var year = date.getFullYear(); 
 var birthday_year = parseInt(usercode.substr(6,4));
 var userage= year - birthday_year;
  if($('#usercode').val()=='' || $('#usercode').val().length != 18){ 
		alert('请输入18位正确身份证号'); 
        return false;
	}
	 if(usercode!=''){
		 $('#age').val(userage);
		 $('#birthday').text(userage)
	 }
 })
 
})
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="js/city/jquery.cxselect.min.js"><?php echo '</script'; ?>
> 
<?php echo '<script'; ?>
 type="text/javascript">
	$('#province').cxSelect({
	url: 'js/city/cityData.min.json',
	selects:['province', 'city', 'area'],
});
<?php echo '</script'; ?>
> 
</body>
</html>
<?php }
}
