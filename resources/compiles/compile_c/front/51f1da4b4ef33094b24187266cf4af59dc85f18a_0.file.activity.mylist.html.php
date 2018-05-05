<?php
/* Smarty version 3.1.30, created on 2017-08-02 06:29:07
  from "/home/bendishangjia.com/www/resources/views/templates/front/activity.mylist.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_59817133649573_54701857',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '51f1da4b4ef33094b24187266cf4af59dc85f18a' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/activity.mylist.html',
      1 => 1501655159,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.headernew.html' => 1,
    'file:public.footernew.html' => 1,
  ),
),false)) {
function content_59817133649573_54701857 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_date_format')) require_once '/home/bendishangjia.com/www/vendor/smarty/plugins/modifier.date_format.php';
$_smarty_tpl->_subTemplateRender("file:public.headernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<!--编辑器皮肤-->
<link href="/style/css_two/quill.snow.css" rel="stylesheet">
<?php echo '<script'; ?>
 type="text/javascript" src="/style/js/jquery.form.js"><?php echo '</script'; ?>
>
<link rel="stylesheet" type="text/css" href="/style/css_two/release.css"/>
<link rel="stylesheet" type="text/css" href="/style/css_two/countdown.css" />
<link rel="stylesheet" href="/style/css_two/jquery.countdown.css" />
<?php echo '<script'; ?>
 type="text/javascript" src="/style/js/activity.js"><?php echo '</script'; ?>
>

<!--倒计时-->
<?php echo '<script'; ?>
 src="/style/js_two/jquery.countdown.js"><?php echo '</script'; ?>
>
<!--头部 start-->
<div class="top w by pf">
    <a class="back pa" href="index.php?a=index&c=activity">
        <img src="/style/img_two/back.png"/>
    </a>
    <p class="f18 cw w tc">活动详情</p>
</div>
<!--头部 end-->

<!--活动详情页 start-->
<p class="huodong_detail_title f18 cb pl10 pr10" style="margin-top:44px;"><?php echo $_smarty_tpl->tpl_vars['act']->value['name'];?>
</p>

<!--活动商家-->
<div class="huodong_detail_logo bw pr">
    <img class="huodong_img pa" src="<?php echo $_smarty_tpl->tpl_vars['act']->value['activity_thumb'];?>
">
    <div class="huodong_text">
        <p class="f14 cb"><?php echo $_smarty_tpl->tpl_vars['act']->value['shop_name'];?>
</p>
        <p class="f12 cg">添加日期：<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['act']->value['add_time'],"%Y-%m-%d");?>
</p>
    </div>
</div>
<!--活动详情页 end-->

<p class="huodong_detail_time f12 cw pl10 pr10 pr" style="margin-top:12px;">活动时间：<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['act']->value['start_date'],"%Y-%m-%d %H:%M");?>
时至<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['act']->value['end_date'],"%Y-%m-%d %H:%M");?>
时<span class="pa"><i></i><?php echo $_smarty_tpl->tpl_vars['act']->value['click_count'];?>
</span></p>

<form action="index.php?a=ajaxmylist&c=activity" method="post" id="questionnaireForm" class="w">
<!--内容部分 start-->
<div class="w clearfloat">
    <div id="contentlist" class="w" style="text-overflow:ellipsis; white-space:pre-wrap; word-wrap:break-word; word-break:normal;padding: 5px;">
        <?php echo $_smarty_tpl->tpl_vars['act']->value['desc'];?>

    </div>
    <?php if ($_smarty_tpl->tpl_vars['act']->value['brief']) {?>
    <p class="f14 cb pl10 pr10 ">活动简述：<?php echo $_smarty_tpl->tpl_vars['act']->value['brief'];?>
</p>
    <?php }?>
    <label class="pl10 tr f14 cb mt10" style="display:inline-block; width:60px;">姓名：</label>
    <input type="text" class="brt brb brl brr mt10 pl10" style="height:30px; width:210px;"   id="user" name="user"><br>
    <label class="pl10 tr f14 cb mt10" style="display:inline-block; width:60px;">手机号：</label>
    <input type="text" class="brt brb brl brr mt10 pl10" style="height:30px; width:210px;"  id="phone"  name="phone" > <br>
    <label class="pl10 tr f14 cb mt10" style="display:inline-block; width:60px; vertical-align:top;">留言：</label>
    <textarea class="brt brb brl brr mt10 pl10" name="" id="" cols="30" rows="10" style="width:210px;"></textarea>

</div>
<!--内容部分 end-->


<!--自定义表单部分 start-->
<div class="clearfloat pl10 pr10">
    <input type="hidden" class="brt brb brl brr"  name="id" id="a_id" value="<?php echo $_smarty_tpl->tpl_vars['act']->value['id'];?>
">
    <?php if ($_smarty_tpl->tpl_vars['act']->value['form_desc']['desc']) {?>
    <div class="w" id="zdytable">
        <!--单行-->
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['act']->value['form_desc']['desc']['txt'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <p class="w f14 cb mt20"><?php echo $_smarty_tpl->tpl_vars['item']->value['val'];?>
：</p>
        <input type="text" class="w bg mt10" name="txt[<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
]" value=""  class="txt" style="height: 40px;"><br>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>


        <!--多行-->
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['act']->value['form_desc']['desc']['text'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <p class="w f14 cb mt20"><?php echo $_smarty_tpl->tpl_vars['item']->value['val'];?>
：</p>
        <textarea  class="w bg mt10" rows="3" name="text[<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
]" required  class="text" style="height: 80px;"></textarea>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

        <!--单选-->

        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['act']->value['form_desc']['desc']['radioname'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <div class="mt20">
            <label class="w f14 cb "><?php echo $_smarty_tpl->tpl_vars['item']->value['val'];?>
：</label>
            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['item']->value['son'], 'item_son', false, 'key_son');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key_son']->value => $_smarty_tpl->tpl_vars['item_son']->value) {
?>
            <input class="f14 cb " type="radio" name="radioname[<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
]" value="<?php echo $_smarty_tpl->tpl_vars['item_son']->value['sub3'];?>
"  name="radioname[<?php echo $_smarty_tpl->tpl_vars['key_son']->value;?>
]" required><span><?php echo $_smarty_tpl->tpl_vars['item_son']->value['val'];?>
</span>
            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

        </div>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>


        <p></p>
        <!--多选-->
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['act']->value['form_desc']['desc']['checkname'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <div class="mt20">
            <label class="w f14 cb "><?php echo $_smarty_tpl->tpl_vars['item']->value['val'];?>
：</label>
            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['item']->value['son'], 'item_son', false, 'key_son');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key_son']->value => $_smarty_tpl->tpl_vars['item_son']->value) {
?>
            <input class="f14 cb " type="checkbox" name="checkname[<?php echo $_smarty_tpl->tpl_vars['key_son']->value;?>
]" value="<?php echo $_smarty_tpl->tpl_vars['item_son']->value['sub3'];?>
" required><span><?php echo $_smarty_tpl->tpl_vars['item_son']->value['val'];?>
</span>
            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

        </div>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

        <?php } else { ?>

        <?php }?>
    </div>
</div>
</form>

<!--自定义表单部分 end-->




<!--活动倒计时 start-->
<?php if ($_smarty_tpl->tpl_vars['act']->value['time_b'] != 0) {?>
<input type="hidden" id="timeb" value="<?php echo $_smarty_tpl->tpl_vars['act']->value['time_b'];?>
"/>
<input type="hidden" id="starttime" value="<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['act']->value['start_date'],'%Y/%m/%d %H:%M:%S');?>
"/><br/>
<input type="hidden" id="endtime"   value="<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['act']->value['end_date'],'%Y/%m/%d %H:%M:%S');?>
"/><br/>
<p id="note" class="tc f22 cb mt20 mb10"></p>
<div id="countdown" class="mt10"></div>
<?php } else { ?>
<p class="countdown_txt w f22 tc" style="color:#e50446;">活动已结束</p>
<?php }?>
<!--活动倒计时 end-->

<!--参加报名的人 start-->
<br/><br/>
<!--参加报名的人 start-->
<div class="clearfloat" style="margin:0 auto; width:90%;" id="setup" >
    <p class="f16 cb1">参与报名的人数</p>
    <div class=" bor br3 mt10"  style="height:250px;">
        <div class="huodong_banner swiper-container w pr bw" style="height:240px;">
            <i></i>
            <div class="swiper-wrapper">
                <div class="swiper-slide f14 cb clearfloat w" id="signlist"></div>
            </div>
            <a class="more" href="javascript:void(0);"></a>
        </div>
    </div>
</div>
<!--参加报名的人 end-->


<?php if ($_smarty_tpl->tpl_vars['aco']->value) {?>
<!--合作商家 start-->
<div class="saller_box clearfloat w" style="display:block;">
    <p class="f16 cb1" style="margin:10px auto; width:90%;">合作商家</p>
    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['aco']->value['aco'], 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
    <a class="wrap fl bg w333 tc" style="display:block; height:110px;" href="javascript:void(0);" onclick="jump_url('<?php echo $_smarty_tpl->tpl_vars['aco']->value['surl'];
echo $_smarty_tpl->tpl_vars['item']->value['act_id'];?>
')">
        <img class="ma pt10" src="/<?php echo $_smarty_tpl->tpl_vars['item']->value['shop_logo'];?>
" style="width:60px; height:60px;"/>
        <p class="fl12 cb tc mt10"><?php echo $_smarty_tpl->tpl_vars['item']->value['shop_logo'];?>
</p>
    </a>
    <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

</div>
<!--合作商家 end-->
<?php }?>

<br>
<br>
<br>
<br>
<!--报名按钮 start-->
<?php if ($_smarty_tpl->tpl_vars['act']->value['time_c'] != 0) {?>
<div class="huodong_btn w  clearfloat">
    <div class="wrap">
        <a class="f18 cw tc by br3" href="javascript:void(0);" onclick="Applicationform()">我要报名</a>
    </div>
</div>
<?php }?>
<!--报名按钮 end-->
<br><br><br><br><br><br><br><br>
<?php $_smarty_tpl->_subTemplateRender("file:public.footernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>


</body>
<!-- Swiper JS -->


<!-- Initialize Swiper -->
<?php echo '<script'; ?>
>
    function  Swiper_Swiper(){
        var swiper1 = new Swiper('.huodong_banner', {
            autoplay: 3000,
            direction: 'vertical',
            slidesPerView: 1,
            loop:true
        });
    }

    $('#contentlist img').each(
            function(){
                $(this).addClass('w');
            }
    );
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
//    window.jQuery(function ($) {
//        "use strict";
//        $('time').countDown({
//            with_separators: false
//        });
//        $('.alt-1').countDown({
//            css_class: 'countdown-alt-1'
//        });
//        $('.alt-2').countDown({
//            css_class: 'countdown-alt-2'
//        });
//    });
    $(function(){
        sign_a();
        $("#zdytable input[type='radio']").each(function(){
            $(this).first().attr('checked', 'checked');
        });
    })










<?php echo '</script'; ?>
>

</html><?php }
}
