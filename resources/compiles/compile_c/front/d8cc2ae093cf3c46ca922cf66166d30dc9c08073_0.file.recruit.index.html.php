<?php
/* Smarty version 3.1.30, created on 2017-07-01 13:50:19
  from "/home/bendishangjia.com/www/resources/views/templates/front/recruit.index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5957381b032af7_86750608',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd8cc2ae093cf3c46ca922cf66166d30dc9c08073' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/recruit.index.html',
      1 => 1498879098,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_5957381b032af7_86750608 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="user-basic">
    <div class="return" data-href="index.php?a=index&c=index"><i></i></div>
    <h1>招聘</h1>
    <div class="addto" data-href="business/index.php?a=add&c=recruit"><i></i></div>
</div>
<div class="tab_list">
    <ul>
        <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 1) {?> class="cur"<?php }?> data-href="index.php?a=index&c=recruit&view=1">附近</li>
        <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 2) {?> class="cur"<?php }?> data-href="index.php?a=index&c=recruit&view=2">最新</li>
        <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 3) {?> class="cur"<?php }?> data-href="index.php?a=index&c=recruit&view=3">热门</li>
        <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 4) {?> class="cur"<?php }?> data-href="index.php?a=index&c=recruit&view=4">薪酬</li>
        <li data-href="index.php?a=category&c=recruit">分类筛选</li>
    </ul>
</div>
<form method="get" action="index.php">
    <div id="agent_retrieval">
        <input type="hidden" name="a" value="index"/> <input type="hidden" name="c" value="list"/>
        <input type="text" value="" id="keyword" name="keyword" placeholder="请输入地址进行查找附近商家">
        <button type="submit" id="retrie">搜索</button>
    </div>
</form>
<div id="list">
<!--    <div class="sup_item" data-href="index.php?a=info&c=supply&<?php echo $_smarty_tpl->tpl_vars['location_area']->value;?>
">
        <div class="pic_img"><img src="style/img/dian.jpg"></div>
        <div class="nearby_con">
            <div class="title">哦多尅新式面包店哦多尅新式面包店哦多尅新式面包店哦多尅新式面包店</div>
            <div class="price"><i></i>价格：<em>￥<i>130.00</i></em></div>
            <div class="address"><span class="strong"><i></i>54km</span><span>上海普陀区白兰路137弄A座809室</span></div>
        </div>
    </div>-->
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
    <div class="sup_item" data-href="index.php?a=info&c=recruit&id={{value.id}}">
        {{if value.recruit_thumb}}
        <div class="pic_img"><img src="/{{value.recruit_thumb}}"></div>
        <div class="nearby_con">
            {{else}}
            <div class="nearby_con" style="margin-left:0">
                {{/if}}
                <div class="title">{{value.name}}</div>
                <div class="price"><i></i>{{if value.price ==''}}<em>面议</em>{{else}}预算：<em>￥<i>{{value.price}}</i></em>{{/if}}</div>
                <div class="address"><span
                        class="strong"><i></i>{{value.shop_name}}</span><span>{{value.roadfullname}}</span></div>
            </div>
        </div>
        {{/each}}
<?php echo '</script'; ?>
>
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
