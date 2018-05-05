<?php
/* Smarty version 3.1.30, created on 2017-06-05 14:53:22
  from "/home/bendishangjia.com/www/resources/views/templates/front/recruit.category.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5934ffe2476891_51034656',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '22a79ec84e73dbde38fb6f324a6c0955d4f0863e' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/recruit.category.html',
      1 => 1496190524,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_5934ffe2476891_51034656 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="user-basic">
    <div class="return" data-href="index.php?a=index&c=supply"><i></i></div>
    <h1>招聘类别</h1>
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
