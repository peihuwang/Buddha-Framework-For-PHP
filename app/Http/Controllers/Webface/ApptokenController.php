<?php

/**
 * Class ApptokenController
 */
class ApptokenController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function getToken()
    {

        /*     http://huiyou.com/webface/?Services=appToken.gettoken&ip=127.0.0.1&starttime=1480591454&appvalue=webface&key=LetMeToTest&sign=19935a99cfc5a04872ff5dd7ca64a0f3
             Array ( [0] => LetMeToTest [1] => webface [2] => 127.0.0.1 [3] => 1480591454 )

            sige= md5(LetMeToTestwebface127.0.0.11480591454)=19935a99cfc5a04872ff5dd7ca64a0f3
            sige= 把appvalue的值 拼接ip的值 拼接key的值 拼接starttime的值 得到的最终字符串再进行md5加密 得到的字符串就是签名sige的值

             access_token =b88f9603be8be14e66a2b204fa15e7f5
             http://huiyou.com/webface/?Services=apptoken.getToken&ip=127.0.0.1&starttime=1480591454&appvalue=wphwebfacetest&key=wphtest&sign=41a6efb906c2e3f4600228944c00b021
          */
        $ip = Buddha_Http_Input::getParameter('ip');
        $starttime = Buddha_Http_Input::getParameter('starttime');
        $appvalue = Buddha_Http_Input::getParameter('appvalue');
        $appkey = Buddha_Http_Input::getParameter('appkey');
        $sign = Buddha_Http_Input::getParameter('sign');

        if (Buddha_Http_Input::checkParameter(array('ip', 'starttime', 'appvalue', 'appkey', 'sign'))) {
            Buddha_Http_Output::makeWebfaceJson(NULL, '/webface/?Services=' . $_REQUEST['Services'], $errcode = 444444, $errmsg = '必填信息不全', $other = '0');
        }
        $signarr = array('ip'=>$ip,'starttime'=>$starttime,'appvalue'=>$appvalue,'appkey'=>$appkey);
        ksort($signarr);
        $signarr = array_values($signarr);

         $mysign = $signarr[0].$signarr[1].$signarr[2].$signarr[3];

         $birth_sign= md5($mysign);

        if($birth_sign==$sign){
            $webface_access_token= md5(Buddha_Atom_String::getRandom(32));
            $endtime=$starttime+24*3600;
            $jsondata['webface_access_token'] =$webface_access_token;
            $jsondata['expires_in'] =43200;


            $ApptokenObj = new Apptoken();
            $visit_count = $ApptokenObj->accessEdit($ip,$appkey,$starttime,$endtime,$webface_access_token);
            $jsondata['visit_count'] =$visit_count;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'get token');
        }else{
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444004,'签名错误');
        }


    }


    

}