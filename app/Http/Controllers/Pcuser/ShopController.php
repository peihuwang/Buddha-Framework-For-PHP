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
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
         $act=Buddha_Http_Input::getParameter('list');
        if($act=='list'){
        $where = " (isdel=0 or isdel=4) and referral_id='{$uid}'";
       // $rcount = $this->db->countRecords( $this->prefix.'shop', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
      /*  $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }*/
            $orderby = " order by id DESC ";
            $list = $this->db->getFiledValues (array('id','name','small','is_sure','createtimestr','state','number','isdel'),  $this->prefix.'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
            foreach($list as $k=>$v){
                $listnow[]=array(
                    'id'=>$v['id'],
                    'name'=>$v['name'],
                    'small'=>$v['small'],
                    'number'=>$v['number'],
                    'state'=>$v['state'],
                    'isdel'=>$v['isdel'],
                    'createtime'=>$v['createtimestr'],
                );
            }
            if($listnow){
                $data['isok']='true';
                $data['data']=$listnow;
            }else{
                $data['isok']='false';
                $data['data']='没有数据!';
            }
            Buddha_Http_Output::makeJson($data);
        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function add(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $UserObj=new User();
        $RegionObj = new Region();
        $GalleryObj=new Gallery();
        $getNatureOption=$ShopObj->getNatureOption();
        $this->smarty->assign('getNatureOption',$getNatureOption);

        $username=Buddha_Http_Input::getParameter('username');
        $password=Buddha_Http_Input::getParameter('password');
        $name=Buddha_Http_Input::getParameter('name');
        $shopcat_id=Buddha_Http_Input::getParameter('shopcat_id');
        $realname=Buddha_Http_Input::getParameter('realname');
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $tel=Buddha_Http_Input::getParameter('tel');
        $opentime=Buddha_Http_Input::getParameter('opentime');
        $level0=Buddha_Http_Input::getParameter('level0');
        $regionstr=Buddha_Http_Input::getParameter('regionstr');
        $specticloc=Buddha_Http_Input::getParameter('specticloc');
        $storetype=Buddha_Http_Input::getParameter('storetype');
        $property = Buddha_Http_Input::getParameter('property');
        $bushour=Buddha_Http_Input::getParameter('bushour');
        $myrange=Buddha_Http_Input::getParameter('myrange');
        $brief=Buddha_Http_Input::getParameter('brief');
        $shopdesc=Buddha_Http_Input::getParameter('shopdesc');

        $level=explode(",", $regionstr);
        //地址ID转换转成文字
        $str = $RegionObj->getAddress($level[2]);
        //获取经纬度
        $lt= $ShopObj->location($str.$specticloc);

        $Db_agentrate=$UserObj->getSingleFiledValues(array('id','agentrate'),"isdel=0 and level3='{$level[2]}' and groupid=2 ");

        if(Buddha_Http_Input::isPost()){
            $num=$UserObj->countRecords("mobile='{$mobile}' and isdel=0 ");
            if($num>0){
                $datas['isok']='false';
                $datas['data']='手机号已存在';
                $datas['url'] = 'index.php?a=add&c=shop';
                Buddha_Http_Output::makeJson($datas);
            }
            $datas=array();
            $data=array();
             if($username!=''){
                 $data['username']=$username;
             }else{
                 $data['username']=$mobile;
             }
            if($password){
                $data['password']=Buddha_Tool_Password::md5($password);
                $data['codes']=$password;
            }else{
                $password='123456';
                $data['password']=Buddha_Tool_Password::md5($password);
                $data['codes']=$password;
            }
            $data['referral_id'] = $uid;
            $data['partnerrate'] = (int)$UserInfo['partnerrate'];
            $data['mobile'] = $mobile;
            $data['mobile_ide'] = 1;
            $data['groupid'] = 1;
            $data['state'] = 1;
            $data['realname'] = $realname;
            $data['mobile'] = $mobile;
            $data['tel'] = $tel;
            $data['level0'] = (int)$level0;
            $data['level1'] = (int)$level[0];
            $data['level2'] = (int)$level[1];
            $data['level3'] = (int)$level[2];
            $data['onlineregtime'] = Buddha::$buddha_array['buddha_timestamp'];

            $User_insertid = $UserObj->add($data);
            if($User_insertid){
                $datas['user_id'] = $User_insertid;
                $datas['referral_id'] = (int)$uid;
                $datas['partnerrate'] = (int)$UserInfo['partnerrate'];
                $datas['agent_id'] = (int)$Db_agentrate['id'];
                $datas['agentrate'] = (int)$Db_agentrate['agentrate'];
                $datas['name'] = $name;
                $datas['shopcat_id'] = $shopcat_id;
                $datas['realname'] = $realname;
                $datas['mobile'] = $mobile;
                $datas['tel'] = $tel;
                $datas['opentime'] = strtotime($opentime);
                $datas['level0'] = (int)$level0;
                $datas['level1'] = (int)$level[0];
                $datas['level2'] = (int)$level[1];
                $datas['level3'] = (int)$level[2];
                $datas['lng'] = $lt['lng'];
                $datas['lat'] = $lt['lat'];
                $datas['number'] = date("ymdHis") . mt_rand(1000, 9999);
                $datas['regionstr'] = $regionstr;
                $datas['specticloc'] = $specticloc;
                $datas['storetype'] = $storetype;
                $datas['property'] = $property;
                $datas['bushour'] = $bushour;
                $datas['myrange'] = $myrange;
                $datas['brief'] = $brief;
                $datas['shopdesc'] = $shopdesc;
                $datas['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $datas['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

                $shop_id = $ShopObj->add($datas);
                if($shop_id){
                $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/shop/{$shop_id}/",
                    array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                    ->getOneReturnArray();
                if ($Image) {
                    $GalleryObj->resolveImageForRotate(PATH_ROOT.$Image,NULL);
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 1200, 640, 'L_');
                }
                $sourcepic = str_replace("storage/shop/{$shop_id}/", '', $Image);
                if ($Image) {
                    $data=array();
                    $data['small'] = "storage/shop/{$shop_id}/S_" . $sourcepic;
                    $data['medium'] = "storage/shop/{$shop_id}/M_" . $sourcepic;
                    $data['large'] = "storage/shop/{$shop_id}/L_" . $sourcepic;
                    $data['sourcepic'] = "storage/shop/{$shop_id}/" . $sourcepic;
                    $ShopObj->edit($data,$shop_id);
                }
                    $datas=array();
                    $datas['isok']='true';
                    $datas['data']='店铺添加成功';
                    $datas['url']='index.php?a=index&c=shop';
                }else{
                    $datas['isok']='false';
                    $datas['data']='店铺添加失败';
                    $datas['url'] = 'index.php?a=index&c=shop';
                }
                Buddha_Http_Output::makeJson($datas);
            }
        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $ShopcatObj=new Shopcat();
        $RegionObj=new Region();
        $UserObj=new User();
        $GalleryObj=new Gallery();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=shop',2);
        }
        $shopinfo=$ShopObj->getSingleFiledValues('',"id='{$id}' and referral_id='{$uid}'");
        if(!$shopinfo){
            Buddha_Http_Head::redirectofmobile('没有找到您要的信息！','index.php?a=index&c=shop',2);
        }


        $name=Buddha_Http_Input::getParameter('name');
        $shopcat_id=Buddha_Http_Input::getParameter('shopcat_id');
        $realname=Buddha_Http_Input::getParameter('realname');
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $tel=Buddha_Http_Input::getParameter('tel');
        $opentime=Buddha_Http_Input::getParameter('opentime');
        $level0=Buddha_Http_Input::getParameter('level0');
        $regionstr=Buddha_Http_Input::getParameter('regionstr');
        $specticloc=Buddha_Http_Input::getParameter('specticloc');
        $storetype=Buddha_Http_Input::getParameter('storetype');
        $property=Buddha_Http_Input::getParameter('property');
        $bushour=Buddha_Http_Input::getParameter('bushour');
        $myrange=Buddha_Http_Input::getParameter('myrange');
        $brief=Buddha_Http_Input::getParameter('brief');
        $shopdesc=Buddha_Http_Input::getParameter('shopdesc');

        $level=explode(",", $regionstr);

        //地址ID转换转成文字
        $str = $RegionObj->getAddress($level[2]);
        //获取经纬度
            $lt = $ShopObj->location($str . $specticloc);
            $Db_agentrate = $UserObj->getSingleFiledValues(array('id', 'agentrate'), "isdel=0 and level3='{$level[2]}' and groupid=2 ");
        if(Buddha_Http_Input::isPost()){
            $datas['referral_id'] = (int)$uid;
            $datas['partnerrate'] = (int)$UserInfo['partnerrate'];
            $datas['agent_id'] = (int)$Db_agentrate['id'];
            $datas['agentrate'] = (int)$Db_agentrate['agentrate'];
            $datas['name'] = $name;
            $datas['shopcat_id'] = $shopcat_id;
            $datas['realname'] = $realname;
            $datas['mobile'] = $mobile;
            $datas['tel'] = $tel;
            $datas['opentime'] = strtotime($opentime);
            $datas['level0'] = $level0;
            $datas['level1'] = $level[0];
            $datas['level2'] = $level[1];
            $datas['level3'] = $level[2];
            $datas['lng'] = $lt['lng'];
            $datas['lat'] = $lt['lat'];
            $datas['number'] = date("ymdHis") . mt_rand(1000, 9999);
            $datas['regionstr'] = $regionstr;
            $datas['specticloc'] = $specticloc;
            $datas['storetype'] = $storetype;
            $datas['property'] = $property;
            $datas['bushour'] = $bushour;
            $datas['myrange'] = $myrange;
            $datas['brief'] = $brief;
            $datas['shopdesc'] = $shopdesc;
            $datas['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $datas['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

            $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/shop/{$id}/",
                array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                ->getOneReturnArray();
            if ($Image) {
                $GalleryObj->resolveImageForRotate(PATH_ROOT.$Image,NULL);
                Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 1200, 640, 'L_');
            }
            $sourcepic = str_replace("storage/shop/{$id}/", '', $Image);
            if ($Image) {
                $ShopObj->deleteFIleOfPicture($id);
                $datas['small'] = "storage/shop/{$id}/S_" . $sourcepic;
                $datas['medium'] = "storage/shop/{$id}/M_" . $sourcepic;
                $datas['large'] = "storage/shop/{$id}/L_" . $sourcepic;
                $datas['sourcepic'] = "storage/shop/{$id}/" . $sourcepic;
            }
            $ShopObj->edit($datas,$id);
            if($ShopObj){
                $datas=array();
                $datas['isok']='true';
                $datas['data']='店铺修改成功';
                $datas['url']='index.php?a=index&c=shop';
            }else{
                $datas['isok']='false';
                $datas['data']='店铺修改失败';
                $datas['url'] = 'index.php?a=index&c=shop';
            }
            Buddha_Http_Output::makeJson($datas);
         }


        $getNatureOption=$ShopObj->getNatureOption($shopinfo['storetype']);
        $agent_area=$RegionObj->getAllArrayAddressByLever($shopinfo['level3']);
        if($agent_area){
            $address='';
            foreach($agent_area as $k=>$v){
                if($k>0 and $k<4){
                    $address.=$v['name'].'>';
                }
            }
            $shopinfo['address']=Buddha_Atom_String::toDeleteTailCharacter($address);
        }

        $shopcat=$ShopcatObj->goods_thumbgoods_thumb($shopinfo['shopcat_id']);

        if($shopcat){
            $cat='';
            foreach($shopcat as $k=>$v){
                $cat.=$v['cat_name'].'>';
            }
            $shopinfo['cat']=Buddha_Atom_String::toDeleteTailCharacter($cat);
        }

        $this->smarty->assign('shopinfo',$shopinfo);
        $this->smarty->assign('getNatureOption',$getNatureOption);
         $TPL_URL = $this->c.'.'.__FUNCTION__;

        $this->smarty -> display($TPL_URL.'.html');
    }




    public function shopcat(){
      $ShopcatObj=new Shopcat();
        $fid = Buddha_Http_Input::getParameter('fid');
       $Db_Shopcat= $ShopcatObj->getShopcatlist($fid);
        $datas = array();
        if($Db_Shopcat){
            $datas['isok']='true';
           $datas['data']=$Db_Shopcat;
        }else{
            $datas['isok']='false';
            $datas['data']='';
        }
        Buddha_Http_Output::makeJson($datas);
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


    public function existnickname($param_username=''){
        if($param_username){
            $username = $param_username;
        }else{
            $username = Buddha_Http_Input::getParameter('username');
        }
        $UserObj = new User();
        $num = $UserObj->countRecords("isdel=0 and username='{$username}'");
        if($param_username){
            if($num==0){
                return 1;

            }else{
                return  0;
            }
        }
        $data = array();
        if($num==0){
            $data['isok']='true';
        }else{
            $data['isok']='false';
        }
        Buddha_Http_Output::makeJson($data);

    }


    public function existmobile($param_mobile=''){
        if($param_mobile){
            $Mobile = $param_mobile;
        }else{
            $Mobile = Buddha_Http_Input::getParameter('Mobile');
            $user_id = Buddha_Http_Input::getParameter('user_id');
        }
        $UserObj = new User();
        if($user_id){
            $num = $UserObj->countRecords("state=1  and id!='{$user_id}' ");
        }else{
            $num = $UserObj->countRecords("isdel=0 and mobile='{$Mobile}'");
        }

        if($param_mobile){
            if($num==0){
                return 1;
            }else{
                return  0;
            }
        }
        $data = array();
        if($num==0){
            $data['isok']='true';
        }else{
            $data['isok']='false';
        }
        Buddha_Http_Output::makeJson($data);

    }





}