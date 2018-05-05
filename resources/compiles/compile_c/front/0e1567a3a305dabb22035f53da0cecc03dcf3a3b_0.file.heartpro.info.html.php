<?php
/* Smarty version 3.1.30, created on 2017-12-23 14:12:02
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/heartpro.info.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a3df3b20fbd06_45170710',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0e1567a3a305dabb22035f53da0cecc03dcf3a3b' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/heartpro.info.html',
      1 => 1514001778,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.recommend.html' => 1,
    'file:../front/public.heartpro.html' => 1,
  ),
),false)) {
function content_5a3df3b20fbd06_45170710 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/bendishangjia.com/vendor/smarty/plugins/modifier.date_format.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>本地商家</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"  />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no"/>
    <meta name="applicable-device" content="mobile"/>
    <link href="/style/css_two/reset.css" rel="stylesheet" type="text/css" />
    <link href="/style/css_two/style.css" rel="stylesheet" type="text/css" />
    <link href="/style/css_two/swiper.min.css" rel="stylesheet" type="text/css" />
    <link href="/style/css_two/buy.css" rel="stylesheet" type="text/css" />
<style>
    .vote_content .wall-column{width: 33.3%}
</style>
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
    <?php echo '<script'; ?>
>

//        function userlogn(msg)
//        {
//            Message.showConfirm(msg, "确定", "取消", function ()
//            {
//                var dq_url= window.location.search;
//                var dq_url=$.base64.btoa(a);//加密
//                // console.log(a===$.base64.atob(b));//解密
//                //将要 跳转的URL
//                var dl_url =  'index.php?a=login&c=account';
//                var url =  dl_url+'&backreturnurl='+dq_url;
//
//                window.location.href = '' + url + '';
//            });
//        }


        function select_spec(event)
        {
            var votecount = $('#votecount').val(),//最少投票量
                islog =  $("input[name='islog']").val(),//是否登录
                isjoin = $("input[name='isjoin']").val(),//是否参与
                is_buy = $("input[name='is_buy']").val(),//是否购买
                current = $("input[name='current']").val();//当前用户投票量
            if(islog==0)
            {
                var msg='请您登陆后再参加竞买或帮助朋友投上您的爱心确认登录吗？';
                userlogn(msg);
                // alert('你还未登陆，请登陆后再操作！');
                return false;
            }else{
                if(is_buy==0)
                {
                    alert('你已经购买过了,请不要重复购买！');
                    return false;
                }else{
                    if(isjoin==0)
                    {
                        alert('你还未参与竞买，快去参与后再竞买吧！');
                        return false;
                    }else{
                        if(current < votecount)
                        {
                            alert('你当前票数不足，不能支付，快去找人帮你投票吧！');
                            return false;
                        }else{
                            $("#choose_attr .f-foot a").removeClass('buy-goods');
                            $(".mask-div").show();
                            $('#public_foot').css('display','none');
                            var total = 0, h = $(window).height(), top = $('.f-title-attr').height() || 0, bottom = $('.f-foot').height() || 0, con = $('.f-content-attr');
                            var li_length=con.find('li').length;
                            if(li_length>1){
                                $("#choose_attr").animate({
                                    height: '70%'
                                }, [10000]);
                                total = 0.7 * h;
                            }
                            else
                            {
                                $("#choose_attr").animate({
                                    height: '55%'
                                }, [10000]);
                                total = 0.55 * h;
                            }
                            $("#choose_attr .f-foot a").addClass(event);
                            con.height(total - top - bottom-24+ 'px');
                            scrollheight = $(document).scrollTop();
                            $("body").css("top","-" + scrollheight+"px");
                            $("body").addClass("visibly");
                            $("#choose_attr .f-foot a").removeClass('add-cart');

                        }
                    }
                }
            }
        }

        function close_choose_spec()
        {
            $(".mask-div").hide();
            $("body").css("top","auto");
            $("body").removeClass("visibly");
            $(window).scrollTop(scrollheight);
            $('#public_foot').css('display','');
            $('#choose_attr').animate({
                height: '0'
            }, [10000]);
        }
    <?php echo '</script'; ?>
>
</head>
<body class="bg" style="width:100%;">
<div id="main">
    <!--头部 start-->
    <div class="top w by pf">
        <a class="back pa" href="javascript:history.go(-1);">
            <img src="/style/img_two/back.png"/>
        </a>
        <p class="f18 cw w tc"><?php echo $_smarty_tpl->tpl_vars['Act']->value['name'];?>
</p>
    </div>

    <!--头部 end-->

    <!--轮播图 start-->
    <div class="localinfo_banner pr h" style="margin-top:44px;">
        <div class="swiper-container w h">
            <div class="swiper-wrapper">
                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['img']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
                <div class="swiper-slide">
                    <img class="w" src="<?php echo $_smarty_tpl->tpl_vars['item']->value['goods_img'];?>
" alt="" style="height: 200px;">
                </div>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
        </div>
    </div>
    <!--轮播图 end-->
    <input type="hidden" name="id" value="<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
">

    <!--投票简介 start-->
    <div class="lease_detail_content bw">
        <div class="w clearfloat pt10 pb10">
            <div class="w333 tc fl" style="margin-bottom: 5px;">
                <p class="f14 cg">单品投票数量</p>
                <p class="f14 cy"><?php echo $_smarty_tpl->tpl_vars['Act']->value['votecount'];?>
</p>
            </div>
            <div class="w333 tc fl" style="margin-bottom: 5px;">
                <p class="f14 cg brl brr">拍品数量</p>
                <p class="f14 cy brl brr"><?php echo $_smarty_tpl->tpl_vars['Act']->value['stock'];?>
</p>
            </div>
            <div class="w333 tc fl" style="margin-bottom: 5px;">
                <p class="f14 cg">访问量</p>
                <p class="f14 cy"><?php echo $_smarty_tpl->tpl_vars['Act']->value['click_count'];?>
</p>
            </div>
            <div class="w333 tc fl" style="margin-bottom: 5px;">
                <p class="f14 cg">参与人数</p>
                <p class="f14 cy"><?php echo $_smarty_tpl->tpl_vars['count']->value;?>
</p>
            </div>
            <div class="w333 tc fl" style="margin-bottom: 1px;border-right: 1px solid #E6E6E6;border-left: 1px solid #E6E6E6;">
                <p class="f14 cg">累计投票</p>
                <p class="f14 cy"><?php echo $_smarty_tpl->tpl_vars['heartplus_num']->value;?>
</p>
            </div>
            <div class="w333 tc fl" style="margin-bottom: 5px;">
                <p class="f14 cg">当前排名</p>
                <p class="f14 cy" id="currentrankings"></p>
            </div>
        </div>
        <!--<div class="w clearfloat pt10 pb10 brb" style="overflow:hidden">-->
            <!--<div class="pl10 pr10" style="display:inline-block;width: 100%">-->
                <!--<span class="f14 cb">-->
                    <!--<img src="/style/img_two/icon_time.png" alt="" style="width:18px; vertical-align:middle;">-->
                    <!--竞买日期：<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['Act']->value['applystarttime'],"%m/%d %H:%M");?>
 至 <?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['Act']->value['applyendtime'],"%m/%d %H:%M");?>
</span>-->
                <!--<span style="float:right;margin-right: 20px;border: 5px solid red;background-color: red;color:white;" onclick="shop_url(<?php echo $_smarty_tpl->tpl_vars['Act']->value['shop_id'];?>
)">进入店铺</span>-->
            <!--</div>-->
        <!--</div>-->
        <div class="w clearfloat pt10 pb10 brb">
            <div class="pl10 pr10">
                <p class="f14 cb">
                    <img src="/style/img_two/icon_time.png" alt="" style="width:18px; vertical-align:middle;">
                    <!--活动倒计时 start-->
                    1分购竞买倒计时：<span id="countdown"></span>
                    <!--活动倒计时 end-->
                </p>
            </div>
        </div>
        <div class="w clearfloat pt10 pb10 brb">
            <div class="pl10 pr10">
                <p class="f14 cb">
                    <img src="/style/img_two/icon_rule.png" alt="" style="width:20px;height: 20px; vertical-align:middle;">
                    <!--活动倒计时 start-->
                    <span>1分购活动规则：</span><span style="line-height: 25px;">竞买人邀请好友协助投票，投票数达到"单品投票数"即可0.01元购得拍品,数量有限,先"到"先得！</span>
                    <!--活动倒计时 end-->
                </p>
            </div>
        </div>
        <input type="hidden" id="endtime" value='<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['Act']->value['applyendtime'],"%Y/%m/%d %H:%M:%S");?>
'>
    </div>
    <!-- 投票简介 end-->
    <div class="bord"></div>

    <!-- 搜索商家描述 start-->
    <div class="vote_search clearfloat pl10 pr10" style="overflow:hidden">
        <input class="search_text bw pl10 fl br3 bor" type="search" placeholder="请输入名称或编号">
        <a class="search_btn f14 cw fr bty ml10 tc br3" href="javascript:void(0);" onclick="ajax_list(1,1)">搜索</a>
    </div>
    <!-- 搜索商家描述 end-->
    <div class="bord"></div>

    <!-- 投票分页 start-->
    <div class="vote_tab clearfloat pl10 pr10">
        <a class="f14 cg bw bor tc br3 ml10 mr10 fl" href="<?php echo $_smarty_tpl->tpl_vars['url']->value['prize'];?>
">活动二维码</a>
        <a class="f14 cg bw bor tc br3 ml10 mr10 fl" href="<?php echo $_smarty_tpl->tpl_vars['url']->value['prize'];?>
">活动详情</a>
        <!--<a class="f14 cg bw bor tc br3 ml10 mr10 fl" href="<?php echo $_smarty_tpl->tpl_vars['url']->value['ranking'];?>
">竞买排名</a>-->
        <!--<a class="f14 cg bw bor tc br3 ml10 mr10 fl" href="<?php echo $_smarty_tpl->tpl_vars['url']->value['sign'];?>
">我要申请参与</a>-->
        <p class="f14 cg bw bor tc br3 ml10 mr10 fl" href="" onclick="activity_pro()" style="display: inline-block;height: 28px;line-height: 28px;width: calc(33% - 22px);color: #fff;background-color: red;">我要竞买</p>
    </div>
    <!-- 投票分页 end-->
    <div class="bord"></div>

    <!--投票内容 start-->
    <div class="vote_content tc pr" style="overflow:hidden">
        <ul class="tab clearfloat ma bor" id="title" >
            <li class="fl w50">
                <a class="f14 active" href="javascript:void(0);" onclick="ajax_list(1,2)" data-index="2">人气排序</a>
            </li>
            <li class="fl w50">
                <a class="f14" href="javascript:void(0);" onclick="ajax_list(1,3)" data-index="3">最新竞买</a>
            </li>
            <!--<li class="fl w50" style="width: 33.1%;">-->
                <!--<a class="f14" href="javascript:void(0);" onclick="ajax_list(1,4)" data-index="3">我参与</a>-->
            <!--</li>-->
        </ul>
        <div class="bord"></div>

        <ul class="wall clearfloat" id="list" data-c="<?php echo $_smarty_tpl->tpl_vars['c']->value;?>
"></ul>
        <div id="nomore" class="mc tc f14 cg" style="line-height: 30px;margin-top: 10px;"><a id="click_more" href=""></a></div>
    </div>
    <br/>
    <br/>

    <?php $_smarty_tpl->_subTemplateRender("file:public.recommend.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>


    <!--投票内容 end-->

<!--////////////////////////////////////////-->

    <section class="mask-div" style="display: none;"></section>
    <section class="f-block" id="choose_attr" style="height: 0; overflow: hidden;">
        <div class="f-title-attr">
            <img class="SZY-GOODS-IMAGE-THUMB" src="<?php echo $_smarty_tpl->tpl_vars['Act']->value['small'];?>
" style="float: left;height: 80px;80px;">
            <div class="f-title-arr-right">
                <span class="goodprice SZY-GOODS-PRICE price-color" id="moneys" style="color:red;"><em>￥<b><?php echo $_smarty_tpl->tpl_vars['Act']->value['price'];?>
</b></em></span>
                <span class="SZY-GOODS-NUMBER" id="stock">库存：<em id="stocknum"><?php echo $_smarty_tpl->tpl_vars['Act']->value['stock'];?>
</em> 件</span>
            </div>
            <a class="c-close-attr" href="javascript:close_choose_spec()"></a>
            <div style="height: 0px; line-height: 0px; clear: both;"></div>
        </div>
        <div class="f-content-attr">
            <ul class="navContent choose" style="display: block;">
                <!-- 产品规格 -->
                <?php if ($_smarty_tpl->tpl_vars['attr']->value) {?>
                <li>
                    <?php if ($_smarty_tpl->tpl_vars['attr']->value['attrname1']) {?>
                    <div class="title1" style="width: 100%;"><?php echo $_smarty_tpl->tpl_vars['attr']->value['attrname1'];?>
</div>
                    <div class="lbdiv">
                        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['attr']->value['attrvalue1'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
                        <div class="" onclick="attrvalue(<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
);"><?php echo $_smarty_tpl->tpl_vars['item']->value;?>
</div>
                        <input type="hidden" name="attrvalue1_<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
" id="attrvalue1_<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['item']->value;?>
"/>
                        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

                    </div>
                    <?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['attr']->value['attrname2']) {?>
                    <div class="title1" style="width: 100%;"><?php echo $_smarty_tpl->tpl_vars['attr']->value['attrname2'];?>
</div>
                    <div class="lbdiv">
                        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['attr']->value['attrvalue2'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
                        <div class="" onclick="attrvalues(<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
);"><?php echo $_smarty_tpl->tpl_vars['item']->value;?>
</div>
                        <input type="hidden" name="attrvalue2_<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
" id="attrvalue2_<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['item']->value;?>
" />
                        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

                    </div>
                    <?php }?>

                </li>
                <?php }?>
                <li>
                    <div class="title1">购买数量</div>
                    <div class="item1">
                        <div class="goods-num amount amount-btn cart-box">

                            <!--<i class="decrease amount-minus input-number-decrement input-num" onclick="reduce();">–</i>-->
                            <input class="input-number amount-input num" type="text" id="defaultnum" value="1" disabled>
                            <!--<i class=" increase amount-plus input-number-increment input-num" onclick="increase();">+</i>-->
                        </div>
                    </div>
                    <div></div>
                </li>
            </ul>
            <!--限购提示--每人限购数量是1件时，则购物车数量达到1件时在点击加号则提示“您已达到限购数量”-->

            <div style="height: 10px"></div>
        </div>
        <input type="hidden" id="attrid" value="<?php echo $_smarty_tpl->tpl_vars['Act']->value['id'];?>
"/>
        <input type="hidden" id="number" value="1"/>
        <input type="hidden" id="votecount" value="<?php echo $_smarty_tpl->tpl_vars['Act']->value['votecount'];?>
"/>
        <input type="hidden" id="current" value="0"/>
        <input name="c" value="<?php echo $_smarty_tpl->tpl_vars['Act']->value['id'];?>
" type="hidden">
        <input name="currentvoting" value="0" type="hidden">
        <input name="islog"  value="<?php echo $_smarty_tpl->tpl_vars['is_log']->value;?>
"  type="hidden">
        <input name="isjoin" value="<?php echo $_smarty_tpl->tpl_vars['is_join']->value;?>
" type="hidden">

        <div class="f-foot">
            <a href="javascript:void(0)" class="bg-color" onclick="shopping(<?php echo $_smarty_tpl->tpl_vars['Act']->value['id'];?>
,<?php echo $_smarty_tpl->tpl_vars['Act']->value['price'];?>
);">确定</a>
        </div>
    </section>

<!--//////////////////////////////-->

    <div class="bord"></div>

    <!--底部导航 start-->
    <?php $_smarty_tpl->_subTemplateRender("file:../front/public.heartpro.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

    <!--底部导航 end-->
</div>
</body>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/TouchSlide/TouchSlide.1.1.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/jquery.base64.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/jquery.md5.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">


    function userlogn(msg)
    {
        Message.showConfirm(msg, "确定", "取消", function ()
        {
            var dq_url= window.location.search;
            var dq_url=$.base64.btoa(dq_url);//加密
            // console.log(a===$.base64.atob(b));//解密
            //将要 跳转的URL
            var dl_url =  'index.php?a=login&c=account';
            var url =  dl_url+'&backreturnurl='+dq_url;

            window.location.href = '' + url + '';
        });
    }


    //限制字符个数
    $(".shop_name").each(function(){
        var maxwidth=12;
        if($(this).text().length>maxwidth){
            $(this).text($(this).text().substring(0,maxwidth));
            $(this).html($(this).html()+"...");
        }
    });
    $(".location p").each(function(){
        var maxwidth=6;
        if($(this).text().length>maxwidth){
            $(this).text($(this).text().substring(0,maxwidth));
            $(this).html($(this).html()+"...");
        }
    });
    //    页面tab切换
    $(".vote_content ul li a").click(function(){
        $(this).addClass("active");
        $(this).parent("li").siblings("li").children("a").removeClass("active");
    });
    //    搜索跳转
    $("#go_search").click(function(){
        $("body").scrollTop(0);
        $("#main").css("display","none");
        $("#search_page").css("display","block");
    });
    $("#back_main").click(function(){
        $("#search_page").css("display","none");
        $("#search_page .search_text").val("");
        $("#main").css("display","block");
    });

<?php echo '</script'; ?>
>
<!-- Swiper JS -->
<?php echo '<script'; ?>
 src="/style/js_two/swiper.min.js"><?php echo '</script'; ?>
>
<!-- Initialize Swiper -->
<?php echo '<script'; ?>
>
    var swiper = new Swiper('.swiper-container', {
        autoplay: 3000,
        pagination: '.swiper-pagination',
        paginationClickable: true
    });
<?php echo '</script'; ?>
>
<!--倒计时-->
<?php echo '<script'; ?>
 src="/style/js_two/jquery.countdown.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>

    $(function(){
        var note = $('#note');
        // Notice the *1000 at the end - time must be in milliseconds
//        var endtime = new Date("2017/11/1 20:10:10");
        var etime=$('#endtime').val();
        var endtime = new Date(etime);
        var nowtime = new Date();
        var magnitude = endtime - nowtime;
        ts = (new Date()).getTime() +magnitude;
        //console.log(ts);
        $('#countdown').countdown({
            timestamp	: ts,
            callback	: function(days, hours, minutes, seconds){
                //console.log(days);
                var message = "";
                message += "距离活动结束时间";
                note.html(message);
            }
        });
        $('.countDays').append('天');
        $('.countHours').append('时');
        $('.countMinutes').append('分');
        $('.countSeconds').append('秒');
    });

<?php echo '</script'; ?>
>
<!--倒计时-->
<?php echo '<script'; ?>
 src="/style/js_two/jaliswall.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
    $(function()
    {
        $('.wall').jaliswall({ item: '.article' });
    });

    function vodesign(){
        var url='index.php?a=vodesign&c=heartpro';
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {},
        })
            .done(function (o) {
                if (o.isok == 'true') {
                    $('#showLoading').hide();
                    Message.showNotify("" + o.data + "", 1000);
                    $('#goods_' + id).remove();
                } else {
                    Message.showNotify("" + o.data + "", 1000);
                }
            })
            .fail(function () {
            })
            .always(function () {
                $('#showLoading').hide();
            });
    }


    $(function(){
        $('#list').activityajax();
    })
    $.fn.activityajax = function(url){
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
                ajax_list(p,0);
            }
        }
        $(window).scroll(scrollHandler);//执行滚动
        ajax_list(1,0)//默认加载一页
    }
    // p:页码
    // err:   1 搜索; 2人气  ； 3 最新（表示点击过来的）

    function ajax_list(p,err,title,search)
        {
            var title=0,id=$('input[name="id"]').val(),c=$('#list').data('c');

            if(err==1||err==2||err==3){
                $('#list li').remove();
            }
            var search=$('input[type="search"]').val();
            $('#title li a').each(function () {//3最新参与和2人气排序
                if( $(this).hasClass('active')){
                    title =$(this).data('index');
                }
            });

            var url='index.php?a=vodelist_ajax&c='+c;
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {title:title,id:id,p:p,search:search},
            })
                .done(function (o)
                {
                    if (o.isok == 'true')
                    {
                        insertListDiv(o.data,o.current,o.is_log,o.p,o.Nws,o.title,o.search,o.is_join,o.currentrankings,o.current);

                    } else {
                        $('#nomre').html("你查询的数据不存在");
//                Message.showNotify("你查询的数据不存在", 1000);
                    }
                })
                .fail(function () {
                })
                .always(function () {
                    $('#showLoading').hide();
                });
        }
    //生成数据html,append到div中
    function insertListDiv(list,current,islog,p,Nws,title,search,isjoin,currentrankings,current)
    {

        var $mainDiv = $("#list");
        $mainDiv.html();
        var c=$('#list').data('c');
        var id=$("input[name='id']").val();

        var html = '';
        for (var i = 0; i< list.length; i++)
        {
            html += '<li class="article pr bw" style="width: 45%;float: left;margin:1% 1% 1% 1.5%;padding:1%;height: 160px;">';
            html += '<a class="clearfloat" href="javascript:void(0);">';
            html += '<img src="'+list[i].img+'" style="height: 80px;width:80px;margin:10px auto"/>';
            if(list[i].is_buy==1){
                html += '<img src="/'+list[i].icon_buy+'" style="height: 40px;width:40px;position: absolute;left:105px;top:15px;"/>';
            }
            html += '<p class="f12 cb tl" style="text-align: left;">姓名：'+list[i].realname+'</p>';
            html += '<p class="f12 cb tl" style="text-align: left;width: 100%">编号：'+list[i].number+'</p>';
            html += '<span class="f12 cb fl" style="margin-bottom: 2px;line-height: 20px;">共投票：<span id="p_'+list[i].id+'">'+list[i].vote_num+'</span> 票</span>';
            html += '<input class="fr cw by br3" type="button" value="投票" onclick="ajax_vote('+list[i].id+')" />';
            html += '</a>';
            html += '</li>';
        }
        $mainDiv.append(html);

        if(Nws=='向上拉加载更多')
        {
            $("#click_more").click(ajax_list(p,err,title,search));
        }else{
            $("#click_more").unbind(ajax_list);
        }

        $("#click_more").html(Nws);
        $("input[name='currentvoting']").val(currentrankings);
        $("input[name='current']").val(current);
        $("input[name='islog']").val(islog);
        $("input[name='isjoin']").val(isjoin);
        $("#currentrankings").html(currentrankings);
    }

    // /**投票*/
    function ajax_vote(shop_id)
    {
        var id=$('input[name="id"]').val(),c=$('#list').data('c');
        var url='index.php?a=ajaxvote&c='+c;
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {shop_id:shop_id,id:id},
        })
            .done(function (o)
            {
                if (o.isok == 'true') {
                    // Message.showNotify('投票成功', 1000);
                    Message.showNotify(o.msg, 1000);
                    var aa='#p_'+o.shop_id;
                    $(aa).html(o.num);//更新投票次数
                } else {
                    if (o.data == 1) {
                        // if (confirm("请登录后再投票(如果没有帐号请注册！),确认登录吗?")) {
                        //     window.location.href = o.url;
                        // }
                        var msg=o.msg+'确认登录（或注册）吗？';
                        userlogn(msg);
                    } else if (o.data == 2)
                    {
//                        Message.showNotify('今天已经你对该商家投过票了，请选择其它或明天再来吧！', 2000);
                        Message.showNotify(o.msg, 2000);
                    } else if (o.data == 4)
                    {
                        Message.showNotify(o.msg, 1000);
                    } else if (o.data == 5)
                    {
                        Message.showNotify(o.msg, 1000);
                    } else if (o.data == 6)
                    {
                        Message.showNotify(o.msg, 1000);
                    } else if (o.data == 7)
                    {
                        Message.showNotify(o.msg, 1000);
                    }
                }
            })
            .fail(function () {
            })
            .always(function () {
                $('#showLoading').hide();
            });
    }

    //竞买
    function activity_pro()
    {
        var id = $("input[name='c']").val();
        var url = 'index.php?a=vodesign_noma&c=heartpro';
        $.ajax({
            url: url,
            type: 'POST',
            dataTpe: 'json',
            data: {id: id},
        })
        .done(function (o) {
            if (o.isok == 'true') {
                Message.showNotify("" + o.data + "", 1500);
                setTimeout("window.location.href='" + o.url + "'", 1500);
            } else {
                if(o.islog == 0){
                    var msg=o.data+'确认登录（或注册）吗？';
                    userlogn(msg);
                }else{
                    Message.showNotify("" + o.data + "", 1500);
                }
            }
        })

    }
    function shop_url(id){
        window.location.href='/index.php?a=mylist&c=shop&id='+id;
    }

<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript" src="/style/js/public.js"><?php echo '</script'; ?>
>
</html><?php }
}
