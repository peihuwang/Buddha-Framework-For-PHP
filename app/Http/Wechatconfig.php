<?php
/*
 *分享
 */
class Wechatconfig extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }



    public function getWechatAccessToken(){
        $type ='wechat';
        $son ='token';
        $WechatconfigObj = new Wechatconfig();
        $wetchatconfig=$WechatconfigObj->getSingleFiledValues(''," type='{$type}'
              and son='{$son}'
              ");
        $options = array(
            'encodingaeskey'=>$wetchatconfig['encodingaeskey'], //填写加密用的EncodingAESKey
            'appid'=>$wetchatconfig['appid'], //填写高级调用功能的app id
            'appsecret'=>$wetchatconfig['appsecret'] //填写高级调用功能的密钥
        );
        $weObj = new Buddha_Bridge_Wechat($options);

     return    $getData = $weObj->checkAuth();


    }

    public function getWechatUserInfo($openid){
        $type ='wechat';
        $son ='token';
        $WechatconfigObj = new Wechatconfig();
        $wetchatconfig=$WechatconfigObj->getSingleFiledValues(''," type='{$type}'
              and son='{$son}'
              ");
        $options = array(
            'encodingaeskey'=>$wetchatconfig['encodingaeskey'], //填写加密用的EncodingAESKey
            'appid'=>$wetchatconfig['appid'], //填写高级调用功能的app id
            'appsecret'=>$wetchatconfig['appsecret'] //填写高级调用功能的密钥
        );
        $weObj = new Buddha_Bridge_Wechat($options);
       return  $weObj->getUserInfo($openid);
    }

    public function getJsSign($share_title,$share_desc,$ahare_link,$share_imgUrl){
        $type ='wechat';
        $son ='token';
        $WechatconfigObj = new Wechatconfig();
        $wetchatconfig=$WechatconfigObj->getSingleFiledValues(''," type='{$type}'
              and son='{$son}'
              ");
        $options = array(
            'encodingaeskey'=>$wetchatconfig['encodingaeskey'], //填写加密用的EncodingAESKey
            'appid'=>$wetchatconfig['appid'], //填写高级调用功能的app id
            'appsecret'=>$wetchatconfig['appsecret'] //填写高级调用功能的密钥
        );
        $weObj = new Buddha_Bridge_Wechat($options);
        $getJsSignData = $weObj->getJsSign();
        $host = Buddha::$buddha_array['host'];
        $getJsSignData['share_title'] =$share_title;

        if($share_desc==''){
            $share_desc="请点击{$share_title}详情查看";
        }
        $getJsSignData['share_desc'] =$share_desc;
        $getJsSignData['ahare_link'] =$host.$ahare_link;

        if($share_imgUrl==''){
            $share_imgUrl="style/images/logo.png";
        }
        $getJsSignData['share_imgUrl'] =$host.$share_imgUrl;
        $this->smarty->assign('signPackage', $getJsSignData);
    }
}