<?php
/* Smarty version 3.1.30, created on 2017-12-17 18:57:25
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/list.shop.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a364d95dcc634_64167315',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f77c07a707e14e99bf17b9879a6bd0b0defd3cbf' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/list.shop.html',
      1 => 1506086308,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_5a364d95dcc634_64167315 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<style type="text/css">
    .showImage{
        float: left;
        right: 2px;
        bottom: 2px;
        display: block;
        position: absolute;
        background: url(../style/images/icon_reward.png) no-repeat;
        width: 30px;
        height: 30px;
    }
</style>
<div id="user-basic">
    <div class="return" data-href="/"><i></i></div>
    <h1><?php if ($_smarty_tpl->tpl_vars['type']->value == 'is_rec') {?>推荐店铺<?php } elseif ($_smarty_tpl->tpl_vars['type']->value == 'is_nws') {?>最新开业<?php } elseif ($_smarty_tpl->tpl_vars['type']->value == 'is_promotion') {?>促销店铺<?php } elseif ($_smarty_tpl->tpl_vars['type']->value == 'is_hot') {?>热门店铺<?php } else {
echo (($tmp = @$_smarty_tpl->tpl_vars['title']->value)===null||$tmp==='' ? '附近商家' : $tmp);
}?></h1>
</div>
<?php if ($_smarty_tpl->tpl_vars['type']->value) {?>
<div class="tab_list">
    <ul>
        <li <?php if ($_smarty_tpl->tpl_vars['type']->value == 'is_rec') {?>class="cur"<?php }?> data-href="index.php?a=shop&c=list&type=is_rec">推荐</li>
        <li <?php if ($_smarty_tpl->tpl_vars['type']->value == 'is_nws') {?>class="cur"<?php }?>  data-href="index.php?a=shop&c=list&type=is_nws">最新</li>
        <li <?php if ($_smarty_tpl->tpl_vars['type']->value == 'is_promotion') {?>class="cur"<?php }?> data-href="index.php?a=shop&c=list&type=is_promotion">促销</li>
        <li <?php if ($_smarty_tpl->tpl_vars['type']->value == 'is_hot') {?>class="cur"<?php }?> data-href="index.php?a=shop&c=list&type=is_hot">热门</li>
        <li data-href="index.php?a=category&c=list&type=<?php echo $_smarty_tpl->tpl_vars['type']->value;?>
">分类</li>
    </ul>
</div>
<?php }?>
<form method="get" action="index.php">
    <div id="agent_retrieval">
        <input type="hidden" name="a" value="shop"/> <input type="hidden" name="c" value="list"/>
        <input type="text" value="<?php echo $_smarty_tpl->tpl_vars['keyword']->value;?>
" id="keyword" name="keyword" placeholder="请输入地址进行查找附近商家">
        <button type="submit" id="retrie">搜索</button>
    </div>
</form>
<div id="list">
<!--<div class="nearby">
        <div class="pic_img"><img src="style/img/dian.jpg"></div>
        <div class="nearby_con">
            <div class="title">哦多尅新式面包店哦多尅新式面包店哦多尅新式面包店哦多尅新式面包店</div>
            <div class="address"><span class="strong"><i></i>54km</span><span>上海普陀区白兰路137弄A座809室</span></div>
        </div>
    </div>-->
</div>
<div id="allmap"></div>
<div class="div_null hide"></div>
<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/style/js/template.js"><?php echo '</script'; ?>
>
<?php if ($_smarty_tpl->tpl_vars['store']->value) {
echo '<script'; ?>
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
<?php } else {
echo '<script'; ?>
 type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=g4GIGbi1Ca4AvOummygB6djDIo5DUPrV"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
    $(function(){
        // 百度地图API功能
        var map = new BMap.Map("allmap");
        var point = new BMap.Point(120.93588079,30.850623);
        map.centerAndZoom(point,12);

        var geolocation = new BMap.Geolocation();
        geolocation.getCurrentPosition(function(r){
            if(this.getStatus() == BMAP_STATUS_SUCCESS){
                var mk = new BMap.Marker(r.point);
                map.addOverlay(mk);
                map.panTo(r.point);
                var lng = r.point.lng;
                var lat = r.point.lat;
                var url=window.location.href;
                url = url+'&lat='+lat+'&lng='+lng;
                $('#list').ajaxshiolist(url);
                //alert('您的位置：'+r.point.lng+','+r.point.lat);
            }
            else {
                alert('failed'+this.getStatus());
            }        
        },{enableHighAccuracy: true})
    })
    $.fn.ajaxshiolist = function(url) {
    var PageSize = 15, p = 1;
    //$(window).scroll(scrollHandler);//执行滚动
    ajaxlist(1, url);//默认加载一页
    function ajaxlist(p, url) {
        $('#showLoading').show();
        var act = 'list';
        $.ajax({
            type: "get",
            url: url,
            data: {PageSize: PageSize, p: p, act: act},
            dataType: "json",
            //cache:false,
            success: function (o) {
                $('#showLoading').hide();
                if(o.isok == 'true') {
                    insertListDiv(o);
                }else{
                    $('.div_null').html(o.data).removeClass('hide');
                }
            },
            beforeSend: function () {
            },
            error: function () {
                $('#showLoading').hide();
            }
        });
    }
}

function insertListDiv(json){
    var html = template('demo', json);
    $('#list').append(html);
    $('[data-href]').on('click',function(e){
        var url=$(this).attr('data-href');
        loadurl(url);//加载跳转链接
        return false;
    });
}
<?php echo '</script'; ?>
>
<?php }
echo '<script'; ?>
 id="demo" type="text/html">
    {{each list as value i}}
    <div class="nearby" data-href="index.php?a=mylist&c=shop&id={{value.id}}">
        <div class="pic_img" style="position: absolute; "><img src="/{{value.small}}">
            {{if value.icon_shang}}
            <span class="showImage"></span>
            {{/if}}
        </div>
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
