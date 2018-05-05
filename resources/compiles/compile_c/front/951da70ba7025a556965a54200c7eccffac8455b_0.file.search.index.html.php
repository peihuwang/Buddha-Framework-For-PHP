<?php
/* Smarty version 3.1.30, created on 2017-07-03 13:15:25
  from "/home/bendishangjia.com/www/resources/views/templates/front/search.index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5959d2ed049b70_78288588',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '951da70ba7025a556965a54200c7eccffac8455b' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/search.index.html',
      1 => 1499058825,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_5959d2ed049b70_78288588 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="user-basic">
    <div class="return" data-href="/"><i></i></div>
    <h1>店铺搜索</h1>
</div>
    <div id="agent_retrieval">
        <input type="text" value="<?php echo $_smarty_tpl->tpl_vars['keyword']->value;?>
" id="keyword" name="keyword" placeholder="输入店铺名称或者道路名称">
        <button type="button" id="retrie">搜索</button>
    </div>
<div id="list">
    <!--    <div class="nearby">
            <div class="pic_img"><img src="style/img/dian.jpg"></div>
            <div class="nearby_con">
                <div class="title">哦多尅新式面包店哦多尅新式面包店哦多尅新式面包店哦多尅新式面包店</div>
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
 src="/style/js/ajaxshopsearchlist.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
    $(function(){
        var position=getCookie("sName");
         position=JSON.parse(position);
           var number= position.adcode;
        $('#retrie').on('click',function () {
            var keyword=$('#keyword').val();
            if(keyword==''){
                alert('你还没有输入搜索标题，请输入吧！');
                return false;
            }else{
                var url='ajax/?a=shop&c=search';
                url=url+'&keyword='+keyword+'&number='+number;
                $('#list').ajaxshiolist(url);
            }
        })
    })
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 id="demo" type="text/html">
    {{each list as value i}}
    <div class="nearby" data-href="index.php?a=mylist&c=shop&id={{value.id}}">
        <div class="pic_img"><img src="/{{value.small}}"></div>
        <div class="nearby_con">
            <div class="title">{{value.name}}</div>
            <div class="brief">{{value.brief}}</div>
            <div class="address"><span class="strong"><i></i>约 {{value.distance}}</span><span>{{value.roadfullname}}</span></div>
        </div>
    </div>
    {{/each}}
<?php echo '</script'; ?>
>
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
