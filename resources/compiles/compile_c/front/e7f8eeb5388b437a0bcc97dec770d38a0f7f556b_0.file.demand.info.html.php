<?php
/* Smarty version 3.1.30, created on 2017-11-22 22:47:39
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/demand.info.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a158e0bc6a101_25341725',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e7f8eeb5388b437a0bcc97dec770d38a0f7f556b' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/demand.info.html',
      1 => 1511200419,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_5a158e0bc6a101_25341725 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/Users/mac/workspace/web/bendishangjia.com/vendor/smarty/plugins/modifier.date_format.php';
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="gallery">
<?php if ($_smarty_tpl->tpl_vars['goods']->value['demand_img']) {?>
    <img src="<?php echo $_smarty_tpl->tpl_vars['goods']->value['demand_img'];?>
"  style="width: 100%; max-height:100%;">
<?php } else { ?>
    <img src="style/images/big_demand_img.jpg"  style="width: 100%; max-height:100%;">
<?php }?>
</div>
<div class="goods_info">
    <div class="title"><?php echo $_smarty_tpl->tpl_vars['goods']->value['name'];?>
</div><?php if ($_smarty_tpl->tpl_vars['uid']->value == $_smarty_tpl->tpl_vars['goods']->value['user_id']) {?><div id="top_up" onclick="Top('<?php echo $_smarty_tpl->tpl_vars['goods']->value['id'];?>
','info.top','demand','0.2')">置  顶</div><?php }?>
    <div class="price">预算：<em>￥<b><?php echo $_smarty_tpl->tpl_vars['goods']->value['budget'];?>
</b>元</em></div>
    <div class="tiem"><span class="update_time">有效时间：<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['goods']->value['demand_start_time'],"%Y-%m-%d ");?>
—<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['goods']->value['demand_end_time'],"%Y-%m-%d");?>
</span><span class="number">浏览：<?php echo $_smarty_tpl->tpl_vars['goods']->value['click_count'];?>
人</span></div>
</div>
<div class="shop_info_supply">
    <div class="shop_title" data-href="<?php echo $_smarty_tpl->tpl_vars['shop_url']->value;
echo $_smarty_tpl->tpl_vars['goods']->value['shop_id'];?>
"><div class="shop_img"><img src="<?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['small']) {
echo $_smarty_tpl->tpl_vars['shopinfo']->value['small'];
} else { ?>/style/images/buslogo.png<?php }?>" alt=""></div><div class="shop_name" style="color: red;font-size: 14px;margin-left: 15%;"> [进入店铺]
        <?php if ($_smarty_tpl->tpl_vars['info']->value) {?>
        <div style="float: right;color: #666;font-size: 12px;width: 80px; height: 50px;display:inline;text-align: center;}">
            <img src="style/images/zhuanfayoushang.png" style="width: 40px;height: 40px;" />
        </div>
        <?php }?>
    </div>
</div>
    <div class="steat"><div class="renzheng"><?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify'] == 1) {?>认证店铺 <em><b>V</b>1</em><?php } else { ?>店铺未认证<?php }?> </div>
        <?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify'] == 1) {?>
        <div class="tel">电话：<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
</div>
        <?php } elseif ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify'] == 0 && $_smarty_tpl->tpl_vars['shopinfo']->value['verify'] == 1) {?>
        <div class="tel">电话：<a href="tel:<?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
"><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
</a></div>
        <?php } else { ?>
            <?php if ($_smarty_tpl->tpl_vars['see']->value == 1) {?>
            <div class="tel">电话：<a href="tel:<?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
"><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
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
    </div>
</div>

<div class="detailed">
    <h2>详细描述</h2>
    <div id="content">
        <?php echo $_smarty_tpl->tpl_vars['goods']->value['demand_desc'];?>

    </div>
</div>
<div class="goodsad"></div>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
    function see(id){
        Message.showConfirm("店铺未认证,支付0.2元查看联系方式", "确定", "取消", function () {
            $.post('/pay/?a=index&c=index',{id:id,good_table:'demand',payment_code:'wxpay',order_type:'info.see',final_amt:0.2},function(re){
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
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
