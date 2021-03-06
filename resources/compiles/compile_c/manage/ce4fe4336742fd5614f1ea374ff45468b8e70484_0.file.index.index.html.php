<?php
/* Smarty version 3.1.30, created on 2018-01-14 09:10:23
  from "/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/index.index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5a5aadffc90d01_76711275',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ce4fe4336742fd5614f1ea374ff45468b8e70484' => 
    array (
      0 => '/Users/mac/workspace/web/localhost.com/resources/views/templates/manage/index.index.html',
      1 => 1503729465,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5a5aadffc90d01_76711275 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>管理系统</title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/font-awesome.min.css" rel="stylesheet">
<link href="css/animate.min.css" rel="stylesheet">
<link href="css/style.min.css" rel="stylesheet">
<?php echo '<script'; ?>
>
if(window.frameElement){
	parent.location.href = window.location.href;
}
<?php echo '</script'; ?>
>
</head>
<body  class="fixed-sidebar full-height-layout">
<div id="wrapper"> 
  <!--左侧导航开始--> 
  <!--左侧导航开始-->
  <nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
      <ul class="nav" id="side-menu">
        <div class="nav-close"><img src="images/logofu.png"> </div>
        <li class="nav-header">
          <div class="profile-element"> <span><img alt="image" class="img-circle" src="images/leftlogo.png"  /></span> </div>
          <div class="logo-element">Brain</div>
        </li>
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['menu']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
        <li act="<?php echo $_smarty_tpl->tpl_vars['item']->value['services'];?>
"><a href="#<?php echo $_smarty_tpl->tpl_vars['item']->value['services'];?>
"  calss="J_menuItem"><i><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</i><span class="nav-label"><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</span><span class="fa arrow"></span></a>
          <?php if ($_smarty_tpl->tpl_vars['item']->value['child']) {?>
          <ul class="nav nav-second-level">
            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['item']->value['child'], 'itemnoe');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['itemnoe']->value) {
?>
            <li><a id="leftmenu_<?php echo $_smarty_tpl->tpl_vars['itemnoe']->value['id'];?>
" class="J_menuItem" href="index.php?a=<?php echo $_smarty_tpl->tpl_vars['itemnoe']->value['operator'];?>
&c=<?php echo $_smarty_tpl->tpl_vars['itemnoe']->value['services'];?>
"><?php echo $_smarty_tpl->tpl_vars['itemnoe']->value['name'];?>
</a></li>
            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

            </ul>
         <?php }?>
        </li>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

      </ul>
    </div>
  </nav>
  <!--导航END-->
  <div id="page-wrapper" class="gray-bg dashbard-1">
    <div class="row border-bottom">
      <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header"><a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="javascript:;"><i class="fa fa-bars"></i> </a> </div>
        <ul class="nav navbar-top-links navbar-right">
          <li class="hidden-xs"><a href="index.php?a=index&c=index" class="J_menuItem"><i class="fa fa-home"></i>系统首页</a></li>
          <li class="hidden-xs"><a href="index.php?a=edit&c=config" class="J_menuItem"><i class="fa fa-gear"></i>基本设置</a></li>
          <li class="hidden-xs"><a href="index.php?a=more&c=manager" class="J_menuItem"><i class="fa fa-users"></i>用户</a></li>
          <li class="hidden-xs"><a href="index.php?a=more&c=menu" class="J_menuItem"><i class="fa fa-desktop"></i>菜单设置</a></li>
          <li class="dropdown hidden-xs"><a class="right-sidebar-toggle" aria-expanded="false"><i class="fa fa-tasks"></i>登录会员</a></li>
        </ul>
      </nav>
    </div>
    <!--右侧头部-->
    <div class="row content-tabs">
      <button class="roll-nav roll-left J_tabLeft"><i class="fa fa-backward"></i> </button>
      <nav class="page-tabs J_menuTabs">
        <div class="page-tabs-content"> <a href="javascript:;" class="active J_menuTab" data-id="main.html">系统首页</a> </div>
      </nav>
      <button class="roll-nav roll-right J_tabRight"><i class="fa fa-forward"></i></button>
      <div class="btn-group roll-nav roll-right">
        <button class="dropdown J_tabClose" data-toggle="dropdown">关闭操作<span class="caret"></span></button>
        <ul role="menu" class="dropdown-menu dropdown-menu-right">
          <li class="J_tabShowActive"><a>定位当前选项卡</a> </li>
          <li class="divider"></li>
          <li class="J_tabCloseAll"><a>关闭全部选项卡</a> </li>
          <li class="J_tabCloseOther"><a>关闭其他选项卡</a> </li>
        </ul>
      </div>
      <a href="index.php?a=logout&c=index" class="roll-nav roll-right J_tabExit"><i class="fa fa fa-sign-out"></i> 退出</a> </div>
    <!--右侧头部END-->
    <div class="row J_mainContent" id="content-main">
      <iframe class="J_iframe" name="iframe0" width="100%" height="100%" src="index.php?a=main&c=index" frameborder="0" data-id="main.html" seamless></iframe>
    </div>
    <div class="footer">
      <div class="pull-right"><?php echo $_smarty_tpl->tpl_vars['hsk_siteCopyRight']->value;?>
 </div>
    </div>
  </div>
  <!--右侧部分结束--> 
  <div id="right-sidebar">
    <div class="skin-setttings">
      <div class="title">当前登录信息</div>
        <div style="padding: 5px;">管理员：<?php echo $_smarty_tpl->tpl_vars['member']->value['username'];?>
</div>
        <div style="padding: 5px;">昵称：<?php echo $_smarty_tpl->tpl_vars['member']->value['nickname'];?>
</div>
        <div style="padding: 5px;">登录时间：<?php echo $_smarty_tpl->tpl_vars['member']->value['logintime'];?>
</div>
    </div>
<div class="text-center"><a href="index.php?a=logout&c=index"  class="btn btn-danger J_menuItem">退出</a> <a href="index.php?a=edit&c=member"  class="btn btn-danger J_menuItem">修改密码</a></div>

</div>
<?php echo '<script'; ?>
 src="js/jquery.min.js?v=2.1.4"><?php echo '</script'; ?>
> 
<?php echo '<script'; ?>
 src="js/bootstrap.min.js?v=3.3.6"><?php echo '</script'; ?>
> 
<?php echo '<script'; ?>
 src="js/plugins/metisMenu/jquery.metisMenu.js"><?php echo '</script'; ?>
> 
<?php echo '<script'; ?>
 src="js/plugins/slimscroll/jquery.slimscroll.min.js"><?php echo '</script'; ?>
> 
<?php echo '<script'; ?>
 src="js/hplus.min.js?v=4.1.0"><?php echo '</script'; ?>
> 
<?php echo '<script'; ?>
 src="js/contabs.min.js"><?php echo '</script'; ?>
> 
<?php echo '<script'; ?>
 src="js/plugins/pace/pace.min.js"><?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
