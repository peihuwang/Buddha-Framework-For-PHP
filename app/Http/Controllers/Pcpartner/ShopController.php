<?php

/**
 * Class ShopController
 */
class ShopController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        $RegionObj=new Region();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;

        $where = " (isdel=0 or isdel=4) and referral_id='{$uid}'";

        if($view){
            switch($view){
                case 2;
                    $where.=' and isdel=0 and is_sure=0';
                    break;
                case 3;
                    $where.=" and isdel=0 and is_sure=1";
                    break;
                case 4;
                    $where.=" and isdel=0 and is_sure=4 ";
                    break;
                case 5;
                    $where.=" and isdel=4 and state=1";
                    break;
            }
        }
        if($keyword){
            $where.=" and name like '%$keyword%'";
        }

         $rcount = $this->db->countRecords( $this->prefix.'shop', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
         $pcount = ceil($rcount/$pagesize);
           if($page > $pcount){
               $page=$pcount;
           }
        $orderby = " order by createtime DESC ";
        $fields=array('id','name','small','is_verify','veifytime','veryfyendtime','createtimestr','number','specticloc','level3','brief','tel','state','is_sure','remarks');
        $list = $this->db->getFiledValues ($fields, $this->prefix.'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=shop&', $pagesize );
        foreach($list as $k=>$v){
            $str = $RegionObj->getAddress($v['level3']);
            $list[$k]['area']=$str;
        }
        $this->smarty->assign('view',$view);
        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function add(){
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
         $ShopcatObj=new Shopcat();
         $ShopObj=new Shop();
         $UserObj=new User();
         $RegionObj=new Region();


        $username=Buddha_Http_Input::getParameter('username');
        $password=Buddha_Http_Input::getParameter('password');
        $name=Buddha_Http_Input::getParameter('name');
        $shopcat_id=Buddha_Http_Input::getParameter('shopcat_id');
        $realname=Buddha_Http_Input::getParameter('realname');
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $tel=Buddha_Http_Input::getParameter('tel');
        $level0=Buddha_Http_Input::getParameter('country');
        $level1=Buddha_Http_Input::getParameter('prov');
        $level2=Buddha_Http_Input::getParameter('city');
        $level3=Buddha_Http_Input::getParameter('area');
        $specticloc=Buddha_Http_Input::getParameter('specticloc');
        $storetype=Buddha_Http_Input::getParameter('storetype');
        $property=Buddha_Http_Input::getParameter('property');
        $bushour=Buddha_Http_Input::getParameter('bushour');
        $myrange=Buddha_Http_Input::getParameter('myrange');
        $brief=Buddha_Http_Input::getParameter('brief');
        $shopdesc=Buddha_Http_Input::getParameter('content');

        if(Buddha_Http_Input::isPost()) {
            $data=array();
           $num=$UserObj->countRecords("isdel=0 and mobile='{$mobile}'");
            if($num){//手机存在不能添加店铺
                Buddha_Http_Output::makeValue(2);
            }
            if ($username) {
                $data['username'] = $username;
            } else {
                $data['username'] = $mobile;
            }
            if ($password) {
                $data['password'] = Buddha_Tool_Password::md5($password);
                $data['codes']=$password;
            } else {
                $data['password'] = Buddha_Tool_Password::md5('123456');
                $data['codes']='123456';
            }
            $data['realname'] = $realname;
            $data['mobile'] = $mobile;
            $data['mobile_ide']=1;
            $data['state']=1;
            $data['referral_id']=$uid;
            $data['partnerrate']=$UserInfo['partnerrate'];
            $data['groupid']=1;
            $data['level0'] = $level0;
            $data['level1'] = $level1;
            $data['level2'] = $level2;
            $data['level3'] = $level3;
            $data['address'] = $specticloc;

            $adduser_id=$UserObj->add($data);
            if ($adduser_id) {
                $str = $RegionObj->getAddress($level3);
                $lt = $ShopObj->location($str . $specticloc);
                $Db_agentrate = $UserObj->getSingleFiledValues(array('id', 'agentrate'), "isdel=0 and level3='{$level3}'and groupid=2");
                $datas = array();
                $datas['user_id'] = $adduser_id;
                $datas['agent_id'] = (int)$Db_agentrate['id'];
                $datas['agentrate'] = (int)$Db_agentrate['agentrate'];
                $datas['referral_id'] =$uid;
                $datas['partnerrate'] =$UserInfo['partnerrate'];
                $datas['number'] = date("ymdHis") . mt_rand(1000, 9999);
                $datas['shopcat_id'] = $shopcat_id;
                $datas['realname'] = $realname;
                $datas['name'] = $name;
                $datas['mobile'] = $mobile;
                $datas['tel'] = $tel;
                $datas['level0'] = $level0;
                $datas['level1'] = $level1;
                $datas['level2'] = $level2;
                $datas['level3'] = $level3;
                $datas['regionstr'] = $level1 . ',' . $level2 . ',' . $level3;
                $datas['lng'] = $lt['lng'];
                $datas['lat'] = $lt['lat'];
                $datas['specticloc'] = $specticloc;
                $datas['storetype'] = $storetype;
                $datas['property'] = $property;
                $datas['bushour'] = $bushour;
                $datas['myrange'] = $myrange;
                $datas['brief'] = $brief;
                $datas['shopdesc'] = $shopdesc;
                $datas['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $datas['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                $addshop_id = $ShopObj->add($datas);
                $jsondata=array();
                if ($addshop_id) {
                    $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/shop/{$addshop_id}/",
                        array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                        ->getOneReturnArray();
                    if ($Image) {
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 800, 800, 'L_');
                    }
                    $sourcepic = str_replace("storage/shop/{$addshop_id}/", '', $Image);
                    if ($Image) {
                        $data=array();
                        $data['small'] = "storage/shop/{$addshop_id}/S_" . $sourcepic;
                        $data['medium'] = "storage/shop/{$addshop_id}/M_" . $sourcepic;
                        $data['large'] = "storage/shop/{$addshop_id}/L_" . $sourcepic;
                        $data['sourcepic'] = "storage/shop/{$addshop_id}/" . $sourcepic;
                    }
                    $ShopObj->edit($data,$addshop_id);

                $jsondata['id'] =$addshop_id;
                $jsondata['errcode'] = 0;
                $jsondata['errmsg'] = "OK";
                Buddha_Http_Output::makeJson($jsondata);
            } else {
                $jsondata['id'] =$addshop_id;
                $jsondata['errcode'] = 0;
                $jsondata['errmsg'] = "err";
                Buddha_Http_Output::makeJson($jsondata);
            }
            }
        }
        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>'', 'width' => '100', 'height' => 500 )
            ));
        $getNatureOption=$ShopObj->getNatureOption();
        $getshopOption=$ShopcatObj->getOption();
        $this->smarty->assign('getshopOption',$getshopOption);
        $this->smarty->assign('getNatureOption',$getNatureOption);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $ShopcatObj=new Shopcat();
        $RegionObj=new Region();
        $UserObj=new User();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirect('参数错误',"index.php?a=index&c=shop");
        }
       $shop=$ShopObj->fetch($id);
        if(!$shop){
            Buddha_Http_Head::redirect('信息存在',"index.php?a=index&c=shop");
        }
        $name=Buddha_Http_Input::getParameter('name');
        $shopcat_id=Buddha_Http_Input::getParameter('shopcat_id');
        $realname=Buddha_Http_Input::getParameter('realname');
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $tel=Buddha_Http_Input::getParameter('tel');
        $level0=Buddha_Http_Input::getParameter('country');
        $level1=Buddha_Http_Input::getParameter('prov');
        $level2=Buddha_Http_Input::getParameter('city');
        $level3=Buddha_Http_Input::getParameter('area');
        $specticloc=Buddha_Http_Input::getParameter('specticloc');
        $storetype=Buddha_Http_Input::getParameter('storetype');
        $property=Buddha_Http_Input::getParameter('property');
        $bushour=Buddha_Http_Input::getParameter('bushour');
        $myrange=Buddha_Http_Input::getParameter('myrange');
        $brief=Buddha_Http_Input::getParameter('brief');
        $shopdesc=Buddha_Http_Input::getParameter('content');

        if(Buddha_Http_Input::isPost()) {
            $str = $RegionObj->getAddress($level3);
            $lt = $ShopObj->location($str . $specticloc);
            $Db_agentrate = $UserObj->getSingleFiledValues(array('id', 'agentrate'), "isdel=0 and level3='{$level3}'and groupid=2");
            $datas = array();
            $datas['user_id'] = $shop['user_id'];
            $datas['agent_id'] = (int)$Db_agentrate['id'];
            $datas['agentrate'] = (int)$Db_agentrate['agentrate'];
            $datas['referral_id'] =(int)$UserInfo['id'];
            $datas['partnerrate'] = (int)$UserInfo['partnerrate'];
            $datas['number'] = date("ymdHis") . mt_rand(1000, 9999);
            $datas['shopcat_id'] =$shopcat_id;
            $datas['realname'] = $realname;
            $datas['name'] = $name;
            $datas['mobile'] = $mobile;
            $datas['tel'] = $tel;
            $datas['level0'] = $level0;
            $datas['level1'] = $level1;
            $datas['level2'] = $level2;
            $datas['level3'] = $level3;
            $datas['regionstr'] =  $level1 . ',' . $level2 . ',' . $level3;
            $datas['lng'] = $lt['lng'];
            $datas['lat'] = $lt['lat'];
            $datas['specticloc'] = $specticloc;
            $datas['storetype'] = $storetype;
            $datas['property'] = $property;
            $datas['bushour'] = $bushour;
            $datas['myrange'] = $myrange;
            $datas['brief'] = $brief;
            $datas['shopdesc'] = $shopdesc;
            $datas['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $datas['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

            $addshop_id = $ShopObj->edit($datas,$id);
            $jsondata=array();
            if ($addshop_id) {
                $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/shop/{$id}/",
                    array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                    ->getOneReturnArray();
                if ($Image) {
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 800, 800, 'L_');
                }
                $sourcepic = str_replace("storage/shop/{$id}/", '', $Image);
                if ($Image) {
                    $data['small'] = "storage/shop/{$id}/S_" . $sourcepic;
                    $data['medium'] = "storage/shop/{$id}/M_" . $sourcepic;
                    $data['large'] = "storage/shop/{$id}/L_" . $sourcepic;
                    $data['sourcepic'] = "storage/shop/{$id}/" . $sourcepic;
                }
                $jsondata['id'] =$id;
                $jsondata['errcode'] = 0;
                $jsondata['errmsg'] = "OK";
                Buddha_Http_Output::makeJson($jsondata);
            }else{
                $jsondata['id'] =$id;
                $jsondata['errcode'] = 0;
                $jsondata['errmsg'] = "err";
                Buddha_Http_Output::makeJson($jsondata);
            }
        }

        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>$shop['shopdesc'], 'width' => '100', 'height' => 500 )
            ));

        $country = $RegionObj->getOptionOfRegionByLevel(0,0);
        $prov = $RegionObj->getOptionOfRegionByLevel($shop['level0'],1);
        $city = $RegionObj->getOptionOfRegionByLevel($shop['level1'],2);
        $area = $RegionObj->getOptionOfRegionByLevel($shop['level2'],3);
        $this->smarty->assign('country',$country);
        $this->smarty->assign('prov',$prov);
        $this->smarty->assign('city',$city);
        $this->smarty->assign('area',$area);
        $getNatureOption=$ShopObj->getNatureOption($shop['storetype']);
        $getshopOption=$ShopcatObj->getOption($shop['shopcat_id']);
        $this->smarty->assign('shop',$shop);
        $this->smarty->assign('getNatureOption',$getNatureOption);
        $this->smarty->assign('getshopOption',$getshopOption);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


}