<?php
/* Smarty version 3.1.30, created on 2017-08-17 23:04:50
  from "/home/bendishangjia.com/www/resources/views/templates/front/shop.mylist.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5995b092bd3264_45295104',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'b11c0c234864150ce8a4c240fcb9cada729dd8d1' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/shop.mylist.html',
      1 => 1502982286,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.headernew.html' => 1,
    'file:public.footernew.html' => 1,
  ),
),false)) {
function content_5995b092bd3264_45295104 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.headernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<link href="/style/css/web.style.css" rel="stylesheet" type="text/css">
<?php echo '<script'; ?>
 src="/style/js/template.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/ajaxlist.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js_two/swiper.min.js"><?php echo '</script'; ?>
>
<style type="text/css">
    /*滚动选项卡样式*/
    .swiper-container{width:100%; height:auto;}
    .swiper-slide{text-align:center; font-size:18px; background:#fff; display:-webkit-box; display:-ms-flexbox; display:-webkit-flex; display:flex;  -webkit-box-pack:center; -ms-flex-pack:center; -webkit-justify-content:center; justify-content:center; -webkit-box-align:center; -ms-flex-align:center; -webkit-align-items:center; align-items:center; height:100%; opacity:0.8; -webkit-transition:300ms; -moz-transition:300ms; -ms-transition:300ms; -o-transition:300ms; transition:300ms; -webkit-transform:scale(0.6,0.6)!important; -moz-transform:scale(0.6,0.6)!important; -ms-transform:scale(0.6,0.6)!important; -o-transform:scale(0.6,0.6)!important; transform:scale(0.6,0.6)!important;}
    .swiper-slide-prev,
    .swiper-slide-next{opacity:0.8; -webkit-transform:scale(0.8,0.8)!important; -moz-transform:scale(0.8,0.8)!important; -ms-transform:scale(0.8,0.8)!important; -o-transform:scale(0.8,0.8)!important; transform:scale(0.8,0.8)!important;}
    .swiper-slide-active{top:0; opacity:1; -webkit-transform:scale(1,1)!important; -moz-transform:scale(1,1)!important; -ms-transform:scale(1,1)!important; -o-transform:scale(1,1)!important; transform:scale(1,1)!important;}
    .swiper-slide img{width:50px;}
</style>
<div class="myshop">
    <div class="myshop_head brb pf w">
        <p class="shop_name f18 cw pl20 pr20"><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['name'];?>
 <?php echo $_smarty_tpl->tpl_vars['info']->value;?>
<span><a class="address fr" href="http://apis.map.qq.com/tools/routeplan/eword=<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['specticloc'];?>
&epointx=<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['lng'];?>
&epointy=<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['lat'];?>
&policy=1?referer=myapp&key=HM5BZ-ICHKU-VDNVI-27RMJ-YZRCQ-P5BTP""><img src="/style/img_two/myshop_nav.png"/></a></span></p>
        <div class="content pl20 pr20 clearfloat">
            <div class="attestation fr">
                <i></i>&nbsp;<span class="f16 cw"><?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify'] == 1) {?><em>已认证</em><?php } else { ?>未认证 <?php }?></span>
            </div>
            <div class="phonenumber fl" >
                <i></i>&nbsp;
                <?php if ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify'] == 1) {?>
                    <a  class="f16 cw"  href="tel:<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
"  style="display: inline-block ;"><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
</a>
                <?php } elseif ($_smarty_tpl->tpl_vars['shopinfo']->value['is_verify'] == 0 && $_smarty_tpl->tpl_vars['shopinfo']->value['verify'] == 1) {?>
                    <a  class="f16 cw"  href="tel:<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
"  style="display: inline-block ;"><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
</a>
                <?php } else { ?>
                    <?php if ($_smarty_tpl->tpl_vars['see']->value == 1) {?>
                    <a  class="f16 cw"  href="tel:<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
"  style="display: inline-block ;"><?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['mobile'];?>
</a>
                <?php } else { ?><a  class="f16 cw" href="javascript:void(0);" onclick="see(<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['id'];?>
)"  style="display: inline-block ;">查看</a>
                    <?php }?>
                <?php }?>
                <!--<a class="f16 cw" href="javaScript:viod(0);" style="display:inline-block;">查看</a>-->
            </div>
        </div>
        <div class="pr">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['header_category']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
                    <div class="swiper-slide <?php if ($_smarty_tpl->tpl_vars['key']->value == 0) {?>swiper-slide-active <?php }?>" data-id="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" data-err="<?php echo $_smarty_tpl->tpl_vars['item']->value['err'];?>
"><img src="/style/img_two/myshop_<?php echo $_smarty_tpl->tpl_vars['item']->value['img'];?>
.png" alt="<?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
"></div>
                    <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

                </div>
                <input type="hidden" id="s_id" value="<?php echo $_smarty_tpl->tpl_vars['shopinfo']->value['id'];?>
">
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
    <div id="myshop_content" style="padding-top:184px;"></div>
</div>
<br/>
<br/>
<br/>
<br/>
<br/>

<?php $_smarty_tpl->_subTemplateRender("file:public.footernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php echo '<script'; ?>
 type="text/javascript">
    $(function(){
//        var url=window.location.href;
        $('#myshop_content').ajaxshiolist();
    })
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
    var swiper = new Swiper('.swiper-container', {
        pagination: '.pagination',
        slidesPerView: 5,
        paginationClickable: true,
        spaceBetween: 30,
        centeredSlides: true,
        loop:true,
        slideToClickedSlide:true,
        onSlideChangeEnd: function(swiper, event){
            var data_active_index = $(".swiper-slide-active").attr("data-swiper-slide-index");
            var data_e = $(".swiper-slide-active").data();
            var view=data_e.err;
            var s_id=$('#s_id').val();
//            alert(data_active_index);
//            console.log(data);
            $('#myshop_content').empty();
            ajaxlist(1);
//            $("#myshop_content").html('我是加载出来的...');
        }
    });
<?php echo '</script'; ?>
>





<?php echo '<script'; ?>
>
    $('.tab').on('click','li',function(){
        var num=$(this).index();
        $(this).addClass('cur').siblings().removeClass('cur');
        $('.shop_menu li').removeClass('scurs');
        $('#list').remove();
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
><?php }
}
