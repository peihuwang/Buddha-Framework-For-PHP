<?php
/* Smarty version 3.1.30, created on 2017-05-27 08:38:00
  from "/home/bendishangjia.com/www/resources/views/templates/public/redirectofmobile.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5928ca6812d919_55107995',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9f6b6b4084c4b6006ec206d056934aebfdc4b537' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/public/redirectofmobile.html',
      1 => 1495845346,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5928ca6812d919_55107995 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
<title>404错误</title>
<style>
html, body {height: 100%; overflow: hidden}
body {background-image: url("/style/images/blueprint.png");  background-color: #f2f2f2;  color: #444;  font: 12px/1.5 'Helvetica Neue',Arial,Helvetica,sans-serif;
	padding:0; margin:0;}
div#da-wrapper, div#da-wrapper.fluid {  width: 100%;  }
div#da-content {  clear: both;  padding-bottom: 58px;  }
div#da-wrapper {  height: auto;  min-height: 100%;  position: relative;  min-width: 320px;  }
div#da-wrapper .da-container, div#da-wrapper.fluid .da-container {  width:100%;  margin: auto;  }
div#da-error-wrapper {  width: 320px;  padding:50px 0 0;  margin: auto;  position: relative;  }
div#da-error-wrapper #da-error-pin {  width: 38px;  height: 38px;  display: block;  margin: auto;  margin-bottom: -27px;  z-index: 10;  position: relative;  background: url("/style/images/error-pin.png") no-repeat center center;  }
div#da-error-wrapper #da-error-code {  width:300px;  height: 170px;  padding: 127px 16px 0 16px;  position: relative;  margin: auto;  margin-bottom: 20px;  z-index: 5;  line-height: 1;  font-size:14px;  text-align: center;  background: url("/style/images/error-hanger.png") no-repeat center center;  -webkit-transform-origin: center top;  -moz-transform-origin: center top;  transform-origin: center top;  -webkit-animation: error-swing infinite 2s ease-in-out alternate;  -moz-animation: error-swing infinite 2s ease-in-out alternate;  animation: error-swing infinite 2s ease-in-out alternate; }
div#da-error-wrapper #da-error-code span {  font-size:30px;  display: block; padding-bottom:20px;;  }
@-webkit-keyframes error-swing { 0% {-webkit-transform: rotate(1deg)  } 100% {-webkit-transform: rotate(-2deg)} }
@-moz-keyframes error-swing { 0% {  -moz-transform: rotate(1deg)  } 100% {  -moz-transform: rotate(-2deg)  } }
@keyframes error-swing { 0% {  transform: rotate(1deg)  } 100% {  transform: rotate(-2deg)  } }
div#da-error-wrapper .da-error-heading {  color: #f60;  text-align: center;  font-size: 20px;  font-family: Georgia,"Times New Roman",Times,serif;  }
div#da-error-wrapper p { text-align: center;  font-size: 14px;}
div#da-error-wrapper p a {  margin: 5px;  color: #fff;  background: #f60;  text-decoration: none;  padding: 1px 6px;  display: inline-block;  -webkit-border-radius: 4px;  -o-border-radius: 4px;  -moz-border-radius: 4px;  border-radius: 4px;  }
div#da-footer {  background: #fff;  border-top: 1px solid #d0d0d0;  text-align: center;  position: absolute;  bottom: 0;  width: 100%;  }
</style>
</head>
<body>

<div id="da-wrapper" class="fluid">
	<!-- Content -->
	<div id="da-content">
		<!-- Container -->
		<div class="da-container clearfix">

			<div id="da-error-wrapper">

				<div id="da-error-pin"></div>
				<div id="da-error-code"><span>提示</span><p><?php echo $_smarty_tpl->tpl_vars['msg']->value;?>
</p></div>
				<h1 class="da-error-heading"></h1>
				<p><br>
					<a href="/">去首页看看</a>
					<a href="<?php echo $_smarty_tpl->tpl_vars['url']->value;?>
">点击跳转</a>
					<?php echo '<script'; ?>
 type="text/javascript">
						setTimeout("window.location.href ='<?php echo $_smarty_tpl->tpl_vars['url']->value;?>
';", <?php if ($_smarty_tpl->tpl_vars['time']->value) {
echo $_smarty_tpl->tpl_vars['time']->value;
} else { ?>500<?php }?>);
					<?php echo '</script'; ?>
>
					<a href="<?php if ($_smarty_tpl->tpl_vars['url']->value) {
echo $_smarty_tpl->tpl_vars['url']->value;
} else { ?>javascript:history.go(-1)<?php }?>">返回</a>

			</div>
		</div>
	</div>

	<!-- Footer -->
	<div id="da-footer">
		<div class="da-container clearfix">
			<p>本地商家网</p></div>
	</div>
</div>
</body>
</html><?php }
}
