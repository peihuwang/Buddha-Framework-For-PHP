<?php
/* Smarty version 3.1.30, created on 2017-08-17 11:23:55
  from "/home/bendishangjia.com/www/resources/views/templates/front/public.shopping.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_59950c4b9b41a4_59469622',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd3fa0b1c8c94541f974e29e014a085e0b0c820e5' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/public.shopping.html',
      1 => 1502940219,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_59950c4b9b41a4_59469622 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!--底部导航 start-->
<div class="f_nav w pf brt bw clearfloat" id="public_foot">
    <a style="width: 10%;" class="nav_home fl f12 cb tc w25" href="/index.php?a=index&c=index" data-a="index" data-c="index">首页</a>
    <a style="width: 10%;" class="nav_shop fl f12 cb tc" href="index.php?a=mylist&c=shop&id=<?php echo $_smarty_tpl->tpl_vars['goods']->value['shop_id'];?>
"  onclick="publicfoot(2)" data-index="2">店铺</a>
    <a style="width: 10%;" class="nav_info fl f12 cb tc w25" href="/index.php?a=infonew&c=local"  data-a="infonew" data-c="local">收藏</a>
    <p style="width: 35%;background-color:#ff9c00;color: #FFF;height: 46px;font-size: 14px;line-height: 40px" class="nav_near fl f12 cb tc w25">加 入 购 物 车</p>
    <p style="width: 35%;background-color: red;color: #FFF;height: 46px;font-size: 14px;line-height: 40px;" class="nav_mine fl f12 cb tc w25" onclick="shopping(<?php echo $_smarty_tpl->tpl_vars['goods']->value['id'];?>
,<?php echo $_smarty_tpl->tpl_vars['goods']->value['promote_price'];?>
?<?php echo $_smarty_tpl->tpl_vars['goods']->value['promote_price'];?>
:<?php echo $_smarty_tpl->tpl_vars['goods']->value['market_price'];?>
);">立 即 购 买</p>
    <input type="hidden" name="uid" id="uid" value="<?php echo $_smarty_tpl->tpl_vars['uid']->value;?>
">
</div>
</body>
</html>
<?php echo '<script'; ?>
>
    $("#public_foot .fl").click(function(){
        $('#public_foot .fl').each(function(){
            $('#public_foot .fl').remove('active');
        })
        $(this).addClass('active');
    });
    function shopping(id,money){
        var uid = $('#uid').val();
        $.ajax({
            type:'post',
            url:'index.php?a=shopping&c=supply',
            data:{id:id,money:money,uid:uid},
            dataType:'json',
            success:function(o){
                if(o.isok == 'true'){
                     //Message.showNotify(""+ o.data+"", 1500);
                    setTimeout("window.location.href='"+o.url+"'");
                }else{
                    Message.showMessage("服务器忙！");
                }
            }
        }); 
    }
<?php echo '</script'; ?>
>
<?php if ($_smarty_tpl->tpl_vars['signPackage']->value) {
echo '<script'; ?>
 src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
    // 注意：所有的JS接口只能在公众号绑定的域名下调用，公众号开发者需要先登录微信公众平台进入“公众号设置”的“功能设置”里填写“JS接口安全域名”。
    // 如果发现在 Android 不能分享自定义内容，请到官网下载最新的包覆盖安装，Android 自定义分享接口需升级至 6.0.2.58 版本及以上。
    // 完整 JS-SDK 文档地址：http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
    wx.config({
        appId: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['appId'];?>
',
        timestamp: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['timestamp'];?>
',
        nonceStr: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['nonceStr'];?>
',
        signature: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['signature'];?>
',
        jsApiList: [
        // 所有要调用的 API 都要加到这个列
        'checkJsApi',
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
        'onMenuShareWeibo'
    ]
    });
    wx.ready(function () {
        // 在这里调用 API

        wx.checkJsApi({
            jsApiList: [
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'onMenuShareWeibo',
                'onMenuShareWeibo'
            ],
            success: function (res) {
                // alert(JSON.stringify(res));
                // alert(JSON.stringify(res.checkResult.getLocation));
                if (res.checkResult.getLocation == false) {
                    alert('你的微信版本太低，不支持微信JS接口，请升级到最新的微信版本！');
                    return;
                }
            }
        });

        wx.onMenuShareTimeline({
            title: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_title'];?>
', // 分享标题
            link:  '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_link'];?>
', // 分享链接
            imgUrl: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_imgUrl'];?>
', // 分享图标
            success: function () {
                var uid = $('#uid').val();
                alert(uid);
                $.ajax({
                    type:'post',
                    url:'',
                    data:{},
                    dataType:'',
                    success:function(){

                    }
                });
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });



        wx.onMenuShareAppMessage({
            title: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_title'];?>
', // 分享标题
            desc: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_desc'];?>
', // 分享描述
            link: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_link'];?>
', // 分享链接
            imgUrl: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_imgUrl'];?>
', // 分享图标
            type: '', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                var uid = $('#uid').val();
                alert(uid);
                $.ajax({
                    type:'post',
                    url:'',
                    data:{},
                    dataType:'',
                    success:function(){

                    }
                });
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });

        wx.onMenuShareQQ({
            title: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_title'];?>
', // 分享标题
            desc:'<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_desc'];?>
', // 分享描述
            link: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_link'];?>
', // 分享链接
            imgUrl: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_imgUrl'];?>
', // 分享图标
            success: function () {
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });

        wx.onMenuShareWeibo({
            title:'<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_title'];?>
', // 分享标题
            desc: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_desc'];?>
', // 分享描述
            link: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_link'];?>
', // 分享链接
            imgUrl: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_imgUrl'];?>
', // 分享图标
            success: function () {
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });

        wx.onMenuShareQZone({
            title: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_title'];?>
', // 分享标题
            desc: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_desc'];?>
', // 分享描述
            link: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_link'];?>
', // 分享链接
            imgUrl: '<?php echo $_smarty_tpl->tpl_vars['signPackage']->value['share_imgUrl'];?>
', // 分享图标
            success: function () {
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });


    });
<?php echo '</script'; ?>
>
<?php }?>

</body>
</html>
<?php }
}
