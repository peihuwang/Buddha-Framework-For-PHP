<?php
/* Smarty version 3.1.30, created on 2017-07-28 10:59:44
  from "/home/bendishangjia.com/www/resources/views/templates/front/supply.promote.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_597aa8a0ce6219_24591824',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ddc30a27e8276ab56729ecbe2010c4865b398e5c' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/supply.promote.html',
      1 => 1501206266,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_597aa8a0ce6219_24591824 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="user-basic">
    <div class="return" data-href="index.php?a=index&c=index"><i></i></div>
    <h1>促销</h1>
    <div class="addto" data-href="business/index.php?a=add&c=supplyinfo"><i></i></div>
</div>
</div>
<form method="get" action="index.php">
    <div id="agent_retrieval">
        <input type="hidden" name="a" value="promote"/> <input type="hidden" name="c" value="supply"/>
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
