<?php
/* Smarty version 3.1.30, created on 2017-08-16 13:15:22
  from "/home/bendishangjia.com/www/resources/views/templates/front/supply.info.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5993d4eaeb3229_17321227',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '49f30b9eb9f133146d896e9f180e73ddc020bdfb' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/supply.info.html',
      1 => 1502860520,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.shopping.html' => 1,
    'file:public.footernew.html' => 1,
  ),
),false)) {
function content_5993d4eaeb3229_17321227 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/home/bendishangjia.com/www/vendor/smarty/plugins/modifier.date_format.php';
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<link href="/style/css_two/reset.css" rel="stylesheet" type="text/css" />
<link href="/style/css_two/style.css" rel="stylesheet" type="text/css" />
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
</b></em><?php }?> </div>
    <div class="tiem"><span class="update_time">更新时间：<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['goods']->value['last_update'],'%Y-%m-%d %H:%M');?>
</span><span class="number">浏览：<?php echo $_smarty_tpl->tpl_vars['goods']->value['click_count'];?>
人</span></div>
</div>
<div class="shop_info_supply">
    <div class="shop_title" data-href="<?php echo $_smarty_tpl->tpl_vars['shop_url']->value;
echo $_smarty_tpl->tpl_vars['goods']->value['shop_id'];?>
"><div class="shop_img"><img src="<?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['small']) {
echo $_smarty_tpl->tpl_vars['shopinfo']->value['small'];
} else { ?>/style/images/buslogo.png<?php }?>" alt=""></div><div class="shop_name"><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['name'];?>
</div></div>
    <div class="steat"><div class="renzheng"><?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify'] == 1) {?>认证店铺 <em><b>V</b>1</em><?php } else { ?>店铺未认证 <?php }?> </div>
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
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/TouchSlide/TouchSlide.1.1.js"><?php echo '</script'; ?>
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
<?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify']) {
$_smarty_tpl->_subTemplateRender("file:public.shopping.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php } else {
$_smarty_tpl->_subTemplateRender("file:public.footernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php }
}
}
