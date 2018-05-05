<?php

/**
 * Class AgentController
 */
class AgentController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $RegionObj=new Region();
        $agent_area= $RegionObj->getAllArrayAddressByLever($UserInfo['level3']);

        $UserObj = new User();
        $CommonObj = new Common();
        $rearrs = $CommonObj->getWeChatUserInformation();
        $times = time();
        if($uid && $rearrs){
            if(!$UserInfo['nickname']){
                $data['nickname'] = $rearrs['nickname'];
                $aas = 1;
            }
            if(!$UserInfo['logo']){
                $data['logo'] = $rearrs['headimgurl'];
                $aas = 1;
            }
            if($aas == 1){
                $UserObj->updateRecords($data,"id='{$uid}'");
            }
        }
        if($agent_area){
            $areadder='';
            foreach($agent_area as $k=> $v) {
                if ($k != 0)
                    $areadder.=$v['name'].' > ';
            }
            $UserInfo['areadder']=Buddha_Atom_String::toDeleteTailCharacter($areadder);
        }
        $where = "u_id={$uid} and sure=1";
        $NewsObj=new News();
        $num = $NewsObj->countRecords($where );
        if($UserInfo['nickname']){
            $UserInfo['realname'] = $UserInfo['nickname'];
        }
        if(stripos($UserInfo['logo'],"qlogo")){
            $sure = 1;
            $this->smarty->assign('sure',$sure);
        }
        $this->smarty->assign('num',$num);
        $this->smarty->assign('UserInfo',$UserInfo);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function info(){
      list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $RegionObj=new Region();
        $agent_area= $RegionObj->getAllArrayAddressByLever($UserInfo['level3']);
        if($agent_area){
            $areadder='';
            foreach($agent_area as $k=> $v) {
                if ($k != 0)
                    $areadder.=$v['name'].' > ';
            }
        }
        $UserInfo['areadder']=Buddha_Atom_String::toDeleteTailCharacter($areadder);
        $UserInfo['logo'] = Buddha_Atom_String::getUserLogo($UserInfo['logo'] );
        $this->smarty->assign('UserInfo',$UserInfo);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }



    public function logo(){
        $UserObj=new User();
        $GalleryObj=new Gallery();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $logo=Buddha_Http_Input::getParameter('logo');

        if(base64_encode(base64_decode($logo))){
            $imgurl= explode(',',$logo);

            @mkdir(PATH_ROOT."storage/user/logo/".$uid.'/'); // 如果不存在则创建
            $savePath ='storage/user/logo/'.$uid.'/';
            if(!file_exists($savePath)){
                @mkdir($savePath, 0777);
            }
            $base64_string = $imgurl[1];
            $output_file = date('ymdhis',time()) . rand(1000, 9990) . '.jpg';
            $filePath =PATH_ROOT.$savePath.$output_file;
            $GalleryObj->resolveImageForRotate($filePath,$base64_string);

            Buddha_Tool_File::thumbImage( $filePath, 640, 640, 'M_' );
            @unlink($filePath);
            @unlink(PATH_ROOT.$UserInfo['logo']);
            $logo = $savePath.'M_'.$output_file;
            $data=array();
            $data['logo']=$logo;
            $UserObj->edit($data,$uid);
            $data=array();
            if($UserObj){
                $data['isok']='true';
                $data['data']=$logo;
            }else{
                $data['isok']='false';
                $data['data']='头像获取失败..';
            }
            Buddha_Http_Output::makeJson($data);

        }
    }

    public function ajaxregion (){
        $UserObj=new User();
        $RegionObj=new Region();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $regionstr = Buddha_Http_Input::getParameter('regionstr');
        $level=explode(",", $regionstr);
        if(is_array($level) and count($level)<2){
            $data['isok']='false';
            $data['data']='区域必须选到区一级';
        }else{
            $datas=array();
            $datas['level0']=1;
            $datas['level1']=$level[0];
            $datas['level2']=$level[1];
            $datas['level3']=$level[2];
            $UserObj->edit($datas,$uid);
            if($UserObj){
                $agent_area= $RegionObj->getAllArrayAddressByLever($level[2]);
                if($agent_area){
                    $areadder='';
                    foreach($agent_area as $k=> $v) {
                        if ($k != 0)
                            $areadder.=$v['name'].' > ';
                    }
                    $adderr=Buddha_Atom_String::toDeleteTailCharacter($areadder);
                }
            }
            $data=array();
            if($UserObj){
                $data['isok']='true';
                $data['data']=$adderr;
            }else{
                $data['isok']='false';
                $data['data']='编辑失败';
            }
        }
        Buddha_Http_Output::makeJson($data);
    }

    public function arear(){
        $RegionObj=new Region();
        $fid = Buddha_Http_Input::getParameter('fid');
        $Db_arear= $RegionObj->getChildlist($fid);
        $datas = array();
        if($Db_arear){
            $datas['isok']='true';
            $datas['datas']=$Db_arear;
        }else{
            $datas['isok']='false';
            $datas['datas']='';
        }
        Buddha_Http_Output::makeJson($datas);
    }

    public function tel(){
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $tel = Buddha_Http_Input::getParameter('fid');
        $UserObj->edit(array('tel'=>$tel),$uid);
        $data=array();
        if($UserObj){
            $data['isok']='true';
            $data['data']=$tel;
        }else{
            $data['isok']='false';
            $data['data']='编辑失败';
        }
        Buddha_Http_Output::makeJson($data);
    }

    public function address(){
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $address = Buddha_Http_Input::getParameter('fid');
        $UserObj->edit(array('address'=>$address),$uid);
        $data=array();
        if($UserObj){
            $data['isok']='true';
            $data['data']=$address;
        }else{
            $data['isok']='false';
            $data['data']='编辑失败';
        }
        Buddha_Http_Output::makeJson($data);
    }

    public function gender(){
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $gender = Buddha_Http_Input::getParameter('fid');
        $UserObj->edit(array('gender'=>$gender),$uid);
        if($gender==0){
            $gender='保密';
        }elseif($gender==1){
            $gender='男';
        }else{
            $gender='女';
        }
        $data=array();
        if($UserObj){
            $data['isok']='true';
            $data['data']=$gender;
        }else{
            $data['isok']='false';
            $data['data']='编辑失败';
        }
        Buddha_Http_Output::makeJson($data);
    }

    public function editpassword(){
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $password = Buddha_Http_Input::getParameter('fid');
        $UserObj->edit(array('password'=>Buddha_Tool_Password::md5($password),'codes'=>$password),$uid);
        $data=array();
        if($UserObj){
            $data['isok']='true';
            $data['data']='';
        }else{
            $data['isok']='false';
            $data['data']='编辑失败';
        }
        Buddha_Http_Output::makeJson($data);
    }


    public function  verifyrequest(){
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $nowtime = time();
        $mobile = Buddha_Http_Input::getParameter('fid');
        $num=$UserObj->countRecords("isdel=0 and mobile='{$mobile}' and id='{$uid}'");
        $datas = array();
        if(!$num){
            $datas['isok']='false';
            Buddha_Http_Output::makeJson($datas);
        }


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
        $num2= $VerifyObj->countRecords("mobile='{$data['mobile']}'  and createtime>=$createtime");
        if($num1<50 and $num2<10 and $data['mobile']) {  //一个IP 一天注册人小于50人 同一个手机号最多一天让发10次验证码
            $snedflag= $VerifyObj->smsSend($mobile,$data['code']);
            $datas = array();
            if($snedflag){
                $datas['isok']='true';
                $VerifyObj->add($data);
            }else{
                $datas['isok']='false';
            }
        }else{
            $datas['isok']='false';
        }
        Buddha_Http_Output::makeJson($datas);
    }

    public function verify(){
        $nowtime = time();
        $mobile = Buddha_Http_Input::getParameter('Mobile');
        $code = Buddha_Http_Input::getParameter('Code');
        if($mobile==''){
            $mobile = Buddha_Http_Input::getParameter('mobile');
            $code = Buddha_Http_Input::getParameter('Code');
        }
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
        $datas = array();
        if($num and $DB_Verify and $DB_Verify['code']==$code){
            $datas['isok']='true';
        }else{
            $datas['isok']='false';
        }
        Buddha_Http_Output::makeJson($datas);
    }




}