<?php
/* Smarty version 3.1.30, created on 2017-12-21 11:17:07
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a3b27b36efa18_85105569',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '5504f633ea75f57e22521bf87b0577b643b1ef09' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/index.html',
      1 => 1513663791,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_5a3b27b36efa18_85105569 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<link type="text/css" rel="stylesheet" href="/style/css_two/swiper.min.css"/>
<link type="text/css" rel="stylesheet" href="/style/css_two/switch.css"/>
<?php echo '<script'; ?>
 type="text/javascript" src="/style/js_two/swiper.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>

<style>
    #mobile_home_1 div ul{height: 150px; }
    #mobile_home_2 div ul{height: 110px; }
    #mobile_home_3 div ul{height: 110px; }


    /*功能分类 轮播 start*/
    .index_classify_banner{padding-bottom:16px;}
    .index_classify_banner ul li a img{width:50%; margin:0 auto;}
    .index_classify_banner .swiper-pagination-bullet{height:2px; width:7px; opacity:0.3; border-radius:0; vertical-align:middle; background:#848484;}
    .index_classify_banner .swiper-pagination-bullet-active{opacity:0.6; height:2px; background:#ff6600;}

    .w{width: 100%}
    .pr{position:relative;}
    .bw{background-color:#fff;}
    .brb{border-bottom:1px solid #e0e0e0;}
    /*功能分类 轮播 start*/


</style>
<style type="text/css">
#ClickMe {border: 1px solid #C40000;display: block;background: #3ca8ef;width: 100%;height: 2.5rem;margin-top:5px;color: #fff;font-size: 1rem;text-align: center;line-height: 2.5rem}
#goodcover {display: none;position: absolute;top: 0%;left: 0%;width: 100%;height: 200%;background-color: black;z-index: 1001;-moz-opacity: 0.8;opacity: 0.70;filter: alpha(opacity=80);}
#code {background-color: #fff;position: absolute;display: none;left: 45%;z-index: 1002;}
.code-img {width: 250px;margin:0 auto;}
.code-img img {width: 250px;}
</style>
<div id="header">
    <div class="logo"><a href="/"><img src="style/images/logo_q.png"></a></div>
    <div class="nav-wrap-left">
        <a  class="react" href="index.php?a=index&c=city">
            <span class="nav-city"></span>
        </a>
    </div>
    <div class="box-search">
        <a class="react" href="index.php?a=index&c=search">
            <i class="icon-search text-icon">⌕</i> <span class="single-line">输入商家/品类/商圈</span>
        </a>
    </div>
    <div id="agenttel" style="top:2px;">
        <span style="width: 29px;height: 29px;"></span>
        <div class="tel">代理商电话<a href="tel:<?php echo $_smarty_tpl->tpl_vars['referral']->value['tel'];?>
"><?php echo $_smarty_tpl->tpl_vars['referral']->value['tel'];?>
</a></div>
    </div>
</div>
<?php if ($_smarty_tpl->tpl_vars['judge']->value == 1) {?>
<div id="ClickMe">
    点击关注公众号
</div>
<?php }?>
<div id="focus" class="focus hide" style="height: 131px;margin-top: 5px;">
<div class="hd"><ul></ul></div>
<div class="bd " id="mobile_home_1"></div>
</div>
<!--网站导航-->


<!--功能分类 轮播 start-->
<div class="index_classify_banner swiper-container w pr bw brb">
    <div class="swiper-wrapper">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['indexnav']->value['nature'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <div class="swiper-slide">
            <ul class="w clearfloat">
                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['item']->value, 'natureitem', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['natureitem']->value) {
?>
                <li class="w20 fl">
                    <a class="w tc pt10 pb10"
                       <?php if ($_smarty_tpl->tpl_vars['natureitem']->value['is_show'] == 1) {?>   href="<?php echo $_smarty_tpl->tpl_vars['natureitem']->value['url'];
if ($_smarty_tpl->tpl_vars['natureitem']->value['p_url'] == 1) {
echo $_smarty_tpl->tpl_vars['location_area']->value;
}?>"  <?php } else { ?> onclick="tangkuang()"  <?php }?> >
                        <img src="<?php echo $_smarty_tpl->tpl_vars['natureitem']->value['img'];?>
" style="width: 80%"/>
                        <!--<p class="f12 cb">中介</p>-->
                    </a>
                </li>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

            </ul>
        </div>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

    </div>
    <!-- Add Pagination -->
    <div class="swiper-pagination" style="bottom:6px;"></div>
</div>
<!--功能分类 轮播 end-->

<!--功能分类 轮播 start-->
<div class="index_classify_banner swiper-container w pr bw brb">
    <div class="swiper-wrapper">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['indexnav']->value['type'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <div class="swiper-slide">
            <ul class="w clearfloat">
                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['item']->value, 'typeitem', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['typeitem']->value) {
?>
                <li class="w20 fl">
                    <a class="w tc pt10 pb10"
                       <?php if ($_smarty_tpl->tpl_vars['typeitem']->value['is_show'] == 1) {?>   href="<?php echo $_smarty_tpl->tpl_vars['typeitem']->value['url'];
if ($_smarty_tpl->tpl_vars['typeitem']->value['p_url'] == 1) {
echo $_smarty_tpl->tpl_vars['location_area']->value;
}?>"  <?php } else { ?> onclick="tangkuang()"  <?php }?> >
                        <img src="<?php echo $_smarty_tpl->tpl_vars['typeitem']->value['img'];?>
" style="width: 80%"/>
                        <!--<p class="f12 cb">中介</p>-->
                    </a>
                </li>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

            </ul>
        </div>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

    </div>
    <!-- Add Pagination -->
    <div class="swiper-pagination" style="bottom:6px;"></div>
</div>
<!--功能分类 轮播 end-->


<!--广告位-->
<div id="focus1" class="focus hide">
    <div class="hd"><ul></ul></div>
    <div class="bd " id="mobile_home_2" style="height: 100px;"></div>
</div>
<!-- END -->
<div id="leftTabBox" class="business" >
    <div class="hd">
        <ul>
            <li class="on"><span>最新需求</span></li>
            <li><span>最新招聘</span></li>
            <li><span>今日促销</span></li>
            <li><span>热门活动</span></li>
        </ul>
    </div>
    <div class="bd">
        <ul id="Demand_Nws"></ul>
        <ul id="Regio_Nws"></ul>
        <ul id="shop_Pro"></ul>
        <ul id="shop_Hot"> </ul>
    </div>
</div>
<!--广告位-->
<div id="focus2" class="focus hide">
    <div class="hd"><ul></ul></div>
    <div class="bd " id="mobile_home_3" style="height: 100px;"></div>
</div>
<!-- END -->
<div id="leftTabhot" class="busihot" style="margin-bottom: 20px;">
<div class="hd">
    <ul>
        <li class="on"><span>最新供应</span></li>
        <li><span>推荐商家</span></li>
        <li><span>最近开业</span></li>
        <li><span>最新租赁</span></li>
    </ul>
</div>
<div class="bd">
    <ul id="goods_nws"></ul>
    <ul id="shop_Rec"></ul>
    <ul id="shop_nws"> </ul>
    <ul id="lease_Nws"></ul>
</div>
</div>

<div id="goodcover"></div>
<div id="code">
  <div class="code-img"><img id="ewmsrc"  src="style/images/qrcode_for_gh_0f246af8a3ff_430.jpg"/></div>
</div>

<?php echo '<script'; ?>
 type="text/javascript">
    function tangkuang(){
        Message.showMessage('功能正在审核中，敬请期待！');
    }
    $(function() {
    //alert($(window).height());
    $('#ClickMe').click(function() {
        $('#code').center();
        $('#goodcover').show();
        $('#code').fadeIn();
    });
    $('#closebt').click(function() {
        $('#code').hide();
        $('#goodcover').hide();
    });
    $('#goodcover').click(function() {
        $('#code').hide();
        $('#goodcover').hide();
    });
    /*var val=$(window).height();
    var codeheight=$("#code").height();
    var topheight=(val-codeheight)/2;
    $('#code').css('top',topheight);*/
    jQuery.fn.center = function(loaded) {
        var obj = this;
        body_width = parseInt($(window).width());
        body_height = parseInt($(window).height());
        block_width = parseInt(obj.width());
        block_height = parseInt(obj.height());

        left_position = parseInt((body_width / 2) - (block_width / 2) + $(window).scrollLeft());
        if (body_width < block_width) {
            left_position = 0 + $(window).scrollLeft();
        };

        top_position = parseInt((body_height / 2) - (block_height / 2) + $(window).scrollTop());
        if (body_height < block_height) {
            top_position = 0 + $(window).scrollTop();
        };

        if (!loaded) {

            obj.css({
                'position': 'absolute'
            });
            obj.css({
                'top': ($(window).height() - $('#code').height()) * 0.5,
                'left': left_position
            });
            $(window).bind('resize', function() {
                obj.center(!loaded);
            });
            $(window).bind('scroll', function() {
                obj.center(!loaded);
            });

        } else {
            obj.stop();
            obj.css({
                'position': 'absolute'
            });
            obj.animate({
                'top': top_position
            }, 200, 'linear');
        }
    }
})

<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/TouchSlide/TouchSlide.1.1.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript" src="https://3gimg.qq.com/lightmap/components/geolocation/geolocation.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/home.js"><?php echo '</script'; ?>
>


<!-- Initialize Swiper -->
<?php echo '<script'; ?>
>
    var swiper = new Swiper('.index_banner', {
        autoplay: 3000,
        pagination: '.swiper-pagination',
        paginationClickable: true
    });
    var swiper1 = new Swiper('.index_broadcast', {
        autoplay: 3000,
        direction: 'vertical',
        slidesPerView: 1,
        loop:true
    });
    var swiper2 = new Swiper('.index_classify_banner', {
        pagination: '.swiper-pagination',
        paginationClickable: true
    });
    var swiper3 = new Swiper('.index_list_banner1', {
        autoplay: 3000,
        slidesPerView: 'auto',
        slidesOffsetBefore: 10,
        loop: true
    });
    var swiper4 = new Swiper('.index_tab_item1', {
        onTransitionEnd: function(swiper){
            $('.index_tab_list1 ul li').eq(swiper4.activeIndex).siblings("li").children("a").removeClass("active");
            $('.index_tab_list1 ul li').eq(swiper4.activeIndex).children("a").addClass("active");
        }
    });
    $(".index_tab_list1 ul li").click(function(){
        $(this).siblings("li").children("a").removeClass("active");
        $(this).children("a").addClass("active");
        swiper4.slideTo($(this).index(), 500, false)
    });
    var swiper5 = new Swiper('.index_list_banner2', {
        autoplay: 3000,
        slidesPerView: 'auto',
        slidesOffsetBefore: 10,
        loop: true
    });
    var swiper6 = new Swiper('.index_tab_item2', {
        onTransitionEnd: function(swiper){
            $('.index_tab_list2 ul li').eq(swiper6.activeIndex).siblings("li").children("a").removeClass("active");
            $('.index_tab_list2 ul li').eq(swiper6.activeIndex).children("a").addClass("active");
        }
    });
    $(".index_tab_list2 ul li").click(function(){
        $(this).siblings("li").children("a").removeClass("active");
        $(this).children("a").addClass("active");
        swiper6.slideTo($(this).index(), 500, false)
    });
<?php echo '</script'; ?>
>








<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php }
}
