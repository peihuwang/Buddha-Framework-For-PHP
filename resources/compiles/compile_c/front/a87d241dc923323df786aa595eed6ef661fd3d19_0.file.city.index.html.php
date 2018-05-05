<?php
/* Smarty version 3.1.30, created on 2017-11-22 23:03:37
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/city.index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a1591c9c28af3_35020827',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'a87d241dc923323df786aa595eed6ef661fd3d19' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/city.index.html',
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
function content_5a1591c9c28af3_35020827 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<div id="agent_retrieval">
    <input type="text" value="" id="keyword" name="keyword" placeholder="输入区或县的名称">
    <button type="button" id="retrie">搜索</button>
</div>

<div id="list"></div>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/template.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 id="city" type="text/html">
{{each list as value i}}
<div class="city">
    <ul>
        <h4>{{value.first}}</h4>
        {{each list[i] as value j}}
        {{if j<=20}}
        <li onclick="city('{{value.number}}')">{{value.namer}}</li>
        {{/if}}
        {{/each}}
        <li data-href="index.php?a=info&c=city&first={{value.first}}">更多>></li>
    </ul>
</div>
{{/each}}
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
    var url='index.php?a=ajax&c=city';
    region(url);
    $(function () {
        $('#retrie').on('click',function () {
           var keyword=$('#keyword').val();
            if(!keyword){
                Message.showNotify("请输入输入区或县的名称!", 500);
                return false;
            }
            url=url+'&keyword='+keyword;
            region(url);
        });
    });

function region(url) {
    $.get(url,function (re) {
        if(re==0){
            $('#list').html('你搜索的区县不存在！');
        }else {
            var html = template('city', re);
        }
        $('#list').html(html);
        $('[data-href]').on('click',function(){
            var url=$(this).attr('data-href');
            loadurl(url);//加载跳转链接
            return false;
        });
    });
}

    function city(number) {

    $.get('index.php?a=getnumber&c=city',{number:number},function (re){
        delCookie('sName');
        var str = JSON.stringify(re);
        setCookie('sName',str);
        window.location.href='/';
    });
}
<?php echo '</script'; ?>
>
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
