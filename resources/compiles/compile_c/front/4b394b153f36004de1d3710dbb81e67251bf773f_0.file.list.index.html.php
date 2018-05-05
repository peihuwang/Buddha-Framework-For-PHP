<?php
/* Smarty version 3.1.30, created on 2017-12-17 15:22:56
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/list.index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a361b507ee709_55538854',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4b394b153f36004de1d3710dbb81e67251bf773f' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/list.index.html',
      1 => 1503729464,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_5a361b507ee709_55538854 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="user-basic">
    <div class="return" data-href="index.php?a=index&c=index"><i></i></div>
    <h1><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</h1>
</div>
<form method="get" action="index.php">
    <div id="agent_retrieval">
        <input type="hidden" name="a" value="index"/> <input type="hidden" name="c" value="list"/> <input type="hidden" name="storetype" value="<?php echo $_smarty_tpl->tpl_vars['store']->value;?>
"/>
        <input type="text" value="<?php echo $_smarty_tpl->tpl_vars['keyword']->value;?>
" id="keyword" name="keyword" placeholder="物业名称或者道路名称">
        <button type="submit" id="retrie">搜索</button>
    </div>
</form>
<div id="list">
</div>
<div class="div_null hide"></div>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/template.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/ajaxlist_s.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
 $(function(){
 var url=window.location.href;
 $('#list').ajaxshiolist(url);
 })
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 id="demo" type="text/html">
    {{each list as value i}}
    <div class="property" data-href="index.php?a=listshop&c=list&property={{value.property}}&storetype={{value.storetype}}">
        <span>物业名称 <p>{{value.property}}</p></span><b>共{{value.total}}个店铺</b></div>
    {{/each}}
<?php echo '</script'; ?>
>
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
