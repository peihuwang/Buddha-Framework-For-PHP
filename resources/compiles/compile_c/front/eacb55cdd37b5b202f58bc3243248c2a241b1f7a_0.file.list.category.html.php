<?php
/* Smarty version 3.1.30, created on 2017-06-06 13:44:49
  from "/home/bendishangjia.com/www/resources/views/templates/front/list.category.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_593641510fafc9_88624692',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'eacb55cdd37b5b202f58bc3243248c2a241b1f7a' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/list.category.html',
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
function content_593641510fafc9_88624692 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="user-basic">
    <div class="return" data-href="index.php?a=shop&c=list&type=<?php echo $_smarty_tpl->tpl_vars['type']->value;?>
"><i></i></div>
    <h1>店铺类别</h1>
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
