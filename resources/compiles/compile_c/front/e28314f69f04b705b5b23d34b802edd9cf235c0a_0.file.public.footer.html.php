<?php
/* Smarty version 3.1.30, created on 2017-08-26 21:17:05
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/public.footer.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_59a174d1e49553_44547874',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e28314f69f04b705b5b23d34b802edd9cf235c0a' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/public.footer.html',
      1 => 1503729464,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_59a174d1e49553_44547874 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div id="footer">
    <div class="meun" style="height: 50px;">
        <ul>
            <li><a href="/index.php?a=index&c=index" ><span><b></b>首页</span></a></li>
            <li><a href="/index.php?a=infonew&c=local"><span><b></b>本地信息</span></a></li>
            <li><a href="/index.php?a=shop&c=list"><span><b></b>附近商家</span></a></li>
            <li><a href="../index.php?a=index&c=ucenter"><span><b></b>我的</span></a></li>
        </ul>
    </div>
</div>
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
