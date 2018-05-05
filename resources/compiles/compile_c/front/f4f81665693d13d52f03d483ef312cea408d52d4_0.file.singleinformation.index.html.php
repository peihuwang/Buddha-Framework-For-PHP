<?php
/* Smarty version 3.1.30, created on 2017-12-21 22:12:22
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/singleinformation.index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a3bc146929be3_65005833',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f4f81665693d13d52f03d483ef312cea408d52d4' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/singleinformation.index.html',
      1 => 1513623685,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_5a3bc146929be3_65005833 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="user-basic">
    <div class="return" data-href="index.php?a=index&c=index"><i></i></div>
    <h1><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</h1>

    <!-- <div class="addto" data-href="business/index.php?a=add&c=demand"><i></i></div> -->
</div>
<div class="tab_list">
    <ul>
        <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 1) {?>class="cur"<?php }?> data-href="index.php?a=index&c=<?php echo $_smarty_tpl->tpl_vars['c']->value;?>
&view=1">附近</li>
        <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 2) {?>class="cur"<?php }?> data-href="index.php?a=index&c=<?php echo $_smarty_tpl->tpl_vars['c']->value;?>
&view=2">最新</li>
        <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 3) {?>class="cur"<?php }?> data-href="index.php?a=index&c=<?php echo $_smarty_tpl->tpl_vars['c']->value;?>
&view=3">热门</li>
        <!-- <li <?php if ($_smarty_tpl->tpl_vars['view']->value == 4) {?>class="cur"<?php }?> data-href="index.php?a=index&c=demand&view=4">预算</li> -->
        <!--<li <?php if ($_smarty_tpl->tpl_vars['view']->value == 5) {?>class="cur"<?php }?> data-href="index.php?a=index&c=<?php echo $_smarty_tpl->tpl_vars['c']->value;?>
&view=5">商家</li>-->
        <!--<li data-href="index.php?a=category&c=demand">分类</li>-->
    </ul>
</div>
<form method="get" action="">
    <div id="agent_retrieval">
        <input type="hidden" name="a" value="index"/> <input type="hidden" name="c" value="demand"/>
        <input type="text" value="" id="keyword" name="keyword" placeholder="请输入地址进行查找附近商家">
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
    $(function()
    {
        var url=window.location.href;
        $('#list').ajaxshiolist(url);
    })
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 id="demo" type="text/html">
    {{each list as value i}}
    <div class="sup_item" data-href="index.php?a=mylist&c=singleinformation&id={{value.id}}">
        {{if value.singleinformation_thumb}}
            <div class="pic_img"><img src="{{value.singleinformation_thumb}}"></div>
            <div class="nearby_con">
        {{else}}
            <div class="nearby_con" style="margin-left:0">
        {{/if}}
            <div class="title">{{value.name}}</div>
            <div class="number"><i></i><em><i>{{value.number}}</i></em></div>
            <div class="address">
                <span class="strong"><i></i>{{value.shop_name}}</span><span>{{value.roadfullname}}</span></div>
            </div>
        </div>
    </div>
    {{/each}}
<?php echo '</script'; ?>
>

<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
