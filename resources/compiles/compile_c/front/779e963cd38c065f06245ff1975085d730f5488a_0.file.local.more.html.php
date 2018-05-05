<?php
/* Smarty version 3.1.30, created on 2017-06-10 13:23:14
  from "/home/bendishangjia.com/www/resources/views/templates/front/local.more.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_593b8242719a72_61238394',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '779e963cd38c065f06245ff1975085d730f5488a' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/local.more.html',
      1 => 1497071859,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.headernew.html' => 1,
    'file:public.footernew.html' => 1,
  ),
),false)) {
function content_593b8242719a72_61238394 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.headernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

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
    <!--轮播图 start-->
    <div class="localinfo_banner pr h">
        <div class="swiper-container w h">
            <div class="swiper-wrapper">
                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['localmore']->value['img'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
                <div class="swiper-slide"><a href="index.php?a=index&c=shop&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
">
                    <img class="w" src="<?php echo $_smarty_tpl->tpl_vars['item']->value['large'];?>
" alt="<?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
"></a>
                </div>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
        </div>
    </div>
    <!--轮播图 end-->
    <!--分类 start-->
    <div class="tab_list pr w bw brb" style="top:0;" id="typelist">
        <ul class="w">
            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['localmore']->value['shopcat'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
                <li class="f16 cb fl tc w25" onclick="localmore(<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
,<?php echo $_smarty_tpl->tpl_vars['localmore']->value['local_p'];?>
)">
                    <span <?php if ($_smarty_tpl->tpl_vars['key']->value == 0) {?> class="active" <?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value['cat_name'];?>
 <input type="hidden" id="cat_id" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
"> </span>
                </li>
            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

        </ul>
    </div>
    <!--分类 end-->
    <!--列表 start-->
    <div class="main_list w" id="main_list">

    </div>


</div>
<br>
<br>
<br>


<?php $_smarty_tpl->_subTemplateRender("file:public.footernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php echo '<script'; ?>
 src="/style/js/ajaxlocalmore.js"><?php echo '</script'; ?>
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


<?php }
}
