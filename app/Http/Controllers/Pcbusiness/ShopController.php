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
        //====================================================================================================
        /*   查询当前用户是否有合伙人
         *      有：则查看店铺有没有合伙人（当前用户有合伙人而店铺没有就要加上）
         */
        $ShopObj=new Shop();
        $ShopObj->referral_id_func();
//====================================================================================================

        $where = " (isdel=0 or isdel=4) and user_id='{$uid}'";
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
        $fields=array('id','name','small','is_verify','veifytime','veryfyendtime','createtimestr','number','specticloc','level3','brief','tel','state','is_sure');
        $list = $this->db->getFiledValues ($fields, $this->prefix.'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=shop&', $pagesize );
        foreach($list as $k=>$v){
            $str = $RegionObj->getAddress($v['level3']);
            $list[$k]['area']=$str;
        }
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


        $name=Buddha_Http_Input::getParameter('name');
        $is_verify=Buddha_Http_Input::getParameter('is_verify');
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
        $qq = Buddha_Http_Input::getParameter('qq');
        $wechatnumber = Buddha_Http_Input::getParameter('wechatnumber');
        if(Buddha_Http_Input::isPost()) {
            $str = $RegionObj->getAddress($level3);
            $lt = $ShopObj->location($str . $specticloc);

            $Db_agentrate = $UserObj->getSingleFiledValues(array('id', 'agentrate'), "isdel=0 and level3='{$level3}'and groupid=2");
            $datas = array();
            $datas['qq'] = $qq;
            $datas['user_id'] = $uid;
            $datas['wechatnumber'] = $wechatnumber;
            $datas['agent_id'] = (int)$Db_agentrate['id'];
            $datas['agentrate'] = (int)$Db_agentrate['agentrate'];
            $datas['referral_id'] = 0;
            $datas['partnerrate'] = 0;
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
            $datas['regionstr'] = $level0 . ',' . $level1 . ',' . $level2 . ',' . $level3;
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
            $jsondata = array();
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
                $data['small'] = "storage/shop/{$addshop_id}/S_" . $sourcepic;
                $data['medium'] = "storage/shop/{$addshop_id}/M_" . $sourcepic;
                $data['large'] = "storage/shop/{$addshop_id}/L_" . $sourcepic;
                $data['sourcepic'] = "storage/shop/{$addshop_id}/" . $sourcepic;
                $ShopObj->edit($data,$addshop_id);
            }

                $jsondata['id'] =$addshop_id;
                $jsondata['errcode'] = 0;
                $jsondata['errmsg'] = "OK";
                Buddha_Http_Output::makeJson($jsondata);
                }else{
                $jsondata['id'] =$addshop_id;
                $jsondata['errcode'] = 1;
                $jsondata['errmsg'] = "err";
                Buddha_Http_Output::makeJson($jsondata);
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
        $referral_id = Buddha_Http_Input::getParameter('al_id');
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
        $qq = Buddha_Http_Input::getParameter('qq');
        $wechatnumber = Buddha_Http_Input::getParameter('wechatnumber');
        if(Buddha_Http_Input::isPost()) {
            $str = $RegionObj->getAddress($level3);
            $lt = $ShopObj->location($str . $specticloc);

            $Db_agentrate = $UserObj->getSingleFiledValues(array('id', 'agentrate'), "isdel=0 and level3='{$level3}'and groupid=2");
            $datas = array();
            $datas['user_id'] = $uid;
            $datas['wechatnumber'] = $wechatnumber;
            $datas['qq'] = $qq;
            $datas['agent_id'] = (int)$Db_agentrate['id'];
            $datas['agentrate'] = (int)$Db_agentrate['agentrate'];
            $datas['referral_id'] = $referral_id;
            $datas['partnerrate'] = 0;
            $datas['number'] = date("ymdHis") . mt_rand(1000, 9999);
            $datas['shopcat_id'] =$shopcat_id;
            $datas['realname'] = $realname;
            $datas['name'] = $name;
            $datas['mobile'] = $mobile;
            $datas['tel'] = $tel;
//            $datas['level0'] = $level0;
//            $datas['level1'] = $level1;
//            $datas['level2'] = $level2;
//            $datas['level3'] = $level3;
            $datas['regionstr'] = $level0 . ',' . $level1 . ',' . $level2 . ',' . $level3;
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

            $ShopObj->edit($datas,$id);
            $jsondata=array();
            if ($ShopObj) {
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
                    $ShopObj->deleteFIleOfPicture($id);
                    $ShopObj->edit($data,$id);
                }

                $jsondata['id'] =$id;
                $jsondata['errcode'] = 0;
                $jsondata['errmsg'] = "OK";
                Buddha_Http_Output::makeJson($jsondata);
            }else{
                $jsondata['id'] =$id;
                $jsondata['errcode'] =1;
                $jsondata['errmsg'] = "OK";
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


        //消息置顶
        $OrderObj=new Order();
        $Top=$OrderObj->getFiledValues(array('final_amt','createtime'),"isdel=0 and good_id='{$id}' and order_type='info.top' and pay_status=1 and user_id='{$uid}'");
        if(count($Top)>0){
            foreach ($Top as $k=>$v){
                $Top[$k]['name']=$shop['name'];
            }
        }
        $this->smarty->assign('Top', $Top);

        $infotop=array('id'=>$shop['id'],'good_table'=>'shop','order_type'=>'info.top','final_amt'=>'0.2','pc'=>'1');
        $this->smarty->assign('infotop', $infotop);


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }




    public function state(){
        $ShopObj=new Shop();
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
         $shop_id=Buddha_Http_Input::getParameter('id');
        $num=$ShopObj->countRecords("isdel=0 and id='{$shop_id}' and is_sure='1'");
        if($num==0){
         Buddha_Http_Output::makeValue(0);
        }else{
            $Db_state=$ShopObj->getSingleFiledValues(array('state'),"id='{$shop_id}' and user_id='{$uid}'") ;
            if($Db_state['state']==1){
                $ShopObj->EnableShop($shop_id,$uid);
                Buddha_Http_Output::makeValue(1);
            }else{
                $ShopObj->DisableShop($shop_id,$uid);
                Buddha_Http_Output::makeValue(1);
            }
        }

    }

    public function  del(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $result = $ShopObj->del($id);
        if($result){
            $ShopObj-> delshop($uid,$id);
            Buddha_Http_Head::redirect('删除成功',"index.php?a=index&c=shop");
        }else{
            Buddha_Http_Head::redirect('删除失败',"index.php?a=index&c=shop");
        }
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function auditfailure(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $num=$ShopObj->countRecords("id='{$id}' and user_id='{$uid}' and isdel=0  and is_sure=4");
        $data=array();
        if($num==0){
            $data=array(
                'errcode'=>'1',
                'errmsg'=>'err',
                'data'=>'数据错误，联系管理员',
            );
            Buddha_Http_Output::makeJson($data);
        }
        $remarks= $ShopObj->getSingleFiledValues(array('remarks'),"id='{$id}' and user_id='{$uid}' and isdel=0 and is_sure=4");

        $data=array(
            'data'=>$remarks['remarks'],
            'errcode'=>'0',
            'errmsg'=>'ok',
        );
        Buddha_Http_Output::makeJson($data);
    }
    //查询订单是否成功
    public function infosee(){
        $OrderObj=new Order();
        $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $good_id=Buddha_Http_Input::getParameter('id');
        $good_table=Buddha_Http_Input::getParameter('good_table');
        if(!$user_id){
            $jsondata = array();
            $jsondata['url'] = 'index.php?a=login&c=account';
            $jsondata['errcode'] = 1;
            $jsondata['errmsg'] = "请登陆";
            Buddha_Http_Output::makeJson($jsondata);
        }
        $startstr = date('Y-m-d',time());
        $start= strtotime($startstr);
        $end=time()+600;
        $Db_orderunm= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$good_id}' and good_table='{$good_table}' and pay_status=1 and createtime>$start and createtime<=$end order by createtime DESC" );

        if($Db_orderunm){
            $jsondata = array();
            $jsondata['url'] = 'index.php?a=detailed&c='.$good_table;
            $jsondata['errcode'] = 0;
            $jsondata['errmsg'] ='ok';
            Buddha_Http_Output::makeJson($jsondata);
        }
    }

}