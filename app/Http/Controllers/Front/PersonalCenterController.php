<?php

/**
 * Class PersonalCenterController
 */
class PersonalCenterController extends Buddha_App_Action{//个人中心共用


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    function signin(){//会员签到
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserObj = new User();
        $SigninObj = new Signin();
        $time = strtotime(date("Y-m-d"));
        $signin = $SigninObj->getSingleFiledValues(array('id','signtimes'),"user_id='{$uid}'");
        $num = $SigninObj->countRecords("user_id='{$uid}' AND signdatetime='{$time}'");
        if(!$num){
            $data['user_id'] = $uid;
            $data['signstatus'] = 1;
            $data['signdatetime'] = $time;
            $data['signdatetimestr'] = date('Y-m-d H:i:s');
            //$data['signdatetimestr'] = $signin['signtimes'] + 1;
            if($signin){
                $insert_id = $SigninObj->updateRecords($data,"id='{$signin['id']}' AND user_id='{$uid}'");
            }else{
                $insert_id = $SigninObj->add($data);
            }
            if($insert_id){
                $integral['memercardpoint'] = $UserInfo['memercardpoint'] + 30;
                $UserObj->updateRecords($integral,"id='{$uid}'");
                $datas['isok'] = 1;
                $datas['info'] = '签到成功,积分已入账！';
                $datas['memercardpoint'] = $integral['memercardpoint'];
            }else{
                $datas['isok'] = 0;
                $datas['info'] = '服务器忙!';
            }
            
        }else{
            $datas['isok'] = 2;
            $datas['info'] = '您今天已签到!';
        }
        Buddha_Http_Output::makeJson($datas);
    }

    function verify(){//提现验证码验证
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $code = Buddha_Http_Input::getParameter('vcode');
        $nowtime = time();
        $mobile = $UserInfo['mobile'];

        $ip = Buddha_Explorer_Network::getIp();
        $createtime= $nowtime-3600;
        $VerifyObj = new Verify();
        $num = $VerifyObj->countRecords("ip='{$ip}' and mobile='{$mobile}' and code='{$code}' and createtime>=$createtime ");

        if($num){
            $DB_Verify = $VerifyObj->getSingleFiledValues(array('code'),"
           ip='{$ip}' and mobile='{$mobile}' and code='{$code}' order by id desc ");
        }else{
            $DB_Verify=0;
        }


        if($param_mobile){
            if($num and $DB_Verify and $DB_Verify['code']==$code){
                return 1;
            }else{
                return 0;
            }
        }
        if($num and $DB_Verify and $DB_Verify['code']==$code){
            $data['isok'] = 1;
        }else{
            $data['isok'] = 0;
        }
        Buddha_Http_Output::makeJson($data);
    }

    /**
     * 发送验证码（验证请求）
     */
    public function  verifyrequest(){
        $UserObj = new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $nowtime = time();
        $mobile = $UserInfo['mobile'];
        if(!$mobile){
            $datas['isok']='false';
            $datas['msg']='请您先绑定手机号在进行提现操作!';
            Buddha_Http_Output::makeJson($datas);
        }else{
           $data = array();
            $data['mobile'] =$mobile;
            $data['code'] = Buddha_Tool_String::getRand();
            $data['ip'] = Buddha_Explorer_Network::getIp();
            $data['createtime'] =  $nowtime;
            $data['createtimestr'] = date('Y-m-d H:i:s',$nowtime);
            $data['regtime'] = $nowtime;
            $data['buddhastatus'] = 0;
            $data['isdel'] = 0;
            $VerifyObj = new Verify();

            $createtime= $nowtime-3600*24;
            $num1 = $VerifyObj->countRecords("ip='{$data['ip']}'  and createtime>=$createtime ");
            $num2= $VerifyObj->countRecords("mobile='{$data['mobile']}'   and createtime>=$createtime ");
            if($num1<50 and $num2<10 and $data['mobile']) {  //一个IP 一天注册人小于50人 同一个手机号最多一天让发10次验证码
               //$snedflag= $VerifyObj->smsSend($mobile,$data['code']);

                $ch = curl_init();
                $post_data = array(
                "account" => "sdk_haibojs",
                "password" => "dkghh46",
                "destmobile" => $mobile,
                "msgText" => "您的验证码是 ". $data['code'] . " 此验证码只用于您的提现操作，请勿将此验证码发给任何号码及其他人【本地商家网】",
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

                $datas = array();
                if(curl_exec($ch)>0){
                    $datas['isok']='true';
                    $datas['msg']='发送成功！';
                    $VerifyObj->add($data);
                }else{
                    $datas['isok']='false';
                    $datas['msg']='发送失败！';
                }
                Buddha_Http_Output::makeJson($datas);
            }else{
                $datas['isok']='false';
                $datas['msg']='24小时内最多发10次验证码';
                Buddha_Http_Output::makeJson($datas);
            } 
        }
        
    }

}