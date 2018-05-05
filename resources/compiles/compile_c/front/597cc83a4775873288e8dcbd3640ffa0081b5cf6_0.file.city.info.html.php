<?php
/* Smarty version 3.1.30, created on 2017-05-31 14:51:17
  from "/home/bendishangjia.com/www/resources/views/templates/front/city.info.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_592e67e50c7612_05351222',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '597cc83a4775873288e8dcbd3640ffa0081b5cf6' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/city.info.html',
      1 => 1496190522,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_592e67e50c7612_05351222 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="list">

    <div class="city">
        <ul>
            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['myzeoregion']->value, 'item', false, NULL, 'test', array (
  'iteration' => true,
));
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['__smarty_foreach_test']->value['iteration']++;
?>
            <?php if ((isset($_smarty_tpl->tpl_vars['__smarty_foreach_test']->value['iteration']) ? $_smarty_tpl->tpl_vars['__smarty_foreach_test']->value['iteration'] : null) == 1) {?>
            <h4><?php echo $_smarty_tpl->tpl_vars['item']->value['first'];?>
</h4>
            <?php } else { ?>
            <li onclick="city('<?php echo $_smarty_tpl->tpl_vars['item']->value['number'];?>
')"><?php echo $_smarty_tpl->tpl_vars['item']->value['namer'];?>
</li>
            <?php }?>
            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

        </ul>
    </div>

</div>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/template.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 >
    function city(number) {
    $.get('index.php?a=getnumber&c=city',{number:number},function (re){
    delCookie('sName');
    var str = JSON.stringify(re);
    setCookie('sName',str);
    window.location.href='/';
    });
    }
<?php echo '</script'; ?>
>

<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
