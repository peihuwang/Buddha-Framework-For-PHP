<?php
/* Smarty version 3.1.30, created on 2017-07-01 11:41:01
  from "/home/bendishangjia.com/www/resources/views/templates/front/supply.index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_595719cd2e3022_19831319',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'a6c901ff10d67c6eef6c7f4090507eb11b5f1667' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/supply.index.html',
      1 => 1498879149,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_595719cd2e3022_19831319 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="user-basic">
    <div class="return" data-href="index.php?a=index&c=index"><i></i></div>
    <h1>供应</h1>
    <div class="addto" data-href="business/index.php?a=add&c=supplyinfo"><i></i></div>
</div>
<div class="tab_list">
    <ul>
        <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 1) {?>class="cur"<?php }?> data-href="index.php?a=index&c=supply&view=1">附近</li>
        <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 2) {?>class="cur"<?php }?> data-href="index.php?a=index&c=supply&view=2">最新</li>
        <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 3) {?>class="cur"<?php }?> data-href="index.php?a=index&c=supply&view=3">热门</li>
        <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 4) {?>class="cur"<?php }?> data-href="index.php?a=index&c=supply&view=4">促销</li>
        <li data-href="index.php?a=category&c=supply">分类筛选</li>
    </ul>
</div>
<form method="get" action="index.php">
    <div id="agent_retrieval">
        <input type="hidden" name="a" value="index"/> <input type="hidden" name="c" value="supply"/>
        <input type="text" value="" id="keyword" name="keyword" placeholder="请输入地址进行查找附近商家">
        <button type="submit" id="retrie">搜索</button>
    </div>
</form>
<div id="list"></div>
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
    <div class="sup_item" data-href="index.php?a=info&c=supply&id={{value.id}}">
        <div class="pic_img"><img src="/{{value.goods_thumb}}"></div>
        <div class="nearby_con">
            <div class="title">{{value.goods_name}}</div>
            <div class="price"><i></i>价格：<em>￥<i>{{value.price}}</i></em></div>
            <div class="address"><span class="strong"><i></i> 约 {{value.distance}}</span><span>{{value.roadfullname}}</span></div>
        </div>
    </div>
    {{/each}}
<?php echo '</script'; ?>
>
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
