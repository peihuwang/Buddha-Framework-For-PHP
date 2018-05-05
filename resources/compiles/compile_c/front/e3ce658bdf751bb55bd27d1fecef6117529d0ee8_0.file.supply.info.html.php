<?php
/* Smarty version 3.1.30, created on 2017-12-21 19:46:15
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/supply.info.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a3b9f07a10895_80076945',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e3ce658bdf751bb55bd27d1fecef6117529d0ee8' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/supply.info.html',
      1 => 1513663791,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.recommend.html' => 1,
    'file:public.shopping.html' => 1,
    'file:public.footernew.html' => 1,
  ),
),false)) {
function content_5a3b9f07a10895_80076945 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/bendishangjia.com/vendor/smarty/plugins/modifier.date_format.php';
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<style>
    .recommend_header{width:100%;display:inline-block;height: 70px;margin-top: 20px;border-top: 35px solid  #F0F0F0;padding-top: 15px;}
    .header_me{width: 80px;float: left;font-size: 18px;margin-left: 10px;}
    .header_more{width: 80px;float: right;color: #787878;}
    .recommend{
        background-color: #F0F0F0;
        /*border: 1px solid red;*/
        /*height: 325px;width: 100%;*/
    }
    .recommend_menu {font-size: 12px;font-weight: bolder; width: 100%;text-align: center;margin: 2px 2px 4px 2px;padding-top: 8px;
    }
    .recommend_menu li{list-style-image: none;list-style-type: none;border-right-width: 1px;
        /*border-right-style: solid;*/ /*border-right-color: #000000;*/float: left; margin-bottom: 8px;margin-left: 5px;
        text-align: center;height: 150px; width: 31.3%;background-color:  #FFFFFF;}
    .recommend_menu li a{color: #FFFFFF;text-decoration: none; margin: 0px; padding-top: 8px;  display: block; /* 作为一个块 */
        padding-right: 50px; /* 设置块的属性 */    padding-bottom: 8px; width: 100%; zoom: 1;}
    .recommend_menu li a img{width: 80px;height: 80px;float: left;margin-left: 15px;}
    .recommend_menu li a span{/*width: 20px;float: left;margin-left: 2px;*/overflow: hidden;text-overflow: ellipsis;white-space: nowrap;width: 100px;font-size: 1.2em;line-height: 1.5; display: block;}
    .recommend_menu li a:hover{background-color: #0099CC;}
</style>
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
        function select_spec(event) {
    $("#choose_attr .f-foot a").removeClass('add-cart');
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

}

function close_choose_spec() {
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
<style>
    /*j角色切换*/ 
    .black_overlay{display: none;position: absolute; top: 0%; left: 0%; width: 100%;height: 100%;background-color: black;z-index:1001;-moz-opacity: 0.8; opacity:.80; filter: alpha(opacity=88);}
    .white_content {display: none;position: absolute;top: 15%; left: 5%; width: 90%; height: 65%;padding: 10px; background-color: white;z-index:1002; overflow: auto;}
    #member{width: 30%;margin: auto;}
    .codeimgs img{width:100%; margin:auto;}
</style>
<link href="/style/css_two/reset.css" rel="stylesheet" type="text/css" />
<link href="/style/css_two/style.css" rel="stylesheet" type="text/css" />
<link href="/style/css_two/buy.css" rel="stylesheet" type="text/css" />

<link href="style/css_two/date.css" rel="stylesheet" type="text/css" />
<link href="style/css_two/switch.css" rel="stylesheet" type="text/css" />
<link href="style/css_two/swiper.min.css" rel="stylesheet" type="text/css" />
<div id="gallery">
    <div id="goods_gallery" class="goods_gallery ">
        <div class="hd"><ul></ul></div>
        <div class="bd">
            <ul>
                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['gsllery']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
                <li><img src="<?php echo $_smarty_tpl->tpl_vars['item']->value['goods_img'];?>
"></li>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

            </ul>
        </div>
    </div>
</div>
<div class="goods_info">
    <div class="title"><?php echo $_smarty_tpl->tpl_vars['goods']->value['goods_name'];?>
</div><?php if ($_smarty_tpl->tpl_vars['uid']->value == $_smarty_tpl->tpl_vars['goods']->value['user_id']) {?><div id="top_up" onclick="Top('<?php echo $_smarty_tpl->tpl_vars['goods']->value['id'];?>
','info.top','supply','0.2')">置  顶</div><?php }?>
    <div class="price"><?php if ($_smarty_tpl->tpl_vars['goods']->value['is_promote'] == 1) {?>促  销  价：<em>￥<b><?php echo $_smarty_tpl->tpl_vars['goods']->value['promote_price'];?>
</b></em>元
        <span style="text-decoration: line-through">原价：<?php echo $_smarty_tpl->tpl_vars['goods']->value['market_price'];?>
</span><?php } else { ?>售价：<em>￥<b><?php echo $_smarty_tpl->tpl_vars['goods']->value['market_price'];?>
</b></em><?php }?>
        
    </div>
    <div class="tiem">
        <span class="update_time">更新时间：<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['goods']->value['last_update'],'%Y-%m-%d %H:%M');?>
</span>
        <span class="number">浏览：<?php echo $_smarty_tpl->tpl_vars['goods']->value['click_count'];?>
人</span>
    </div>
</div>
<div class="shop_info_supply">
    <div class="shop_title" data-href="<?php echo $_smarty_tpl->tpl_vars['shop_url']->value;
echo $_smarty_tpl->tpl_vars['goods']->value['shop_id'];?>
"><div class="shop_img"><img src="<?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['small']) {
echo $_smarty_tpl->tpl_vars['shopinfo']->value['small'];
} else { ?>/style/images/buslogo.png<?php }?>" alt=""></div><div class="shop_name" style="color: red;font-size: 14px;margin-left: 15%;"> [进入店铺] 
        <?php if ($_smarty_tpl->tpl_vars['info']->value) {?>
        <div style="float: right;color: #666;font-size: 12px;width: 80px; height: 50px;display:inline;text-align: center;}">
            <img src="style/images/zhuanfayoushang.png" style="width: 40px;height: 40px;"/>
        </div>
        <?php }?>
    </div>
</div>
    <div class="steat"><div class="renzheng"><?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify'] == 1) {?>认证店铺 <em><b>V</b>1</em><?php } else { ?>店铺未认证 <?php }?> <?php if ($_smarty_tpl->tpl_vars['isok']->value == 1) {?>
        <span class="codeimg">
            <img onclick="document.getElementById('light').style.display='block';document.getElementById('fade').style.display='block'" src="<?php echo $_smarty_tpl->tpl_vars['nlog']->value;?>
">
        </span>
        <?php }?>
    </div>
        <?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify'] == 1) {?>
        <!--<div class="tel">电话：<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
</div>-->
        <div class="tel"><a href="tel:<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
"><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
</a></div>
        <?php } elseif ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify'] == 0 && $_smarty_tpl->tpl_vars['shopinfo']->value['verify'] == 1) {?>
        <div class="tel"><a href="tel:<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
