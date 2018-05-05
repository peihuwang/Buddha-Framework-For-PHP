<?php
/* Smarty version 3.1.30, created on 2017-12-21 11:22:49
  from "/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/public.recommend.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a3b29096bbdf1_00051077',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3c5f59b762de7729e2fccbd4953fc014fc809843' => 
    array (
      0 => '/Users/mac/workspace/web/bendishangjia.com/resources/views/templates/front/public.recommend.html',
      1 => 1513738058,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a3b29096bbdf1_00051077 (Smarty_Internal_Template $_smarty_tpl) {
?>
<style>
    .recommend_header{width:100%;display:inline-block;margin-top: 20px;border-top: 35px solid  #F0F0F0;padding-top: 15px;}
    .header_me{width: 150px;float: left;font-size: 18px;margin-left: 10px;}
    .header_more{width: 30%;float: right;color: #787878;}
    .recommend{
        background-color: #F0F0F0;
        /*border: 1px solid red;*/
        /*height: 325px;width: 100%;*/
    }
    .recommend_menu {
        font-size: 12px;
        font-weight: bolder;
        width: 100%;
        text-align: center;
        margin: 2px auto;
        /*background-color: #999999;*/
        padding-top: 8px;
    }
    .recommend_menu li{
        list-style-image: none;
        list-style-type: none;

        border-right-width: 1px;
        /*border-right-style: solid;*/
        /*border-right-color: #000000;*/
        float: left;
        margin-bottom: 8px;
        margin-left: 5px;
        text-align: center;
        height: 150px;
        width: 30%;
        background-color:  #FFFFFF;
        border: 1px solid  #F0F0F0;
        display: block;
        padding: 0.1px;
    }
    .recommend_menu li a{
        color: #FFFFFF;
        text-decoration: none;
        margin: 0px;
        padding-top: 8px;
        display: block; /* 作为一个块 */
        padding-right: 50px; /* 设置块的属性 */
        padding-bottom: 8px;
        /*padding-left: 17px;*/
        width: 100%;
        zoom: 1;
    }
    /*.recommend_menu li a span{*/

          /*}*/
    .recommend_menu li a img{
        width: 80px;height: 80px;float: left;margin-left: 15px;
    }
    .recommend_menu li a span{
        /*width: 20px;float: left;margin-left: 2px;*/
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 95%;
        font-size: 1.2em;
        line-height: 1.5;
        display: block;
        width:110px;
    }
    .recommend_menu li a:hover{
        background-color: #0099CC;
    }
</style>

