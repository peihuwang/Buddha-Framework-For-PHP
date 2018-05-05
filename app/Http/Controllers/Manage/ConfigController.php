<?php

/**
 * Class ConfigController
 */
class ConfigController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function edit()
    {
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c . '.' . __FUNCTION__);
        $cdesc = array(
            'hsk_smsAccount' => '短信发送账号',
            'hsk_smsPassword' => '短信发送密码',
            'hsk_smsForwordAddress' => '短信发送接口地址',
            'hsk_smtphost' => '邮件发送主机地址',
            'hsk_smtpuser' => '邮件发送接口账号',
            'hsk_smtppw' => '邮件发送接口密码',
            'hsk_smtpport' => '邮件发送SMTP端口',
            'hsk_siteCopyRight' => '底部版权',
            'hsk_is_shop_needverify' => '0表示免费，1是收费',
            'hsk_is_shop_verify_zl' => '0表示不上传，1表示必须上传'

        );
        $configsobj = new Config();
        $configs = $configsobj->getConfig();
        if (count($configs) == 0) {
            $configs['hsk_eastTicketForwordAddress'] = 0;
            $configs['hsk_smsAccount'] = 0;
            $configs['hsk_smsPassword'] = 0;
            $configs['hsk_smsForwordAddress'] = 0;
            $configs['hsk_smtphost'] = 0;
            $configs['hsk_smtpuser'] = 0;
            $configs['hsk_smtppw'] = 0;
            $configs['hsk_smtpport'] = 0;
            $configs['hsk_siteCopyRight'] = 0;
            $configs['hsk_is_shop_needverify'] = 1;
            $configs['hsk_is_shop_verify_zl'] = 1;

        }


        if ($_POST) {

            $config = Buddha_Http_Input::getParameter('config');


            $configs ['hsk_smsAccount'] = trim($config ['hsk_smsAccount']);
            $configs ['hsk_smsPassword'] = trim($config ['hsk_smsPassword']);
            $configs ['hsk_smsForwordAddress'] = trim($config ['hsk_smsForwordAddress']);

            $configs ['hsk_smtphost'] = trim($config ['hsk_smtphost']);
            $configs ['hsk_smtpuser'] = trim($config ['hsk_smtpuser']);
            $configs ['hsk_smtppw'] = trim($config ['hsk_smtppw']);
            $configs ['hsk_smtpport'] = trim($config ['hsk_smtpport']);

            $configs ['hsk_siteCopyRight'] = trim($config ['hsk_siteCopyRight']);
            $configs ['hsk_is_shop_needverify'] = trim($config ['hsk_is_shop_needverify']);
            $configs ['hsk_is_shop_verify_zl'] = trim($config ['hsk_is_shop_verify_zl']);

            $configsobj->updateConfig($configs, $cdesc);
            $configsobj->cacheConfigs();


        }

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->assign('config', $configs);
        $this->smarty->display($TPL_URL . '.html');
    }

}