<?php
/* Smarty version 3.1.30, created on 2017-07-29 13:46:01
  from "/home/bendishangjia.com/www/resources/views/templates/front/index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_597c211981df81_53346201',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '6cea179cbfb766eec50f9d545b2bb3bb3b43fb91' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/index.html',
      1 => 1501307158,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_597c211981df81_53346201 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

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
    <div id="agenttel">
        <span></span>
        <div class="tel">代理商电话<a href="tel:<?php echo $_smarty_tpl->tpl_vars['referral']->value['tel'];?>
"><?php echo $_smarty_tpl->tpl_vars['referral']->value['tel'];?>
</a></div>
    </div>
</div>
<div id="focus" class="focus hide">
<div class="hd"><ul></ul></div>
<div class="bd " id="mobile_home_1"></div>
</div>
<!--网站导航-->
<div id="index_meun">
    <ul>
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['storetype']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <li><a href="index.php?a=<?php if ($_smarty_tpl->tpl_vars['key']->value == 1 || $_smarty_tpl->tpl_vars['key']->value == 5) {?>shop<?php } else { ?>index<?php }?>&c=list&storetype=<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
"><span></span><?php echo $_smarty_tpl->tpl_vars['item']->value;?>
</a></li>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

        <li><a href="index.php?a=index&c=demand<?php echo $_smarty_tpl->tpl_vars['location_area']->value;?>
"><span></span>需求</a></li>
        <li><a href="index.php?a=index&c=supply<?php echo $_smarty_tpl->tpl_vars['location_area']->value;?>
"><span></span>供应</a></li>
        <li><a href="index.php?a=promote&c=supply<?php echo $_smarty_tpl->tpl_vars['location_area']->value;?>
"><span></span>促销</a></li>
        <li><a href="index.php?a=index&c=recruit<?php echo $_smarty_tpl->tpl_vars['location_area']->value;?>
"><span></span>招聘</a></li>
        <li><a href="index.php?a=index&c=lease<?php echo $_smarty_tpl->tpl_vars['location_area']->value;?>
"><span></span>租赁</a></li>
    </ul>
</div>
<!--广告位-->
<div id="focus1" class="focus hide">
<div class="hd"><ul></ul></div>
<div class="bd " id="mobile_home_2"></div>
</div>
<!-- END -->
<div id="leftTabBox" class="business">
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
    <div class="bd " id="mobile_home_3"></div>
</div>
<!-- END -->
<div id="leftTabhot" class="busihot">
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
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
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
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php }
}
