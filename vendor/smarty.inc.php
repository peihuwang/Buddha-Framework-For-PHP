<?php
/**
 * @version $Id: smarty.inc.php v1.0 $
 * @package HuoSuKe
 * @copyright Copyright (C) 2007 - 2020 veryceo.com. All Rights Reserved.
 * @license huosuke is commercial software and use is subject to license terms
 */
if (!defined('IN_HUOSUKE'))
{
	exit('Access Denied!');
}
require_once(PATH_ROOT.'includes/smarty/Smarty.class.php');
//设定模版目录
$templates_dir = PATH_ROOT.'templates/';
$compile_dir = PATH_ROOT.'compiles/compile_c/';
$cache_dir = PATH_ROOT.'compiles/cache/';
$config_dir = PATH_ROOT.'compiles/configs/';
$dir = TPL_DIR;
$smarty = new Smarty;									//实例化smarty对象
$smarty->debugging = FALSE;
$smarty->allow_php_tag = FALSE;                         //php 标签
$smarty->template_dir = $templates_dir.$dir;			//模版
$smarty->compile_dir = $compile_dir.$dir;				//编译
$smarty->config_dir  = $config_dir;
$smarty->cache_dir = $cache_dir.$dir;					//缓存
$smarty->cache_lifetime = 0;							//缓存时间
$smarty->caching = false;
$smarty->left_delimiter = '{#';
$smarty->right_delimiter = '#}';