;"><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
</a></div>
        <?php } else { ?>
        <?php if ($_smarty_tpl->tpl_vars['see']->value == 1) {?>
        <div class="tel"><a href="tel:<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
;"><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
</a></div>
        <?php } else { ?>
        <div class="tel" onclick="see(<?php echo $_smarty_tpl->tpl_vars['goods']->value['id'];?>
)"><i></i>电话</div>
        <?php }?>
        <?php }?>
    </div>
    <div class="address">
        <div><span data-href="http://apis.map.qq.com/tools/routeplan/eword=<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['specticloc'];?>
&epointx=<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['lng'];?>
&epointy=<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['lat'];?>
&policy=1?referer=myapp&key=HM5BZ-ICHKU-VDNVI-27RMJ-YZRCQ-P5BTP"><i></i><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['distance'];?>
</span> <?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['level2'];
echo $_smarty_tpl->tpl_vars['shopinfo']->value['level3'];
echo $_smarty_tpl->tpl_vars['shopinfo']->value['specticloc'];?>
</div>
        <?php if ($_smarty_tpl->tpl_vars['goods']->value['is_promote'] == 1) {?>
        <p>促销时间：<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['goods']->value['promote_start_date'],"%Y-%m-%d %H:".((string)$_smarty_tpl->tpl_vars['M']->value).":%S");?>
 - <?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['goods']->value['promote_end_date'],"%Y-%m-%d %H:".((string)$_smarty_tpl->tpl_vars['M']->value).":%S");?>
