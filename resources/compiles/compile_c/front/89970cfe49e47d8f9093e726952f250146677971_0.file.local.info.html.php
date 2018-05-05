<?php
/* Smarty version 3.1.30, created on 2017-06-10 13:09:46
  from "/home/bendishangjia.com/www/resources/views/templates/front/local.info.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_593b7f1ad43664_47930404',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '89970cfe49e47d8f9093e726952f250146677971' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/local.info.html',
      1 => 1497071219,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.headernew.html' => 1,
    'file:public.footernew.html' => 1,
  ),
),false)) {
function content_593b7f1ad43664_47930404 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.headernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<body>
<!--头部 start-->
<div class="top w by pf">
    <a class="back pa" href="index.php?a=index&c=index">
        <img src="/style/img_two/back.png"/>
    </a>
    <p class="f18 cw w tc">本地信息</p>
    <a class="classify pa" href="index.php?a=index&c=local">
        <img src="/style/img_two/classify.png"/>
    </a>
</div>
<!--头部 end-->

<div id="main" style="padding-top:44px;">

</div>
<br>
<br>
<br>



<!--底部导航 end-->

<?php echo '<script'; ?>
 src="/style/js/ajaxlocal.js"><?php echo '</script'; ?>
>
<!-- Swiper JS -->
<?php echo '<script'; ?>
 src="/style/js_two/swiper.min.js"><?php echo '</script'; ?>
>

<?php echo '<script'; ?>
 type="text/javascript">
    $(function(){
        $('#main').ajaxshiolist();
    })
<?php echo '</script'; ?>
>


<?php $_smarty_tpl->_subTemplateRender("file:public.footernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
