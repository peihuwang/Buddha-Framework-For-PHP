<?php

/**
 * Class BusinessController
 */
class BusinessController extends Buddha_App_Action{
    public function __construct() {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }
    
    public function index()
    {
        
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $RegionObj=new Region();
        $UserfeeObj = new Userfee();
        $ShopObj = new Shop();
        $UserObj = new User();
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
            $CommonObj = new Common();
            $rearrs = $CommonObj->getWeChatUserInformation();
            //print_r($rearrs);
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
                if(!$UserInfo['openid'] || $UserInfo['openid'] != $rearrs['openid']){
                    $data['openid'] = $rearrs['openid'];
                    $aas = 1;
                }
                if($aas == 1){
                    $UserObj->updateRecords($data,"id='{$uid}'");
                }
            }
        }
        
        $usaerfeeInfo = $UserfeeObj->getSingleFiledValues('',"user_id='{$uid}' AND (fee_type=1 OR fee_type=2) AND ispay=1 AND is_sure=1 AND isdel=0 AND endtime>'{$times}'");
        
        $shopInfo = $ShopObj->getFiledValues('',"user_id='{$uid}'");
        if(!empty($shopInfo)){
            $shopNum = 1;
        }else{
            $shopNum = 0;
        }

        $agent_area= $RegionObj->getAllArrayAddressByLever($UserInfo['level3']);
        $where = "u_id={$uid} and sure=1";
        $NewsObj=new News();
        $num = $NewsObj->countRecords($where );
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

        if($shopNum){
            $this->smarty->assign('shopNum',$shopNum);
        }
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
        $prov = $RegionObj -> getFiledValues(array('id,name'),"level=1");
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
        $this->smarty->assign('prov',$prov);
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
    public function realname()
    {
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $realname = Buddha_Http_Input::getParameter('fid');
        $UserObj->edit(array('realname'=>$realname),$uid);
        $data=array();
        if($UserObj){
            $data['isok']='true';
            $data['data']=$realname;
        }else{
            $data['isok']='false';
            $data['data']='编辑失败';
        }
        Buddha_Http_Output::makeJson($data);
    }

    public function nickname()
    {
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $nickname = Buddha_Http_Input::getParameter('fid');
        $UserObj->edit(array('nickname'=>$nickname),$uid);
        $data=array();
        if($UserObj){
            $data['isok']='true';
            $data['data']=$nickname;
        }else{
            $data['isok']='false';
            $data['data']='编辑失败';
        }
        Buddha_Http_Output::makeJson($data);
    }

    public function tel()
    {
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

    public function address()
    {
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

    public function gender()
    {
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
    public function city(){
        $RegionObj=new Region();
        $prov=Buddha_Http_Input::getParameter('pro');
        $Db_arear=$RegionObj->getFiledValues(array('id','name'),"father='{$prov}'");
        $datas = array();
        if($Db_arear){
            $datas['state']='success';
            foreach($Db_arear as $k=>$v){
                $datas['data'][]=array(
                    'value'=>$v['id'],
                    'name'=>$v['name'],
                );
            }
        }
        Buddha_Http_Output::makeJson($datas);
    }

    public function area(){
        $RegionObj=new Region();
        $city=Buddha_Http_Input::getParameter('city');
        $Db_arear=$RegionObj->getFiledValues(array('id','name'),"father='{$city}'");
        $datas = array();

        if($Db_arear){
            $datas['state']='success';
            foreach($Db_arear as $k=>$v){
                $datas['data'][]=array(
                    'value'=>$v['id'],
                    'name'=>$v['name'],
                );
            }

        }
        Buddha_Http_Output::makeJson($datas);
    }

    public function exitCity(){
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $number=Buddha_Http_Input::getParameter('fid');
        if($number){
            $number = explode(',', $number);
            $re = $UserObj->edit(array('level1'=>$number['0'],'level2'=>$number['1'],'level3'=>$number['2']),$uid);
            if($re){
                $data['isok']='true';
                $data['data']=$data;
            }else{
                $data['isok']='false';
                $data['data']='编辑失败';
            }
            Buddha_Http_Output::makeJson($data);
        }
        
    }

    public function uploadlicense(){//店铺资料 法人、身份证、营业执照
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopdatumObj = new Shopdatum();
        $username = Buddha_Http_Input::getParameter('username');
        $number = Buddha_Http_Input::getParameter('number');
        $img = Buddha_Http_Input::getParameter('img');
        $imgs = Buddha_Http_Input::getParameter('imgs');
        $canvasImg = Buddha_Http_Input::getParameter('canvasImg');
        $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/supply/{$good_id}/",array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('file')->getAllReturnArray();



       
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function createQrcode(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $CommonObj = new Common();
        $qrcodeimg = $CommonObj->getQRCode('user','register',$uid);
        if($qrcodeimg){
            $data['isok']=1;
            $data['data']=$qrcodeimg;
        }
        Buddha_Http_Output::makeJson($data);
    }


    public function makemoney(){//分享躺赚
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $view = Buddha_Http_Input::getParameter('view')?Buddha_Http_Input::getParameter('view'):1;
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $UserObj = new User();
        $UserassoObj = new Userasso();
        $userInfo = $UserObj->getSingleFiledValues(array('realname','nickname','logo'),"id='{$uid}'");
        $field = array('id','father_id','username','nickname','realname','mobile','logo');
        $sql = $UserassoObj->getSqlFrontByLayerLimitNumberStr('',$uid);
        $idarr = $UserassoObj->getFiledValues(array('user_id'),"1=1 {$sql}");
        $idSets = Buddha_Atom_Array::getIdInStr($idarr);
        if($keyword){
            $where = " ( username LIKE '%{$keyword}%' OR mobile LIKE '%{$keyword}%' ) AND isdel=0 AND id in({$idSets})";
            $users = $UserObj->getSingleFiledValues($field,$where);
            foreach ($idarr as $k => $v){
                if(in_array($users['id'],$v)){
                    $aa = 1;
                    break;
                }else{
                    $aa = 0;
                }
            }
            if($aa){
                $userInfos[0] = $users;
            }else{
                $userInfos = 0;
            }
        }else{
            if($idSets){
                $userInfos = $UserObj->getFiledValues($field," 1=1 AND id in({$idSets})");
            }
        }

        if($view == 2){
            if($userInfo['nickname']){
                $userInfo['realname'] = $userInfo['nickname'];
            }
            $UserObj->createQrcodeForShare($uid,$userInfo['logo'],$userInfo['realname']);
        }
        foreach ($userInfos as $k => $v) {
            $logo = $v['logo'];

            if(Buddha_Atom_String::isValidString($logo)){
                if(!Buddha_Atom_String::hasNeedleString($logo,'http')){
                    $logo = '/' . Buddha_Atom_Dir::getformatDbStorageDir($logo);
                }
            }else{
                $logo = Buddha_Atom_String::getUserLogo($logo);
            }
            $userInfos[$k]['logo'] = $logo;
        }
        $this->smarty->assign('userInfos', $userInfos);
        $this->smarty->assign('keyword',$keyword);
        $this->smarty->assign('view',$view);
        $this->smarty->assign('haibaourl',$haibaourl);
        $this->smarty->assign('uid',$uid);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    

}