</p>
        <?php }?>
    </div>
</div>
<?php if ($_smarty_tpl->tpl_vars['goods']->value['goods_brief']) {?>
<div class="detailed">
    <h2>商品简述</h2>
    <div id="content">
        <?php echo $_smarty_tpl->tpl_vars['goods']->value['goods_brief'];?>

    </div>
</div>
<?php }?>
<div class="detailed">
    <h2>详细描述</h2>
    <div id="content">
        <?php echo $_smarty_tpl->tpl_vars['goods']->value['goods_desc'];?>

    </div>
</div>
<div class="goodsad"></div>
<br/>
<br/>
<br/>
<br/>
<div id="light" class="white_content">
    <a href = "javascript:void(0)" onclick = "document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'">点击关闭</a>
    <div id="membe">
        <div class="codeimgs"><img src="<?php echo $_smarty_tpl->tpl_vars['nlog']->value;?>
"></div>
    </div>
</div>

<section class="mask-div" style="display: none;"></section>
<section class="f-block" id="choose_attr" style="height: 0; overflow: hidden;">
    <div class="f-title-attr">
        <img class="SZY-GOODS-IMAGE-THUMB" src="<?php echo $_smarty_tpl->tpl_vars['gsllery']->value[0]['goods_img'];?>
" style="float: left;">
        <div class="f-title-arr-right">
            <span class="goodprice SZY-GOODS-PRICE price-color" id="moneys" style="color:red;"><?php if ($_smarty_tpl->tpl_vars['goods']->value['is_promote'] == 1) {?><em>￥<b><?php echo $_smarty_tpl->tpl_vars['goods']->value['promote_price'];?>
</b></em>
        <span style="text-decoration: line-through;">￥<?php echo $_smarty_tpl->tpl_vars['goods']->value['market_price'];?>
</span><?php } else { ?><em>￥<b><?php echo $_smarty_tpl->tpl_vars['goods']->value['market_price'];?>
</b></em><?php }?></span>
            <span class="SZY-GOODS-NUMBER" id="stock">
                库存：<em id="stocknum"><?php if ($_smarty_tpl->tpl_vars['stockNum']->value != 0) {
echo $_smarty_tpl->tpl_vars['stockNum']->value;
} else { ?>999+<?php }?></em> 件
            </span>
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
                    
                        <i class="decrease amount-minus input-number-decrement input-num" onclick="reduce();">–</i>
                           <input class="input-number amount-input num" type="text" id="defaultnum" value="1">
                        <i class=" increase amount-plus input-number-increment input-num" onclick="increase();">+</i>
                    </div>
                </div>
                <div>
                </div>
            </li>
        </ul>
        <!--限购提示  每人限购数量是1件时，则购物车数量达到1件时在点击加号则提示“您已达到限购数量”-->
        
        <div style="height: 10px"></div>
    </div>
    <input type="hidden" id="attrid" value="<?php echo $_smarty_tpl->tpl_vars['attr']->value['id'];?>
"/>
    <input type="hidden" id="number" value="1"/>
    <div class="f-foot">
        <a href="javascript:void(0)" class="bg-color" onclick="shopping(<?php echo $_smarty_tpl->tpl_vars['goods']->value['id'];?>
