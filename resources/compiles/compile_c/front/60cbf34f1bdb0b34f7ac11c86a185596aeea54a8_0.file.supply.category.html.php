<?php
/* Smarty version 3.1.30, created on 2017-06-04 15:31:21
  from "/home/bendishangjia.com/www/resources/views/templates/front/supply.category.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5933b7498e1889_13262835',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '60cbf34f1bdb0b34f7ac11c86a185596aeea54a8' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/supply.category.html',
      1 => 1496190525,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_5933b7498e1889_13262835 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="user-basic">
    <div class="return" data-href="index.php?a=index&c=supply"><i></i></div>
    <h1>供应类别</h1>
</div>
<div id="category">
<?php echo $_smarty_tpl->tpl_vars['category']->value;?>

</div>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
    $(function(){
        $('#category li').each(function(){
            var aa=$(this).find('ul').length;
            if(aa==0){
                $(this).parent().addClass('yes');
            }
        })
    })
<?php echo '</script'; ?>
>
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
