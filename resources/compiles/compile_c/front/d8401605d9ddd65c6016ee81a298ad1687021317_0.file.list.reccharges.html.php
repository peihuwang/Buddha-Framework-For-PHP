<?php
/* Smarty version 3.1.30, created on 2017-12-17 15:21:44
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/list.reccharges.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a361b08ea5017_03952787',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd8401605d9ddd65c6016ee81a298ad1687021317' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/list.reccharges.html',
      1 => 1511946836,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_5a361b08ea5017_03952787 (Smarty_Internal_Template $_smarty_tpl) {
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
    <h1>有赏店铺</h1>
</div>

<div id="list">

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
