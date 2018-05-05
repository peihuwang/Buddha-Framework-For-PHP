<?php
header('content-type:application:json;charset=utf8');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:GET,POST,OPTIONS');
//header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Headers:x-requested-with,content-type');

define('TPL_DIR', 'webface');
require_once  '../bootstrap/Init.php';