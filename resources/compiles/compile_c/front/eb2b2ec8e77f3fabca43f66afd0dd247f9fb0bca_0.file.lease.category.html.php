<?php
/* Smarty version 3.1.30, created on 2017-06-01 18:49:45
  from "/home/bendishangjia.com/www/resources/views/templates/front/lease.category.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_592ff14978e6d0_00552651',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'eb2b2ec8e77f3fabca43f66afd0dd247f9fb0bca' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/lease.category.html',
      1 => 1496190523,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_592ff14978e6d0_00552651 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="user-basic">
    <div class="return" data-href="index.php?a=index&c=lease"><i></i></div>
    <h1>租赁类别</h1>
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
