<?php
/* Smarty version 3.1.30, created on 2017-12-17 16:18:04
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/local.infonew.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a36283c9fc6b1_18008111',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'b26016b1eb2d3d250be4e4e833ee894175de6a7e' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/local.infonew.html',
      1 => 1511450353,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.headernew.html' => 1,
    'file:public.footernew.html' => 1,
  ),
),false)) {
function content_5a36283c9fc6b1_18008111 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.headernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<style type="text/css">
    .swiper-wrapper01{height:<?php echo $_smarty_tpl->tpl_vars['height']->value;?>
px;}
    .swiper-slide01{height:auto;}
</style>
<div id="main">
    <!--头部 start-->
    <div class="top w by pf">
        <a class="back pa" href="index.php?a=index&c=index">
            <img src="/style/img_two/back.png"/>
        </a>
        <p class="f18 cw w tc"><span id="title"><?php echo $_smarty_tpl->tpl_vars['shopcat']->value[0]['cat_name'];?>
</span>(已入驻<span id="count" ></span>家)</p>
    </div>
    <!--头部 end-->

    <!--分类 start-->
    <div class="wrapper" id="retr" style="top:44px;">
        <div class="scroller w bw brb">
            <ul class="tab clearfloat" id="cat_id">
                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['shopcat']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
                    <li class="f16 cb fl tc <?php if ($_smarty_tpl->tpl_vars['key']->value == 0) {?>cur<?php } else {
}?> " data-id="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" data-index="<?php echo $_smarty_tpl->tpl_vars['key']->value+1;?>
">
                        <p onclick="Headerclick(<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
,<?php echo $_smarty_tpl->tpl_vars['key']->value+1;?>
)"><?php echo $_smarty_tpl->tpl_vars['item']->value['cat_name'];?>
</p>
                    </li>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

            </ul>
        </div>
        <a style="position:fixed; right:0; top:44px;" href="javascript:void(0);" onclick="showClassify();"><img style="height:48px;" src="/style/img_two/classify_new.png"></a>
    </div>
    <!--分类 end-->
    <!--轮播图 start-->
    <div class="localinfo_banner pr w" style="overflow:hidden; padding-top:92px;">
        <div class="swiper-container04 w">
            <div class="swiper-wrapper" id="swiper-slide-content"></div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
        </div>
    </div>
    <!--轮播图 end-->


    <!--列表 start-->
    <div class="swiper-container03" >
        <div class="swiper-wrapper swiper-wrapper03">
            <div class="swiper-slide swiper-slide03 main_list w">
                <div class="swiper-container01" >
                    <div class="swiper-wrapper swiper-wrapper01">
                        <div class="swiper-slide swiper-slide01 w"  id="content_list"></div>
                    </div>
                    <div class="swiper-scrollbar01"></div>
                </div>
            </div>
        </div>
        <div class="loadtip w tc cg f16" id="more"></div>
        <div class="swiper-scrollbar03"></div>
    </div>
    <!--列表 end-->
    <br>
    <br>
    <br>

    <div id="classify" class="w pf bw" style="top:0; left:0; z-index:10000; display:none;">
        <!--头部 start-->
        <div class="top w by pf">
            <a class="back pa" href="javascript:void(0);" onclick="hideClassify();">
                <img src="/style/img_two/back.png"/>
            </a>
            <p class="f18 cw w tc">本地信息类目</p>
        </div>
        <!--头部 end-->

        <!--类目 start-->
        <div class="classify_list clearfloat">
            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['shopcat']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
            <a class="fl w25 tc" href="javascript:void(0);" data-id="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" data-index="<?php echo $_smarty_tpl->tpl_vars['key']->value+1;?>
" onclick="Headerclick_1(<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
,<?php echo $_smarty_tpl->tpl_vars['key']->value+1;?>
)">
                <p class="f14 cb br3 b"><?php echo $_smarty_tpl->tpl_vars['item']->value['cat_name'];?>
</p></a>
            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

        </div>
        <!--类目 end-->
    </div>
    <!--底部导航 start-->
<?php $_smarty_tpl->_subTemplateRender("file:public.footernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

    <!--底部导航 end-->
</div>

<?php echo '<script'; ?>
 src="/style/js/localnew.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
    $(function(){
        $('#content_list').ajaxlist(1);
    })
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
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
    $(".tab_list ul li span").click(function(){
        $(this).addClass("active");
        $(this).parent("li").siblings("li").children("span").removeClass("active");
    });
    //    显示分类菜单
    function showClassify(){
        $("#classify").show();
    }
    //    隐藏分类菜单
    function hideClassify(){
        $("#classify").hide();
    }
    //    在分类菜单里面选中相应分类
    $("#classify .classify_list a").click(function(){
        hideClassify();
    });
<?php echo '</script'; ?>
>
<!-- Swiper JS -->
<?php echo '<script'; ?>
 src="/style/js_two/swiper.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
    //    轮播图
    var swiper = new Swiper('.swiper-container04', {
        autoplay: 3000,
        pagination: '.swiper-pagination',
        paginationClickable: true,
        loop:true
    });
    //    懒加载
    var loadFlag = true;
    var p = 2;
    var mySwiper01 = new Swiper('.swiper-container01',{
        direction: 'vertical',//方向上下
        scrollbar: '.swiper-scrollbar01',
        slidesPerView: 'auto',
        freeMode: true,
        onTouchEnd: function(swiper) {
            var _viewHeight = document.getElementsByClassName('swiper-wrapper01')[0].offsetHeight;
            var _contentHeight = document.getElementsByClassName('swiper-slide01')[0].offsetHeight;
            // 上拉加载
            if(mySwiper01.translate <= _viewHeight - _contentHeight && mySwiper01.translate < 0) {
                if(loadFlag){
                    $(".loadtip").html('正在加载...');
                }else{
                    $(".loadtip").html('<div class="nomore"></div>');
                }
                setTimeout(function() {
//                    alert(p);
                    $('#content_list').ajaxlist(p);

//                    $("#content_list").append('<div class="list_item pr brb clearfloat">我是加载出来的...</div><div class="list_item pr brb clearfloat">我是加载出来的...</div><div class="list_item pr brb clearfloat">我是加载出来的...</div><div class="list_item pr brb clearfloat">我是加载出来的...</div>');

//                    $(".loadtip").html('上拉加载更多...');
//                    mySwiper01.update(); // 重新计算高度;
                    p++;
                }, 800);
            }
            return false;
        }
    });
    //    左右滑动加载
    var mySwiper03 = new Swiper('.swiper-container03',{
        direction: 'horizontal',//方向左右
        scrollbar: '.swiper-scrollbar03',
        slidesPerView: 'auto',
        freeMode: true,
        onTouchEnd: function(swiper) {
//            console.log(mySwiper03.translate);
            if(mySwiper03.translate>40){
                var div_checked=$('#cat_id li');
                var data_arr=div_checked_next=cat_id=cat_index=data_arr=div_checked_first='';
                div_checked.each(function(){
                    if($(this).hasClass("cur")){
                        data_id=$(this).data('id');
                        //先判断当前是不是第一个
                        div_checked_first= div_checked.first('li');  //获取第一个
                        if(data_id==div_checked_first.data('id')){
                            alert('已经到顶部了，请往左划吧！');
                            return false;
                        }else{
                            div_checked_pre=$(this).prev('li'); // 获取当前的上一个
                            $(this).removeClass('cur');// 去除当前的cur 类
                            div_checked_pre.addClass('cur');// 获取当前的下一个添加cur 类
                            $('#title').html('');
                            $('#title').html(div_checked_pre.text());
                            data_arr= div_checked_pre.data();
                            cat_id=data_arr.id;
                            cat_index=data_arr.index;
                            Headerclick(cat_id,cat_index);
                            return false;
                        }
                    }
                })

//                alert("向右");
            } else if(mySwiper03.translate<-40){
                var div_checked=$('#cat_id li');
                var data_arr=div_checked_next=cat_id=cat_index=data_arr='';

                div_checked.each(function() {
                    if ($(this).hasClass("cur")) {
                        data_id = $(this).data('id');
                        //先判断当前是不是第一个
                        div_checked_first= div_checked.last('li');  //获取第一个
                        if(data_id==div_checked_first.data('id')){
                            alert('已经到底部了，请往右划吧！');
                            return false;
                        }else{
                            div_checked_next = $(this).next('li').first(); // 获取当前的下一个
                            $(this).removeClass('cur');// 去除当前的cur 类
                            div_checked_next.addClass('cur');// 获取当前的下一个添加cur 类
                            $('#title').html('');
                            $('#title').html(div_checked_next.text());
                            data_arr = div_checked_next.data();
                            cat_id = data_arr.id;
                            cat_index = data_arr.index;
                            Headerclick(cat_id, cat_index);
                            return false;
                        }
                    }
                });
//                alert("向左");
            }
        }
    });

    //    tab滚动
    var mySswiper02 = new Swiper('.swiper-container02', {
        autoplay: 1000,
        slidesPerView : 3,
        slidesPerGroup : 3,
        loop:true
    });
<?php echo '</script'; ?>
>
<!--左右滚动导航栏-->
<?php echo '<script'; ?>
 type="text/javascript" src="/style/js_two/iscroll.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript" src="/style/js_two/navbarscroll.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
    $(function(){
        //demo示例一到四 通过lass调取，一句可以搞定，用于页面中可能有多个导航的情况
        $('.wrapper').navbarscroll();
    });

    $(function() {
        var $this = $(".scroller");
        var scrollTimer;
        $this.hover(function() {
            clearInterval(scrollTimer);
        }, function() {
            scrollTimer = setInterval(function() {
                scrollTab($this);
            }, 2000);
        }).trigger("touchleave");

        function scrollTab(obj) {
            var $self = obj.find("ul");
            var lineWidth = $self.find("li:first").width();
            $self.animate({
                "marginLeft": -lineWidth + "px"
            }, 600, function() {
                $self.css({
                    marginLeft: 0
                }).find("li:first").appendTo($self);
            })
        }
    })
<?php echo '</script'; ?>
>
</html><?php }
}
