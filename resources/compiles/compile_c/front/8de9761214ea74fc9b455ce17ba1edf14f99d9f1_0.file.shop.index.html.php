<?php
/* Smarty version 3.1.30, created on 2017-07-01 11:19:25
  from "/home/bendishangjia.com/www/resources/views/templates/front/shop.index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_595714bd7ef791_59973358',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '8de9761214ea74fc9b455ce17ba1edf14f99d9f1' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/shop.index.html',
      1 => 1498879106,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_595714bd7ef791_59973358 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="shop_store">
    <div class="title"><?php echo $_smarty_tpl->tpl_vars['shop']->value['name'];?>
</div>
   <div class="shop_info"><div class="shop_info_x">
       <span>真诚为您服务!</span><?php if ($_smarty_tpl->tpl_vars['shop']->value['is_verify'] == 1) {?><em>已认证<i>V</i>1</em><?php } else { ?>未认证 <?php }?></div>
       <div class="shop_info_tel"><i></i> 电话：
           <?php if ($_smarty_tpl->tpl_vars['shop']->value['is_verify'] == 1) {?><a href="tel:<?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
"><?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
</a>
           <?php } elseif ($_smarty_tpl->tpl_vars['shop']->value['is_verify'] == 0 && $_smarty_tpl->tpl_vars['shop']->value['verify'] == 1) {?>
                    <a href="tel:<?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
"><?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
</a>
           <?php } else { ?>
                <?php if ($_smarty_tpl->tpl_vars['see']->value == 1) {?>
                    <a href="tel:<?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
"><?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
</a>
                <?php } else { ?><em onclick="see(<?php echo $_smarty_tpl->tpl_vars['shop']->value['id'];?>
)">点击查看</em>
                <?php }?>
           <?php }?></div></div>
</div>
<div class="shop_menu">
    <ul>
        <li data-href="index.php?a=mylist&c=shop&id=<?php echo $_smarty_tpl->tpl_vars['shop']->value['id'];?>
"><i></i>促销</li>
        <li data-href="index.php?a=mylist&c=shop&view=supply&id=<?php echo $_smarty_tpl->tpl_vars['shop']->value['id'];?>
"><i></i>供应</li>
        <li data-href="index.php?a=mylist&c=shop&view=demand&id=<?php echo $_smarty_tpl->tpl_vars['shop']->value['id'];?>
"><i></i>需求</li>
        <li data-href="index.php?a=mylist&c=shop&view=recruit&id=<?php echo $_smarty_tpl->tpl_vars['shop']->value['id'];?>
"><i></i>招聘</li>
        <li data-href="index.php?a=mylist&c=shop&view=lease&id=<?php echo $_smarty_tpl->tpl_vars['shop']->value['id'];?>
"><i></i>租赁</li>
    </ul>
</div>
<div class="shop_nav tab">
<ul>
    <li class="cur">店铺简介</li>
    <li>联系我们</li>
    <li data-href="http://apis.map.qq.com/tools/routeplan/eword=<?php echo $_smarty_tpl->tpl_vars['shop']->value['specticloc'];?>
&epointx=<?php echo $_smarty_tpl->tpl_vars['shop']->value['lng'];?>
&epointy=<?php echo $_smarty_tpl->tpl_vars['shop']->value['lat'];?>
&policy=1?referer=myapp&key=HM5BZ-ICHKU-VDNVI-27RMJ-YZRCQ-P5BTP">位置导航</li>
</ul></div>
<div id="box_con">
<div class="box" style="display: block"><?php if ($_smarty_tpl->tpl_vars['shop']->value['shopdesc']) {
echo $_smarty_tpl->tpl_vars['shop']->value['shopdesc'];
} else { ?><img src="<?php echo $_smarty_tpl->tpl_vars['shop']->value['medium'];?>
" class="w"><?php }?></div>
<div  class="box">
店铺联系人：<?php echo $_smarty_tpl->tpl_vars['shop']->value['realname'];?>
<br>
开业时间：<?php echo $_smarty_tpl->tpl_vars['shop']->value['createtimestr'];?>
<br>
    <?php if ($_smarty_tpl->tpl_vars['shop']->value['is_verify'] == 1) {?>
        联系人手机：<a href="tel:<?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
"><?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
</a><br>
        联系人电话：<a href="tel:<?php echo $_smarty_tpl->tpl_vars['shop']->value['tel'];?>
"><?php echo $_smarty_tpl->tpl_vars['shop']->value['tel'];?>
</a><br>
    <?php } elseif ($_smarty_tpl->tpl_vars['shop']->value['is_verify'] == 0 && $_smarty_tpl->tpl_vars['shop']->value['verify'] == 1) {?><!--没有认证、没有过试用期-->
        联系人手机：<a href="tel:<?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
"><?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
</a><br>
        联系人电话：<a href="tel:<?php echo $_smarty_tpl->tpl_vars['shop']->value['tel'];?>
"><?php echo $_smarty_tpl->tpl_vars['shop']->value['tel'];?>
</a><br>
        qq：<?php echo $_smarty_tpl->tpl_vars['shop']->value['qq'];?>
<br>
    <?php } else { ?>
        <?php if ($_smarty_tpl->tpl_vars['see']->value == 0) {?><!--付费30min查看-->
        联系人手机：******<br>
        联系人电话：******<br>
        <?php } else { ?>
        联系人手机：<a href="tel:<?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
"><?php echo $_smarty_tpl->tpl_vars['shop']->value['mobile'];?>
</a><br>
        联系人电话：<a href="tel:<?php echo $_smarty_tpl->tpl_vars['shop']->value['tel'];?>
"><?php echo $_smarty_tpl->tpl_vars['shop']->value['tel'];?>
</a><br>
        qq：<?php echo $_smarty_tpl->tpl_vars['shop']->value['qq'];?>
<br>
        <?php }?>
    <?php }?>
店铺地址：<?php echo $_smarty_tpl->tpl_vars['shop']->value['specticloc'];?>

</div>
</div>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
    $('.tab').on('click','li',function(){
        var num=$(this).index();
        $(this).addClass('cur').siblings().removeClass('cur');
        $('#box_con').find('.box').eq(num).show().siblings().hide();
    });
    function see(id){
        Message.showConfirm("店铺未认证,支付0.2元查看联系方式", "确定", "取消", function () {
            $.post('/pay/?a=index&c=index',{id:id,good_table:'shop',payment_code:'wxpay',order_type:'info.see',final_amt:0.01},function(re){
                if(re.errcode==0){
                    window.location.href=''+re.url+'';
                }else{
                    Message.showNotify(""+re.errmsg+"", 1200);
                    setTimeout("window.location.href='"+re.url+"'",1300);
                }
            })
        });
    }
<?php echo '</script'; ?>
>
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
