<?php

return array(
    'code' => 'alipay',
    'name' => getLang('alipay'),
    'desc' => getLang('alipay_desc'),
    'is_online' => '1',
    'author' => '建营 TEAM',
    'website' => 'http://www.alipay.com',
    'version' => '1.0',
    'currency' => getLang('alipay_currency'),
    'config' => array(
        'alipay_account' => array(        //账号
            'text' => getLang('alipay_account'),
            'desc' => getLang('alipay_account_desc'),
            'type' => 'text',
        ),
        'alipay_key' => array(        //密钥
            'text' => getLang('alipay_key'),
            'desc' => getLang('alipay_key_desc'),
            'type' => 'text',
        ),
        'alipay_partner' => array(        //合作者身份ID
            'text' => getLang('alipay_partner'),
            'type' => 'text',
        ),
        'alipay_service' => array(         //服务类型
            'text' => getLang('alipay_service'),
            'desc' => getLang('alipay_service_desc'),
            'type' => 'select',
            'items' => array(
                'trade_create_by_buyer' => getLang('trade_create_by_buyer'),
                'create_partner_trade_by_buyer' => getLang('create_partner_trade_by_buyer'),
                'create_direct_pay_by_user' => getLang('create_direct_pay_by_user'),
            ),
        ),
    ),
);

?>