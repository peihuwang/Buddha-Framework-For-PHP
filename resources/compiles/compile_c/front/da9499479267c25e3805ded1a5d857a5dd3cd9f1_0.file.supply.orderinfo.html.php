<?php
/* Smarty version 3.1.30, created on 2017-07-28 11:45:50
  from "/home/bendishangjia.com/www/resources/views/templates/front/supply.orderinfo.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_597ab36e8def50_28134473',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'da9499479267c25e3805ded1a5d857a5dd3cd9f1' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/supply.orderinfo.html',
      1 => 1501213455,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:../front/public.headernew.html' => 1,
  ),
),false)) {
function content_597ab36e8def50_28134473 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:../front/public.headernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<link href="/style/css/web.style.css" rel="stylesheet" type="text/css">
<body class="bg">
<!--头部 start-->
<div class="address_list" style="margin-top:45px;">
  <div class="top w by pf">
      <a class="back pa"  onclick="javascript:history.go(-1);">
          <img src="/style/img_two/back.png"/>
      </a>
      <p class="f18 cw w tc">确认订单</p>
  </div>
  <div>
  <?php if ($_smarty_tpl->tpl_vars['addressinfo']->value) {?>
    <div class=" bw">
    <a href="user/index.php?a=index&c=address">
      <div class="pl10 pr10 brb pt10">
            <p class="clearfloat f15 cb">
                <span class="fl">收货人：<?php echo $_smarty_tpl->tpl_vars['addressinfo']->value['name'];?>
</span>
                <span class="fr">电话：<?php echo $_smarty_tpl->tpl_vars['addressinfo']->value['mobile'];?>
</span>
            </p>
            <p class="f15 cb mt10 mb10">详细地址：<?php echo $_smarty_tpl->tpl_vars['addressinfo']->value['addre'];?>
 <?php echo $_smarty_tpl->tpl_vars['addressinfo']->value['address'];?>
</p>
        </div>
    </div>
    </a>
  </div>
  <?php } else { ?>
    <div style="width: 80%;background-color: #f60;color: #fff;margin: 0 auto;border-radius: 3px;height: 40px;">
      <a style="color: #fff;font-size: 16px;text-align: center;line-height: 40px;" href="user/index.php?a=add&c=address">✚ 添加收货地址</a>
    </div>
  <?php }?>
  <div class="bord bg"></div>
  <div style="background-color: #fff;height: 40px;line-height: 40px;">订单号：<?php echo $_smarty_tpl->tpl_vars['orderinfo']->value['order_sn'];?>
</div>
  <div style="background-color: #fff;">
    <div class="sup_item" data-href="index.php?a=info&c=supply&id=<?php echo $_smarty_tpl->tpl_vars['goodsinfo']->value['id'];?>
">
        <div class="pic_img"><img src="<?php echo $_smarty_tpl->tpl_vars['goodsinfo']->value['goods_thumb'];?>
"></div>
        <div class="nearby_con">
            <div class="title"><?php echo $_smarty_tpl->tpl_vars['goodsinfo']->value['goods_name'];?>
</div>
            <div class="price">价格：<em>￥<i><?php if ($_smarty_tpl->tpl_vars['goodsinfo']->value['promote_price'] != '0.00') {
echo $_smarty_tpl->tpl_vars['goodsinfo']->value['promote_price'];
} else {
echo $_smarty_tpl->tpl_vars['goodsinfo']->value['market_price'];
}?></i></em></div>
        </div>
    </div>
  </div>
  <div class="bord bg"></div>
  <div style="background-color: #fff;">
    <div>
      <p style="text-align: right;">订单总额：<span style="color: red">￥ <?php if ($_smarty_tpl->tpl_vars['goodsinfo']->value['promote_price'] != '0.00') {
echo $_smarty_tpl->tpl_vars['goodsinfo']->value['promote_price'];
} else {
echo $_smarty_tpl->tpl_vars['goodsinfo']->value['market_price'];
}?></span></p>
      <p style="text-align: right;">应付金额：<span style="color: red">￥ <?php if ($_smarty_tpl->tpl_vars['goodsinfo']->value['promote_price'] != '0.00') {
echo $_smarty_tpl->tpl_vars['goodsinfo']->value['promote_price'];
} else {
echo $_smarty_tpl->tpl_vars['goodsinfo']->value['market_price'];
}?></span></p>
    </div>
  </div>
  <div style="width: 80%;background-color: #f60;color: #fff;margin: 0 auto;margin-top:40px;border-radius: 3px;height: 40px;">
      <p style="color: #fff;font-size: 16px;text-align: center;line-height: 40px;" onclick="pay_ment('<?php echo $_smarty_tpl->tpl_vars['url']->value;?>
');">立即支付</p>
    </div>
</div>
<?php echo '<script'; ?>
 type="text/javascript">
  function pay_ment(url){
    window.location.href=url;
  }
<?php echo '</script'; ?>
>
</body>
</html><?php }
}