,<?php echo $_smarty_tpl->tpl_vars['goods']->value['promote_price'];?>
?<?php echo $_smarty_tpl->tpl_vars['goods']->value['promote_price'];?>
:<?php echo $_smarty_tpl->tpl_vars['goods']->value['market_price'];?>
);">确定</a>
    </div>
</section>
<div id="fade" class="black_overlay"></div>


<?php $_smarty_tpl->_subTemplateRender("file:public.recommend.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>



<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>


<input type="hidden" id="s_id" value="<?php echo $_smarty_tpl->tpl_vars['goods']->value['shop_id'];?>
">
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/TouchSlide/TouchSlide.1.1.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
$(document).ready(function(){
    $('.lbdiv div').click(function() {
        var index=$(this).index();
        $(this).addClass('curr').siblings().removeClass();
    
    });
});
(function(){
    $(".input-num").unbind("dbclick");
    window.inputNumber = function(el) {
        var min = el.attr('min') || false;
        var max = el.attr('max') || false;
        var els = {};
        els.dec = el.prev();
        els.inc = el.next();
        el.each(function() {
            init($(this));
        });
        function init(el) {
            els.dec.on('click', decrement);
            els.inc.on('click', increment);
            function decrement() {
                var value = el[0].value;
                value--;
                if(!min || value >= min) {
                    el[0].value = value;
                }
            }
            function increment() {
                var value = el[0].value;
                value++;
                if(!max || value <= max) {
                    el[0].value = value++;
                }
            }
        }
    }
})();

inputNumber($('.input-number'));
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
    TouchSlide({
        slideCell :"#goods_gallery",
        titCell : ".hd ul", // 开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
        mainCell : ".bd ul",
        effect : "leftLoop",
        autoPlay : true, // 自动播放
        autoPage : true, // 自动分页
        delayTime: 200, // 毫秒；切换效果持续时间（执行一次效果用多少毫秒）
        interTime: 2500, // 毫秒；自动运行间隔（隔多少毫秒后执行下一个效果

    });
    function see(id){
        Message.showConfirm("店铺未认证,支付0.2元查看联系方式", "确定", "取消", function () {
            $.post('/pay/?a=index&c=index',{id:id,good_table:'supply',payment_code:'wxpay',order_type:'info.see2',final_amt:0.2},function(re){
                if(re.errcode==0){
                    window.location.href=''+re.url+'';
                }else{
                    Message.showNotify(""+re.errmsg+"", 1200);
                }
            })
        });
    }
<?php echo '</script'; ?>
>

<?php echo '<script'; ?>
>
    var attrvalue1 = '';
    var attrvalue2 = '';
    var attrid = $('#attrid').val();
    function attrvalue(id){//动态改变商品的价格
        attrvalue1 = $('#attrvalue1_'+id).val();
        if(attrvalue1 && attrvalue2){
            $.ajax({
                type:'post',
                url:'index.php?a=attrvalue&c=supply',
                data:{attrid:attrid,attrvalue1:attrvalue1,attrvalue2:attrvalue2},
                dataType:'JSON',
                success:function(o){
                    if(o.isok == 1){
                        var data = o.info;
                        $('#addspan').remove();
                        $('#moneys').html('<span style="color:red;"><em>￥<b>' + data.money + '</b></em></span>');
                        $('#stocknum').html(data.stock);
                        $('#stock').after('<span id="addspan" style="font-size:8px;">已选：' + attrvalue1 + attrvalue2 + '</span>');
                        if(data.stock < 1){
                            $('.f-foot').html(' <a href="javascript:void(0)" class="bg-color">确定</a>');
                            Message.showMessage('库存不足');
                        }else{
                            $('.f-foot').html('<a href="javascript:void(0)" class="bg-color" onclick="shopping(<?php echo $_smarty_tpl->tpl_vars['goods']->value['id'];?>
,<?php echo $_smarty_tpl->tpl_vars['goods']->value['promote_price'];?>
?<?php echo $_smarty_tpl->tpl_vars['goods']->value['promote_price'];?>
:<?php echo $_smarty_tpl->tpl_vars['goods']->value['market_price'];?>
);">确定</a>');
                        }
                    }   
                },
            });
        }
    }
    function attrvalues(id){//动态改变商品的价格
        attrvalue2 = $('#attrvalue2_'+id).val();
        if(attrvalue1 && attrvalue2){
            $.ajax({
                type:'post',
                url:'index.php?a=attrvalue&c=supply',
                data:{attrid:attrid,attrvalue1:attrvalue1,attrvalue2:attrvalue2},
                dataType:'JSON',
                success:function(o){
                    if(o.isok == 1){
                        var data = o.info;
                        $('#addspan').remove();
                        $('#moneys').html('<span style="color:red;"><em>￥<b>' + data.money + '</b></em></span>');
                        $('#stocknum').html(data.stock);
                        $('#stock').after('<span id="addspan" style="font-size:8px;">已选：' + attrvalue1 + attrvalue2 + '</span>');
                        if(data.stock < 1){
                            $('.f-foot').html(' <a href="javascript:void(0)" class="bg-color">确定</a>');
                            Message.showMessage('库存不足');
                        }else{
                            $('.f-foot').html('<a href="javascript:void(0)" class="bg-color" onclick="shopping(<?php echo $_smarty_tpl->tpl_vars['goods']->value['id'];?>
,<?php echo $_smarty_tpl->tpl_vars['goods']->value['promote_price'];?>
?<?php echo $_smarty_tpl->tpl_vars['goods']->value['promote_price'];?>
:<?php echo $_smarty_tpl->tpl_vars['goods']->value['market_price'];?>
);">确定</a>');
                        }
                    } 
                },
            });
        }
    }

    function increase(){
        var num = $('#defaultnum').val();
        var stocknum = $('#stocknum').text();
        num++;
        if(num<1){
            return false;
        }
        if(num > stocknum){
            Message.showMessage("购买数量不能大于库存数量");
            num--;
            $('#defaultnum').val(num);
            return false;
        }
        $('#number').val(num);
    }
    function reduce(){
        var num = $('#defaultnum').val();
        num--;
        if(num<1){
            Message.showMessage("购买数量必须大于等于1");
            num=1;
            $('#defaultnum').val(num);
            return false;
        }
        $('#number').val(num);
        
    }

    //上海人才创业园A1栋702室
    function Top(id, order_type, good_table, final_amt) {
        Message.showConfirm("信息置顶,0.2元每次", "确定", "取消", function () {
            $.post('/pay/index.php?a=index&c=index',
                    {id: id, order_type: order_type, good_table: good_table, final_amt: final_amt,pc:1},
                    function (re) {
                        if (re.errcode == 0) {
                            window.location.href = '' + re.url + '';
                        } else {
                            Message.showNotify("" + re.errmsg + "", 1200);
                        };
                    });
        });
    };
<?php echo '</script'; ?>
>
<!-- <?php echo '<script'; ?>
 charset="utf-8" src="http://map.qq.com/api/js?v=2.exp"><?php echo '</script'; ?>
>

<?php echo '<script'; ?>
>
        var geocoder, map, marker = null;
   
        geocoder = new qq.maps.Geocoder();

        function codeAddress() {
            var address = '中国,浙江,嘉善县,上海人才创业园A1栋';
            //对指定地址进行解析
            geocoder.getLocation(address);
            //设置服务请求成功的回调函数
            geocoder.setComplete(function(result) {
            console.log(result);
            });
            //若服务请求失败，则运行以下函数
            geocoder.setError(function() {
                alert("出错了，请输入正确的地址！！！");
            });
        }
        codeAddress();
<?php echo '</script'; ?>
> -->
<?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify']) {
$_smarty_tpl->_subTemplateRender("file:public.shopping.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php } else {
$_smarty_tpl->_subTemplateRender("file:public.footernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php }
}
}
