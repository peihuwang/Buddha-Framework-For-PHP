<?php

/**
 * Class PartnerController
 */
class PartnerController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        $RegionObj=new Region();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $area=$RegionObj->getAllArrayAddressByLever($UserInfo['level3']);
        if(is_array($area) and count($area) >0){
            $quyu='';
            foreach($area as $k=>$v){
                if($v['id']!=1){
                    $quyu.=$v['name'].' > ';
                }
            }
            $UserInfo['quyu']=Buddha_Atom_String::toDeleteTailCharacter($quyu);
        }
        $this->smarty->assign('UserInfo',$UserInfo);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function info() {
        $UserObj = new User();
        $RegionObj = new Region();
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $realname = Buddha_Http_Input::getParameter('realname');
        $email = Buddha_Http_Input::getParameter('email');
        $level0 = Buddha_Http_Input::getParameter('country');
        $level1 = Buddha_Http_Input::getParameter('prov');
        $level2 = Buddha_Http_Input::getParameter('city');
        $level3 = Buddha_Http_Input::getParameter('area');
        $address = Buddha_Http_Input::getParameter('address');

        if (Buddha_Http_Input::isPost()) {
            $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/user/logo/{$uid}/",
                array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('logo')
                ->getOneReturnArray();
            if ($Image) {
                Buddha_Tool_File::thumbImage(PATH_ROOT . $Image, 300, 300, 'S_');
            }
            $sourcepic = str_replace("storage/user/logo/{$uid}/", '', $Image);
            @unlink(PATH_ROOT . $Image);
            $data = array();
            $data['realname'] = $realname;
            $data['email'] = $email;

                $data['level0'] = $level0;
                $data['level1'] = $level1;
                $data['level2'] = $level2;
                $data['level3'] = $level3;

            $data['address'] = $address;
            if ($Image) {
                @unlink(PATH_ROOT . $UserInfo['logo']);
                $data['logo'] = "storage/user/logo/{$uid}/S_" . $sourcepic;
            }
            $UserObj->edit($data, $uid);
            if ($UserObj) {
                Buddha_Http_Output::makeValue(1);
            } else {
                Buddha_Http_Output::makeValue(0);
            }
        }

        $area=$RegionObj->getAllArrayAddressByLever($UserInfo['level3']);
        if(is_array($area) and count($area) >0){
            $quyu='';
            foreach($area as $k=>$v){
                if($v['id']!=1){
                    $quyu.=$v['name'].' > ';
                }
            }
            $UserInfo['quyu']=Buddha_Atom_String::toDeleteTailCharacter($quyu);
        }
        $this->smarty->assign('UserInfo',$UserInfo);

        /*

        if ($UserInfo['level0'] != 0){
            $country = $RegionObj->getChildlistpc(0);
            $prov = $RegionObj->getChildlistpc($UserInfo['level0']);
            $city = $RegionObj->getChildlistpc($UserInfo['level1']);
            $area = $RegionObj->getChildlistpc($UserInfo['level2']);
        }
        $this->smarty->assign('country',$country);
        $this->smarty->assign('prov',$prov);
        $this->smarty->assign('city',$city);
        $this->smarty->assign('area',$area);*/
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function pwd(){
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserObj=new User();
        $VerifyObj=new Verify();
        $step=Buddha_Http_Input::getParameter('step')?(int)Buddha_Http_Input::getParameter('step'):1;

        if($step==1){
            $mobile=Buddha_Http_Input::getParameter('mobile');
            $code=Buddha_Http_Input::getParameter('code');
            if(Buddha_Http_Input::isPost()){
                $Code1= $this->verify($mobile,$code);
                if(!$Code1){
                    Buddha_Http_Output::makeValue(0);
                }else{
                    $VerifyObj->hadPass($mobile,$code);
                    Buddha_Http_Output::makeValue(1);
                }
            }
        }

        if($step==2){
            $password=Buddha_Http_Input::getParameter('password');
            if(Buddha_Http_Input::isPost()){
                $pwd=Buddha_Tool_Password::md5($password);
                $num = $UserObj->countRecords("isdel=0 and id='{$uid}' and password='{$pwd}'");
                if($num){
                    Buddha_Http_Output::makeValue(0);
                }
                $UserObj->edit(array('password'=>Buddha_Tool_Password::md5($password),'codes'=>$password),$uid);
                if($UserObj){
                    Buddha_Http_Output::makeValue(1);
                }else{
                    Buddha_Http_Output::makeValue(2);
                }
            }

        }

        if($UserInfo['mobile']){
            $UserInfo['mobiley']=substr_replace($UserInfo['mobile'],'****',3,4);
        }
        $this->smarty->assign('UserInfo',$UserInfo);
        $this->smarty->assign('step',$step);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }






    public function prov(){
        $RegionObj=new Region();
        $country=Buddha_Http_Input::getParameter('country');
        $Db_arear=$RegionObj->getFiledValues(array('id','name'),"father='{$country}'");
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

    public function city(){
        $RegionObj=new Region();
        $prov=Buddha_Http_Input::getParameter('prov');
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




    public function verify($param_mobile='',$param_code=''){
        $nowtime = time();
        if($param_mobile){
            $mobile = $param_mobile;
            $code = $param_code;
        }else{
            $mobile = Buddha_Http_Input::getParameter('Mobile');
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
        if($param_mobile){
            if($num and $DB_Verify and $DB_Verify['code']==$code){
                return 1;
            }else{
                return 0;
            }
        }
        if($num and $DB_Verify and $DB_Verify['code']==$code){
            Buddha_Http_Output::makeValue(1);
        }else{
            Buddha_Http_Output::makeValue(0);
        }
    }


    public function  verifyrequest(){
        $nowtime = time();
        $json = Buddha_Http_Input::getParameter('json');
        $json_arr =Buddha_Atom_Array::jsontoArray($json);
        $mobile =$json_arr['mobile'];

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
            $snedflag= $VerifyObj->smsSend($mobile,$data['code']);
            if($snedflag){
                $VerifyObj->add($data);
                Buddha_Http_Output::makeValue(1);
            }else{
                Buddha_Http_Output::makeValue(0);
            }
        }
    }

}