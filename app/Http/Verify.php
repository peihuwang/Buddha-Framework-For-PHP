<?php
class Verify extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }


    public function emailSend($email,$emailcode){
        $subject= "本地商家网-{$email}邮箱绑定";
        $body="您的会员注册验证码是".$emailcode."此验证码120秒内有效!【新会友】";
        Buddha_Http_Send::sendMail($email,$subject,$body);
        return 1;

    }

    public function smsSend($mobile,$mobilecode){

        $ch = curl_init();
        $post_data = array(
        "account" => "sdk_haibojs",
        "password" => "dkghh46",
        "destmobile" => $mobile,
        "msgText" => "您的验证码是 ". $mobilecode . " 此验证码只用于绑定、修改手机、修改密码验证，请勿将此验证码发给任何号码及其他人【本地商家网】",
        "sendDateTime" => ""
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);  
        curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $post_data = http_build_query($post_data);
        //echo $post_data;
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_URL, 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/http/sendBatchMessage');
        if(curl_exec($ch)>0){
            return true;
        }else{
            return false;
        }


    }
    public function hadEmailPass($email,$emailcode){
        $this->updateRecords(array('buddhastatus'=>1)," email='{$email}' and code='{$emailcode}' ");

    }

    public function hadPass($mobile,$mobilecode){
           $this->updateRecords(array('buddhastatus'=>1)," mobile='{$mobile}' and code='{$mobilecode}' ");

    }

    public function hasMobileCode($mobile,$mobilecode){
        $nowtime = time();
        $ip = Buddha_Explorer_Network::getIp();
        $createtime= $nowtime-3600;

        $num = $this->countRecords("
               buddhastatus=0  and
              mobile='{$mobile}' and
              code='{$mobilecode}' and
              createtime>=$createtime  ");

        return  $num;
    }

    public function hasEmailCode($email,$emailcode){
        $nowtime = time();
        $ip = Buddha_Explorer_Network::getIp();
        $createtime= $nowtime-3600;
        $num = $this->countRecords("ip='{$ip}' and
               buddhastatus=0  and
              email='{$email}' and
              code='{$emailcode}' and
              createtime>=$createtime ");

        return  $num;
    }



}