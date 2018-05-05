<?php
/* Smarty version 3.1.30, created on 2017-07-20 03:53:23
  from "/home/bendishangjia.com/www/resources/views/templates/front/local.index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_596fb8b332c5c8_16238744',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'fddf8dbb66ff4eb5c57582350218474c04b929ed' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/local.index.html',
      1 => 1499232636,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.headernew.html' => 1,
    'file:public.footernew.html' => 1,
  ),
),false)) {
function content_596fb8b332c5c8_16238744 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.headernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="main">
    <!--头部 start-->
    <div class="top w by pf">
        <a class="back pa" href="index.php?a=info&c=local">
            <img src="/style/img_two/back.png"/>
        </a>
        <p class="f18 cw w tc">本地信息类目</p>
    </div>
    <!--类目 start-->
    <div class="classify_list clearfloat">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['shopcat']->value, 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
            <a class="fl w25 tc" href="index.php?a=infonew&c=local&type_id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
"><p class="f14 cb br3 b"><?php echo $_smarty_tpl->tpl_vars['item']->value['cat_name'];?>
</p></a>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

    </div>
    <!--类目 end-->

    <?php $_smarty_tpl->_subTemplateRender("file:public.footernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
