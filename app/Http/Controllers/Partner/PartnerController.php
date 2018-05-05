<?php

/**
 * Class CopyController
 */
class PartnerController extends Buddha_App_Action{
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $where = "u_id={$uid} and sure=1";
        $NewsObj=new News();
        $num = $NewsObj->countRecords($where );
        $this->smarty->assign('num',$num);
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


        $ShopObj = new Shop();
        $times = time();
        $UserfeeObj = new Userfee();
        $usaerfeeInfo = $UserfeeObj->getSingleFiledValues('',"user_id='{$uid}' AND (fee_type=1 OR fee_type=2) AND ispay=1 AND is_sure=1 AND isdel=0 AND endtime>'{$times}'");

        $shopInfo = $ShopObj->getFiledValues('',"user_id='{$uid}'");
        if(!empty($shopInfo)){
            $shopNum = 1;
        }else{
            $shopNum = 0;
        }
        $is_verify = 0;
        foreach ($shopInfo as $key => $value) {
            if($value['is_verify'] == 1){
                $is_verify = 1;
            }
        }

        if($usaerfeeInfo || $is_verify == 1){
            $usaerfeeNum = 1;
            $this->smarty->assign('usaerfeeNum',$usaerfeeNum);
        }


        if($UserInfo['nickname']){
            $UserInfo['realname'] = $UserInfo['nickname'];
        }
        if(stripos($UserInfo['logo'],"qlogo")){
            $sure = 1;
            $this->smarty->assign('sure',$sure);
        }
        $counts = $ShopObj->countRecords("referral_id={$uid} and is_sure=1");
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty->assign('counts',$counts);
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
            $UserInfo['areadder']=Buddha_Atom_String::toDeleteTailCharacter($areadder);
        }
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

    public function tel(){
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $fid = Buddha_Http_Input::getParameter('fid');
        $UserObj->edit(array('tel'=>$fid),$uid);
        $data=array();
        if($UserObj){
            $data['isok']='true';
            $data['data']=$fid;
        }else{
            $data['isok']='false';
            $data['data']='编辑失败';
        }
        Buddha_Http_Output::makeJson($data);
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

    public function address(){
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $fid = Buddha_Http_Input::getParameter('fid');
        $UserObj->edit(array('address'=>$fid),$uid);
        $data=array();
        if($UserObj){
            $data['isok']='true';
            $data['data']=$fid;
        }else{
            $data['isok']='false';
            $data['data']='编辑失败';
        }
        Buddha_Http_Output::makeJson($data);
    }

    public function gender(){
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $fid = Buddha_Http_Input::getParameter('fid');
        $gender = $fid;
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
        $mobile = Buddha_Http_Input::getParameter('mobile');
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
            $ch = curl_init();
            $post_data = array(
            "account" => "sdk_haibojs",
            "password" => "dkghh46",
            "destmobile" => $mobile,
            "msgText" => "您的验证码是 ". $data['code'] . " 此验证码只用于绑定、修改手机、修改密码验证，请勿将此验证码发给任何号码及其他人【本地商家网】",
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


    public function business (){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act=Buddha_Http_Input::getParameter('list');
        if($act=='list'){
            $where = " isdel=0 and referral_id='{$uid}'";
            $keyword=Buddha_Http_Input::getParameter('keyword');
            if($keyword){
                $where.="and (username LIKE '%{$keyword}%' or mobile LIKE '%{$keyword}%' or realname LIKE '%{$keyword}%')";
            }

            $rcount = $this->db->countRecords( $this->prefix.'user', $where);
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];
            $pcount = ceil($rcount/$pagesize);
            if($page > $pcount){
                $page=$pcount;
            }
            $orderby = " order by onlineregtime DESC ";
            $find=array('id','username','mobile','onlineregtime','groupid','state','username','realname');
            $list = $this->db->getFiledValues ($find,  $this->prefix.'user', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
            foreach($list as $k=>$v){
                if($v['state']==0){
                    $list[$k]['state']='未激活';
                }elseif($v['state']==1){
                    $list[$k]['state']='激活';
                }else{
                    $list[$k]['state']='注销';
                }
                if($v['groupid']==1){
                    $list[$k]['groupid']='商家';
                }
                if($v['realname']=='' or $v['realname']=='0'){
                    $list[$k]['realname']=$v['username'];
                }else{
                    $list[$k]['realname']=$v['realname'];
                }

                $list[$k]['onlineregtime']=date('Y-m-d',$v['onlineregtime']);
            }
            if($list){
                $data['isok']='true';
                $data['data']=$list;
            }else{
                $data['isok']='false';
                $data['data']='没有数据!';
            }
            Buddha_Http_Output::makeJson($data);

        }
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


}