<!--↓↓↓↓↓↓↓↓↓↓↓ 需求 推荐 、、、↓↓↓↓↓↓↓↓↓↓↓-->
<?php if (count($_smarty_tpl->tpl_vars['recommend']->value['demand']['more']) > 0) {?>
<div class="recommend_header">
    <span class="header_me">我的<?php echo $_smarty_tpl->tpl_vars['recommend']->value['demand']['headettitle'];?>
</span>
    <a class="header_more" href="<?php echo $_smarty_tpl->tpl_vars['recommend']->value['demand']['url'];?>
">更多>></a> </div>
<div class="recommend" style="background-color: #F0F0F0;">
    <ul class="recommend_menu">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['recommend']->value['demand']['more'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <li style="">
            <a href="/index.php?a=info&c=demand&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['demand_id'];?>
">
                <img src="<?php echo $_smarty_tpl->tpl_vars['item']->value['img'];?>
">
                <span style=" color: black;"><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['price'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['brief'];?>
</span>
            </a>
        </li>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

    </ul>
</div>
<br/>
<br/>
<?php }?>

<!--↑↑↑↑↑↑↑↑↑↑ 需求 推荐 、、、↑↑↑↑↑↑↑↑↑↑-->


<!--↓↓↓↓↓↓↓↓↓↓↓ 供应 推荐 ↓↓↓↓↓↓↓↓↓↓↓-->
<?php if (count($_smarty_tpl->tpl_vars['recommend']->value['supply']['more']) > 0) {?>
<div class="recommend_header">
    <span class="header_me">我的<?php echo $_smarty_tpl->tpl_vars['recommend']->value['supply']['headettitle'];?>
</span>
    <a class="header_more" href="<?php echo $_smarty_tpl->tpl_vars['recommend']->value['supply']['url'];?>
">更多>></a> </div>
<div class="recommend" >
    <ul class="recommend_menu">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['recommend']->value['supply']['more'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <li>
            <a href="/index.php?a=info&c=supply&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['supply_id'];?>
">
                <img src="<?php echo $_smarty_tpl->tpl_vars['item']->value['img'];?>
">
                <span style=" color: black;"><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['price'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['brief'];?>
</span>
            </a>
        </li>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

    </ul>
</div>
<br/>
<br/>
<?php }?>

<!--↑↑↑↑↑↑↑↑↑↑ 供应 推荐 ↑↑↑↑↑↑↑↑↑↑-->




<!--↓↓↓↓↓↓↓↓↓↓↓ 活动 推荐 ↓↓↓↓↓↓↓↓↓↓↓-->
<?php if (count($_smarty_tpl->tpl_vars['recommend']->value['activity']['more']) > 0) {?>
<div class="recommend_header">
    <span class="header_me">我的<?php echo $_smarty_tpl->tpl_vars['recommend']->value['activity']['headettitle'];?>
</span>
    <a class="header_more" href="<?php echo $_smarty_tpl->tpl_vars['recommend']->value['activity']['url'];?>
">更多>></a> </div>
<div class="recommend" >
    <ul class="recommend_menu">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['recommend']->value['activity']['more'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <li>
            <a href="/index.php?a=<?php if ($_smarty_tpl->tpl_vars['item']->value['type'] == 1 || $_smarty_tpl->tpl_vars['item']->value['type'] == 2) {?>mylist<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['type'] == 3) {?>vodelist<?php }?>&c=activity&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['activity_id'];?>
">
                <img src="<?php echo $_smarty_tpl->tpl_vars['item']->value['img'];?>
">
                <span style=" color: black;"><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['number'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['brief'];?>
</span>
            </a>
        </li>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

    </ul>
</div>
<br/>
<br/>
<?php }?>
<!--↑↑↑↑↑↑↑↑↑↑ 活动 推荐 ↑↑↑↑↑↑↑↑↑↑-->


<!--↓↓↓↓↓↓↓↓↓↓↓ 1分购 推荐 ↓↓↓↓↓↓↓↓↓↓↓-->
<?php if (count($_smarty_tpl->tpl_vars['recommend']->value['heartpro']['more']) > 0) {?>
<div class="recommend_header">
    <span class="header_me">我的<?php echo $_smarty_tpl->tpl_vars['recommend']->value['heartpro']['headettitle'];?>
</span>
    <a class="header_more" href="<?php echo $_smarty_tpl->tpl_vars['recommend']->value['heartpro']['url'];?>
">更多>></a> </div>
<div class="recommend" >
    <ul class="recommend_menu">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['recommend']->value['heartpro']['more'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <li>
            <a href="/index.php?a=info&c=heartpro&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['heartpro_id'];?>
">
                <img src="<?php echo $_smarty_tpl->tpl_vars['item']->value['img'];?>
">
                <span style=" color: black;"><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['price'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['brief'];?>
</span>
            </a>
        </li>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

    </ul>
</div>
<br/>
<br/>
<?php }?>
<!--↑↑↑↑↑↑↑↑↑↑ 1分购 推荐 ↑↑↑↑↑↑↑↑↑↑-->




<!--↓↓↓↓↓↓↓↓↓↓↓ 租赁 推荐 ↓↓↓↓↓↓↓↓↓↓↓-->
<?php if (count($_smarty_tpl->tpl_vars['recommend']->value['lease']['more']) > 0) {?>
<div class="recommend_header">
    <span class="header_me">我的<?php echo $_smarty_tpl->tpl_vars['recommend']->value['lease']['headettitle'];?>
</span>
    <a class="header_more" href="<?php echo $_smarty_tpl->tpl_vars['recommend']->value['lease']['url'];?>
">更多>></a> </div>
<div class="recommend" >
    <ul class="recommend_menu">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['recommend']->value['lease']['more'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <li>
            <a  href="/index.php?a=info&c=lease&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['lease_id'];?>
">
                <img src="<?php echo $_smarty_tpl->tpl_vars['item']->value['img'];?>
">
                <span style=" color: black;"><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['price'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['brief'];?>
</span>
            </a>
        </li>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

    </ul>
</div>
<br/>
<br/>
<?php }?>
<!--↑↑↑↑↑↑↑↑↑↑ 租赁 推荐 ↑↑↑↑↑↑↑↑↑↑-->



<!--↓↓↓↓↓↓↓↓↓↓↓ 传单 推荐 ↓↓↓↓↓↓↓↓↓↓↓-->
<?php if (count($_smarty_tpl->tpl_vars['recommend']->value['singleinformation']['more']) > 0) {?>
<div class="recommend_header">
    <span class="header_me">我的<?php echo $_smarty_tpl->tpl_vars['recommend']->value['singleinformation']['headettitle'];?>
</span>
    <a class="header_more" href="<?php echo $_smarty_tpl->tpl_vars['recommend']->value['singleinformation']['url'];?>
">更多>></a> </div>
<div class="recommend" >
    <ul class="recommend_menu">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['recommend']->value['singleinformation']['more'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <li>
            <a href="/index.php?a=mylist&c=singleinformation&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['singleinformation_id'];?>
" >
                <img src="<?php echo $_smarty_tpl->tpl_vars['item']->value['img'];?>
">
                <span style=" color: black;"><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['number'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['brief'];?>
</span>
            </a>
        </li>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

    </ul>
</div>
<br/>
<br/>
<?php }?>
<!--↑↑↑↑↑↑↑↑↑↑ 传单 推荐 ↑↑↑↑↑↑↑↑↑↑-->


<!--↓↓↓↓↓↓↓↓↓↓↓ 招聘 推荐 ↓↓↓↓↓↓↓↓↓↓↓-->
<?php if (count($_smarty_tpl->tpl_vars['recommend']->value['recruit']['more']) > 0) {?>
<div class="recommend_header">
    <span class="header_me">我的<?php echo $_smarty_tpl->tpl_vars['recommend']->value['recruit']['headettitle'];?>
</span>
    <a class="header_more" href="<?php echo $_smarty_tpl->tpl_vars['recommend']->value['recruit']['url'];?>
">更多>></a> </div>
<div class="recommend" >
    <ul class="recommend_menu">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['recommend']->value['recruit']['more'], 'item', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['item']->value) {
?>
        <li>
            <a href="/index.php?a=info&c=recruit&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['recruit'];?>
">
                <img src="<?php echo $_smarty_tpl->tpl_vars['item']->value['img'];?>
">
                <span style=" color: black;"><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['price'];?>
</span>
                <span style=" color: #787878;font-size: 12px;"><?php echo $_smarty_tpl->tpl_vars['item']->value['brief'];?>
</span>
            </a>
        </li>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

    </ul>
</div>
<br/>
<br/>
<?php }?>
<!--↑↑↑↑↑↑↑↑↑↑ 招聘 推荐 ↑↑↑↑↑↑↑↑↑↑-->
<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><?php }
}
