<?php
/* Smarty version 3.1.30, created on 2017-11-23 21:59:44
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/public.headernew.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a16d45087f5d0_32299900',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '875be063259ad19dfa87e0562fd42d9997564837' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/public.headernew.html',
      1 => 1509096884,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a16d45087f5d0_32299900 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>本地商家</title>
    <meta name="toTop" content="true">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"  />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no"/>
    <meta name="applicable-device" content="mobile"/>

    <link type="text/css" rel="stylesheet" href="/style/css_two/reset.css"/>
    <link type="text/css" rel="stylesheet" href="/style/css_two/style.css"/>
    <link type="text/css" rel="stylesheet" href="/style/css_two/date.css"/><!--首页、-->
    <link type="text/css" rel="stylesheet" href="/style/css_two/switch.css"/><!--首页、-->
    <link type="text/css" rel="stylesheet" href="/style/css_two/swiper.min.css"/><!--首页、热门活动（首页和个人中心）-->
    <?php echo '<script'; ?>
 type="text/javascript" src="/style/js_two/jquery-3.1.1.min.js"><?php echo '</script'; ?>
><!--首页、-->
    <?php echo '<script'; ?>
 type="text/javascript" src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
><!---提示信息必须引用 -->
    <?php echo '<script'; ?>
 type="text/javascript" src="/style/js_two/toTop.js"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 type="text/javascript" src="/style/js_two/swiper.min.js"><?php echo '</script'; ?>
><!--首页-->
    <?php echo '<script'; ?>
 type="text/javascript" src="/style/js_two/iscroll.js"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 type="text/javascript" src="/style/js_two/navbarscroll.js"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 type="text/javascript" src="/style/js/public.js"><?php echo '</script'; ?>
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
        //浮动导航条拖动
        $(function () {
            var box = document.getElementById('fd');
            box.onmousedown = function (event) {
                var e = event || window.event,
          t = e.target || e.srcElement,
                //鼠标按下时的坐标x1,y1
          x1 = e.clientX,
          y1 = e.clientY,
                //鼠标按下时的左右偏移量
          dragLeft = this.offsetLeft,
          dragTop = this.offsetTop;
                document.onmousemove = function (event) {
                    var e = event || window.event,
          t = e.target || e.srcElement,
                    //鼠标移动时的动态坐标
          x2 = e.clientX,
          y2 = e.clientY,
                    //鼠标移动时的坐标的变化量
          x = x2 - x1,
          y = y2 - y1;
                    box.style.left = (dragLeft + x) + 'px';
                    box.style.top = (dragTop + y) + 'px';
                }
                document.onmouseup = function () {
                    this.onmousemove = null;
                }
            }
        });
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
        <a id="fd" class="flipInX" href="javascript:;" style="position:fixed; top:70%; right:0; z-index:999; ">
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
