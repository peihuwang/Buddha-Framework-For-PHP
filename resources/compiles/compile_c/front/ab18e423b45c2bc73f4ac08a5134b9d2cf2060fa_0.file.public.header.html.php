<?php
/* Smarty version 3.1.30, created on 2017-07-19 23:33:13
  from "/home/bendishangjia.com/www/resources/views/templates/front/public.header.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_596f7bb94a0507_82604515',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ab18e423b45c2bc73f4ac08a5134b9d2cf2060fa' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/public.header.html',
      1 => 1500478363,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_596f7bb94a0507_82604515 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>本地商家</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta content="telephone=no" name="format-detection">
    <link href="/style/css/web.style.css" rel="stylesheet" type="text/css">
    <?php echo '<script'; ?>
 type="text/javascript" src="/style/js_two/jquery-3.1.1.min.js"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 type="text/javascript">
        var w,h,className;
        function getSrceenWH(){
            w = $(window).width();
            h = $(window).height();
            $('#dialogBg').width(w).height(h);
        }
        window.onresize = function(){
            getSrceenWH();
        };
        $(window).resize();
        $(function(){
            getSrceenWH();
            //显示弹框
            $('.box a').click(function(){
                className = $(this).attr('class');
                $('#dialogBg').fadeIn(300);
                $('#dialog').removeAttr('class').addClass('animated '+className+'').fadeIn();
            });
            //关闭弹窗
            $('.claseDialogBtn').click(function(){
                $('#dialogBg').fadeOut(300,function(){
                    $('#dialog').addClass('bounceOutUp').fadeOut();
                });
            });
            var screenwidth=screen.width;
            setCookie('screenwidth',screenwidth);//将当前屏幕宽度设置为COOKIE便于文字截取
        });
    <?php echo '</script'; ?>
>
    <style type="text/css">
        .animated{
            -webkit-animation-duration:1.4s;
            animation-duration:1.4s;
            -webkit-animation-fill-mode:both;
            animation-fill-mode:both
        }
        @-webkit-keyframes bounceOutUp {
            0% {
                -webkit-transform: translateY(0);
                transform: translateY(0);
            }
            20% {
                opacity: 1;
                -webkit-transform: translateY(20px);
                transform: translateY(20px);
            }
            100% {
                opacity: 0;
                -webkit-transform: translateY(-2000px);
                transform: translateY(-2000px);
            }
        }
        @keyframes bounceOutUp {
            0% {
                -webkit-transform: translateY(0);
                -ms-transform: translateY(0);
                transform: translateY(0);
            }
            20% {
                opacity: 1;
                -webkit-transform: translateY(20px);
                -ms-transform: translateY(20px);
                transform: translateY(20px);
            }
            100% {
                opacity: 0;
                -webkit-transform: translateY(-2000px);
                -ms-transform: translateY(-2000px);
                transform: translateY(-2000px);
            }
        }
        .bounceOutUp {
            -webkit-animation-name: bounceOutUp;
            animation-name: bounceOutUp;
        }
        @-webkit-keyframes flipInX {
            0% {
                -webkit-transform: perspective(400px) rotateX(90deg);
                transform: perspective(400px) rotateX(90deg);
                opacity: 0;
            }
            40% {
                -webkit-transform: perspective(400px) rotateX(-10deg);
                transform: perspective(400px) rotateX(-10deg);
            }
            70% {
                -webkit-transform: perspective(400px) rotateX(10deg);
                transform: perspective(400px) rotateX(10deg);
            }
            100% {
                -webkit-transform: perspective(400px) rotateX(0deg);
                transform: perspective(400px) rotateX(0deg);
                opacity: 1;
            }
        }
        @keyframes flipInX {
            0% {
                -webkit-transform: perspective(400px) rotateX(90deg);
                -ms-transform: perspective(400px) rotateX(90deg);
                transform: perspective(400px) rotateX(90deg);
                opacity: 0;
            }
            40% {
                -webkit-transform: perspective(400px) rotateX(-10deg);
                -ms-transform: perspective(400px) rotateX(-10deg);
                transform: perspective(400px) rotateX(-10deg);
            }
            70% {
                -webkit-transform: perspective(400px) rotateX(10deg);
                -ms-transform: perspective(400px) rotateX(10deg);
                transform: perspective(400px) rotateX(10deg);
            }
            100% {
                -webkit-transform: perspective(400px) rotateX(0deg);
                -ms-transform: perspective(400px) rotateX(0deg);
                transform: perspective(400px) rotateX(0deg);
                opacity: 1;
            }
        }
        .flipInX {
            -webkit-backface-visibility: visible !important;
            -ms-backface-visibility: visible !important;
            backface-visibility: visible !important;
            -webkit-animation-name: flipInX;
            animation-name: flipInX;
        }

        #dialog p,
        #dialog a{font-family:"Microsoft YaHei",Helvetica,Arial,sans-serif; text-decoration:inherit;}
        #dialog p{font-size:12px; padding:0 20px; line-height:28px; margin:0 auto 10px;}
        #dialog .btn{display:block; height:40px; line-height:40px; background-color:#ff6600; font-size:16px; color:#fff; border-radius:6px; margin:10px auto 4px; text-align:center; width:70%;}
        #dialogBg{width:100%;height:100%;background-color:#000000;opacity:.8;filter:alpha(opacity=60);position:fixed;top:0;left:0;z-index:9999;display:none;}
        #dialog{width:80%; height: 340px; display:none; background-color:#ffffff; position:fixed; top:50%; left:50%; margin:-190px 0 0 -40%; z-index:10000; border:1px solid #ccc; border-radius: 10px; -webkit-border-radius: 10px; box-shadow: 3px 2px 4px rgba(0,0,0,0.2); -webkit-box-shadow: 3px 2px 4px rgba(0,0,0,0.2); }
        .dialogTop{width:96%;margin:0 auto; letter-spacing:1px; padding:6px 0;text-align:right;}
    </style>
</head>
<body>
<div id="wrapper">
    <div class="box">
        <a class="flipInX" href="javascript:;" style="position:fixed; top:70%; right:0; z-index:999; ">
            <img src="/style/img_two/index_sq.png" style="width:60px; height:60px;"/>
        </a>
        <div id="dialogBg"></div>
        <div id="dialog" class="animated">
            <div class="dialogTop">
                <a href="javascript:;" class="claseDialogBtn"><img src="/style/img_two/close.png" alt="" style="width:20px; height:20px;"></a>
            </div>
            <a class="btn" href="/index.php?a=register&c=account">商家免费注册获取商店</a>
            <p>免费发布各类产品，需求，招聘，租赁，促销，活动，简介，地址，导航，名片，传单等功能。</p>
            <a class="btn" href="/index.php?a=register&c=account">个人免费注册获取商店</a>
            <p>免费发布需求，租赁，求职等。</p>
            <?php if ($_smarty_tpl->tpl_vars['url']->value) {?>
            <a class="btn" href="<?php echo $_smarty_tpl->tpl_vars['url']->value;?>
">发布需求</a>
            <?php }?>
        </div>
    </div>

</div><?php }
}
