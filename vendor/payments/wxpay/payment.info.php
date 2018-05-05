<?php

return array(
    'code' => 'wxpay',
    'name' => getLang('wxpay'),
    'desc' => getLang('wxpay_desc'),
    'is_online' => '1',
    'author' => 'HuoSuKe TEAM',
    'website' => 'https://mp.weixin.qq.com/cgi-bin/loginpag',
    'version' => '1.0',
    'currency' => getLang('wxpay_currency'),
    'config' => array(
        'wxpay_account' => array(
            'text' => getLang('wxpay_account'),
            'desc' => getLang('wxpay_account_desc'),
            'type' => 'text',
        ),

        'wxpay_app_secret' => array(
            'text' => getLang('wxpay_app_secret'),
            'desc' => getLang('wxpay_app_secret_desc'),
            'type' => 'text',
        ),

        'wxpay_mchid' => array(
            'text' => getLang('wxpay_mchid'),
            'desc' => getLang('wxpay_mchid_desc'),
            'type' => 'text',
        ),

        'wxpay_mchid_key' => array(
            'text' => getLang('wxpay_mchid_key'),
            'desc' => getLang('wxpay_mchid_key_desc'),
            'type' => 'text',
        ),

        'wxpay_returnurl' => array(
            'text' => getLang('wxpay_returnurl'),
            'desc' => getLang('wxpay_returnurl_desc'),
            'type' => 'text',
        ),

        'wxpay_successurl' => array(
            'text' => getLang('wxpay_successurl'),
            'desc' => getLang('wxpay_successurl_desc'),
            'type' => 'text',
        ),


    ),
);

?>