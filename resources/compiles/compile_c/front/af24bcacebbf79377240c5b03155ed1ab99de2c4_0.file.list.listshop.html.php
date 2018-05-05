<?php
/* Smarty version 3.1.30, created on 2017-07-10 00:56:25
  from "/home/bendishangjia.com/www/resources/views/templates/front/list.listshop.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_596260392d9f35_16570773',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'af24bcacebbf79377240c5b03155ed1ab99de2c4' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/list.listshop.html',
      1 => 1499421640,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_596260392d9f35_16570773 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="user-basic">
    <div class="return" data-href="index.php?a=index&c=list&storetype=<?php echo $_smarty_tpl->tpl_vars['store']->value;?>
"><i></i></div>
    <h1><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</h1>
</div>
<form method="get" action="index.php">
    <div id="agent_retrieval">
        <input type="hidden" name="a" value="listshop"/> <input type="hidden" name="c" value="list"/>
        <input type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['store']->value;?>
" name="storetype"><input type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['property']->value;?>
" name="property">
        <input type="text" value="<?php echo $_smarty_tpl->tpl_vars['keyword']->value;?>
" id="keyword" name="keyword" placeholder="输入道路名称检索">
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
    <div class="nearby" data-href="{{value.url}}{{value.id}}">
        <div class="pic_img"><img src="/{{value.small}}"></div>
        <div class="nearby_con">
            <div class="title">{{value.name}}</div>
            <div>{{value.brief}}</div>
            <div class="address"><span class="strong"><i></i>约 {{value.distance}}</span><span>{{value.roadfullname}}</span></div>
        </div>
    </div>
    {{/each}}
<?php echo '</script'; ?>
>
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
