<?php
/* Smarty version 3.1.30, created on 2017-12-21 11:23:24
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/heartpro.vodeprize.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a3b292c7910e3_33421938',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '316efa44aa5f5493be2ffa41aaa1c1ae943133eb' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/heartpro.vodeprize.html',
      1 => 1513740396,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:../front/public.footernew.html' => 1,
  ),
),false)) {
function content_5a3b292c7910e3_33421938 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>本地商家</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"  />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no"/>
    <meta name="applicable-device" content="mobile"/>
    <link href="/style/css_two/reset.css" rel="stylesheet" type="text/css" />
    <link href="/style/css_two/style.css" rel="stylesheet" type="text/css" />
    <link href="/style/css_two/swiper.min.css" rel="stylesheet" type="text/css" />
    <?php echo '<script'; ?>
 type="text/javascript" src="/style/js_two/jquery-3.1.1.min.js"><?php echo '</script'; ?>
>
</head>
<body>

<img class="w" src=" <?php echo $_smarty_tpl->tpl_vars['Act']->value['small'];?>
" alt="" style="height:200px; ">

<!--活动介绍-->
<div class="pl10 pr10 pt10 pb10">
    <h4 class="cy f16 mb10">1分购详情</h4>
    <div class="brt pl10 pr10 pb10 pt10 content">
        <?php echo $_smarty_tpl->tpl_vars['Act']->value['desc'];?>

    </div>
    <div style="width:100%; ">
        <img src="<?php echo $_smarty_tpl->tpl_vars['Act']->value['codeimg'];?>
"  style="width:100%; "/>
    </div>
</div>


<br/><br/><br/><br/><br/><br/>

<!--奖品设置-->
</body>
<?php $_smarty_tpl->_subTemplateRender("file:../front/public.footernew.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>



<?php echo '<script'; ?>
>
    //给富文本框的图片添加样式
    $('.content img').each(function(){
        $(this).addClass('w');
    });
<?php echo '</script'; ?>
>
</html><?php }
}
