<?php

return array(
    'database'=>array(
    // 必须配置项
    'database_type' => 'mysql',
    'database_name' => 'buddha',
    'server' => 'localhost',
    'username' => 'buddha',
    'password' => 'buddha',
    'charset' => 'utf8',


    // 可选参数
    'port' => 3306,

    // 可选，定义表的前缀
    'prefix' => 'b2b_',

    // 连接参数扩展, 更多参考 http://www.php.net/manual/en/pdo.setattribute.php
    'option' => array(
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    )
    ),


    'smarty'=>array(
        'debugging' => FALSE,
        'template_dir' => PATH_ROOT.'resources/views/templates/'.TPL_DIR,
        'compile_dir' => PATH_ROOT.'resources/compiles/compile_c/'.TPL_DIR,
        'config_dir' =>  PATH_ROOT.'resources/compiles/configs/',
        'cache_dir' =>PATH_ROOT.'resources/compiles/cache/'.TPL_DIR,
        'cache_lifetime' => 0,
        'caching' => FALSE,
        'left_delimiter' => '{#',
        'right_delimiter' => '#}',
    ),

    'url'=>array(
        'URL_MODE' => '0',
        'VAR_CONTROLLER' => 'c',
        'VAR_ACTION' =>'a',
        'VAR_MODULE' =>  'm'
    ),

    'site'=>array(
        'www' => 'www.veryceo.com'
    ),

);