<?php

/**
 * Class LeaseController
 */
class LeaseController extends Buddha_App_Action{

    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        $LeasecatObj=new Leasecat();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act=Buddha_Http_Input::getParameter('act');
        if($act=='list'){
            $keyword=Buddha_Http_Input::getParameter('keyword');
                $where = "isdel=0 and user_id='{$uid}'";
                if($keyword){
                    $where.=" and lease_name like '%{$keyword}%'";
                }
                $rcount = $this->db->countRecords( $this->prefix.'supply', $where);
                $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
                $pagesize = Buddha::$buddha_array['page']['pagesize'];
            /* $pcount = ceil($rcount/$pagesize);
           if($page > $pcount){
                $page=$pcount;
            }*/

            $orderby = " order by id DESC ";
            $list = $this->db->getFiledValues('', $this->prefix . 'lease', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
            foreach ($list as $k => $v) {
                $cat_id = $v['leasecat_id'];
                $Db_Lease = $LeasecatObj->goods_thumbgoods_thumb($cat_id);
                if ($Db_Lease) {
                    $cat_name = '';
                    foreach ($Db_Lease as $k1 => $v1) {
                        $cat_name .= $v1['cat_name'] . ' > ';
                    }
                    $list[$k]['cat_name'] = Buddha_Atom_String::toDeleteTailCharacter($cat_name);
                }
            }
            if (is_array($list) and count($list) > 0) {
                $datas['isok'] = 'true';
                $datas['data'] = $list;
            } else {
                $datas['isok'] = 'false';
                $datas['data'] = '没有了';
            }
            Buddha_Http_Output::makeJson($datas);
        }
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function add(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $OrderObj=new Order();
        $UserObj=new User();
        $LeaseObj=new Lease();
        $GalleryObj=new Gallery();
        $ShopObj=new Shop();


        $act=Buddha_Http_Input::getParameter('act');
        $goods_name=Buddha_Http_Input::getParameter('lease_name');
        $leasecat_id=Buddha_Http_Input::getParameter('leasecat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $rent=Buddha_Http_Input::getParameter('rent');
        $keywords=Buddha_Http_Input::getParameter('keywords');

        //商品促销
        $lease_start_time=Buddha_Http_Input::getParameter('lease_start_time');
        $lease_end_time=Buddha_Http_Input::getParameter('lease_end_time');

        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        $regionstr=Buddha_Http_Input::getParameter('regionstr');

        //描述、图片
        $lease_brief=Buddha_Http_Input::getParameter('lease_brief');
        $lease_desc=Buddha_Http_Input::getParameter('lease_desc');



            if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['lease_name']=$goods_name;
            $data['user_id']=$uid;
            $data['leasecat_id']=$leasecat_id;
            $data['shop_id']=$shop_id;
            $data['rent']=$rent;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['lease_brief']=$lease_brief;
            $data['lease_desc']=$lease_desc;
            $data['lease_start_time']=strtotime($lease_start_time);
            $data['lease_end_time']=strtotime($lease_end_time);

                if($regionstr){
                    $level = explode(",", $regionstr);
                    $data['is_remote']=$is_remote;
                    $data['level0']=1;
                    $data['level1']=$level[0];
                    $data['level2']=$level[1];
                    $data['level3']=$level[2];
                }else{
                    $data['is_remote']=0;
                    $data['level0']=$UserInfo['level0'];
                    $data['level1']=$UserInfo['level1'];
                    $data['level2']=$UserInfo['level2'];
                    $data['level3']=$UserInfo['level3'];

            }
              $lease_id=$LeaseObj->add($data);
                $datas = array();
                if($lease_id){
                    $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/lease/{$lease_id}/",
                        array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                        ->getOneReturnArray();
                    if ($Image) {
                        $GalleryObj->resolveImageForRotate(PATH_ROOT.$Image,NULL);
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 1200, 640, 'L_');
                    }
                    $sourcepic = str_replace("storage/lease/{$lease_id}/", '', $Image);
                    if ($Image) {
                        $data['lease_thumb'] = "storage/lease/{$lease_id}/S_" . $sourcepic;
                        $data['lease_img'] = "storage/lease/{$lease_id}/M_" . $sourcepic;
                        $data['lease_large'] = "storage/lease/{$lease_id}/L_" . $sourcepic;
                        $data['sourcepic'] = "storage/lease/{$lease_id}/" . $sourcepic;
                        $LeaseObj->edit($data,$lease_id);
                    }

                   //$remote为1表示发布异地产品添加订单
                    if($is_remote==1){
                        $Db_referral=$UserObj->getSingleFiledValues(array('agent_id','agentrate'),"level3='{$UserInfo['level3']}' and isdel=0");
                        $money=0.2;
                        $money_agent=$Db_referral['agentrate']*$money/100;
                        $money_plat=$money-$money_agent;
                        $data=array();
                        $data['good_id']=$lease_id;
                        $data['user_id']=$uid;
                        $data['order_sn']= $OrderObj->birthOrderId($uid);
                        $data['good_table']='lease';
                        $data['pay_type']='third';
                        $data['good_table'] = 'info.market';
                        $data['referral_id']=0;
                        $data['partnerrate']=0;
                        $data['agent_id']=$Db_referral['agent_id'];
                        $data['agentrate']=$Db_referral['agentrate'];
                        $data['goods_amt'] = $money;
                        $data['final_amt'] = $money;
                        $data['money_plat'] = $money_plat;
                        $data['money_agent'] =$money_agent;
                        $data['money_partner'] = 0;
                        $data['payname']='微信支付';
                        $data['make_level0']=$UserInfo['level0'];
                        $data['make_level1']=$UserInfo['level1'];
                        $data['make_level2']=$UserInfo['level2'];
                        $data['make_level3']=$UserInfo['level3'];
                        $data['make_level4']=$UserInfo['level4'];
                        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                        $order_id=$OrderObj->add($data);
                        $datas['isok']='true';
                        $datas['data']='添加成功,去支付。';
                        $datas['url']='/topay/wechat/example/jsapi.php?order_id='.$order_id;
                    }else{
                        $datas['isok']='true';
                        $datas['data']='添加成功';
                        $datas['url']='index.php?a=index&c=lease';
                    }
                }else{
                    $datas['isok']='false';
                    $datas['data']='添加失败';
                    $datas['url']='index.php?a=add&c=lease';

            }
                Buddha_Http_Output::makeJson($datas);
        }


        $getshoplistOption=$ShopObj->getShoplistOption($uid,0);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    /**
     *
     */
    public function edit(){
        $OrderObj=new Order();
        $UserObj=new User();
        $ShopObj=new Shop();
        $LeaseObj=new Lease();
        $LeasecatObj=new Leasecat();
        $GalleryObj=new Gallery();
        $RegionObj=new Region();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
          Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=lease',2);
        }
        $Lease=$LeaseObj->getSingleFiledValues('',"id='{$id}' and user_id='{$uid}'");
        if(!$Lease){
            Buddha_Http_Head::redirectofmobile('商品已删除！','index.php?a=index&c=lease',2);
        }



            $goods_name=Buddha_Http_Input::getParameter('lease_name');
            $leasecat_id=Buddha_Http_Input::getParameter('leasecat_id');
            $shop_id=Buddha_Http_Input::getParameter('shop_id');
            $rent=Buddha_Http_Input::getParameter('rent');
            $keywords=Buddha_Http_Input::getParameter('keywords');

            //商品促销
            $lease_start_time=Buddha_Http_Input::getParameter('lease_start_time');
            $lease_end_time=Buddha_Http_Input::getParameter('lease_end_time');

            //商品异地发布
            $is_remote=Buddha_Http_Input::getParameter('is_remote');
            $regionstr=Buddha_Http_Input::getParameter('regionstr');

            //描述、图片
            $lease_brief=Buddha_Http_Input::getParameter('lease_brief');
            $lease_desc=Buddha_Http_Input::getParameter('lease_desc');

            if(Buddha_Http_Input::isPost()){
                $data=array();
                $data['lease_name']=$goods_name;
                $data['user_id']=$uid;
                $data['leasecat_id']=$leasecat_id;
                $data['shop_id']=$shop_id;
                $data['rent']=$rent;
                $data['keywords']=$keywords;
                $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
                $data['lease_brief']=$lease_brief;
                $data['lease_desc']=$lease_desc;
                $data['lease_start_time']=strtotime($lease_start_time);
                $data['lease_end_time']=strtotime($lease_end_time);

                    if($regionstr){
                        $level = explode(",", $regionstr);
                        $data['is_remote']=$is_remote;
                        $data['level0']=1;
                        $data['level1']=$level[0];
                        $data['level2']=$level[1];
                        $data['level3']=$level[2];
                    }else{
                        $data['is_remote']=0;
                        $data['level0']=$UserInfo['level0'];
                        $data['level1']=$UserInfo['level1'];
                        $data['level2']=$UserInfo['level2'];
                        $data['level3']=$UserInfo['level3'];
                    }


                $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/lease/{$id}/",
                    array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                    ->getOneReturnArray();
                if ($Image) {
                    $GalleryObj->resolveImageForRotate(PATH_ROOT.$Image,NULL);
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 1200, 640, 'L_');
                }
                $sourcepic = str_replace("storage/lease/{$id}/", '', $Image);
                if ($Image) {
                    $data['lease_thumb'] = "storage/lease/{$id}/S_" . $sourcepic;
                    $data['lease_img'] = "storage/lease/{$id}/M_" . $sourcepic;
                    $data['lease_large'] = "storage/lease/{$id}/L_" . $sourcepic;
                    $data['sourcepic'] = "storage/lease/{$id}/" . $sourcepic;
                    $LeaseObj->deleteFIleOfPicture($id);
                }

                $LeaseObj->edit($data,$id);

                $datas = array();
                if($LeaseObj){

                    //$is_remote为1表示发布异地产品添加订单
                    if($is_remote==1){
                        $Db_referral=$UserObj->getSingleFiledValues(array('agent_id','agentrate'),"level3='{$UserInfo['level3']}' and isdel=0");
                        $money=0.2;
                        $money_agent=$Db_referral['agentrate']*$money/100;
                        $money_plat=$money-$money_agent;
                        $data = array();
                        $data['good_id']=$id;
                        $data['user_id']=$uid;
                        $data['order_sn']= $OrderObj->birthOrderId($uid);
                        $data['good_table']='lease';
                        $data['pay_type']='third';
                        $data['good_table'] = 'info.market';
                        $data['referral_id']=0;
                        $data['partnerrate']=0;
                        $data['agent_id']=$Db_referral['agent_id'];
                        $data['agentrate']=$Db_referral['agentrate'];
                        $data['goods_amt'] = $money;
                        $data['final_amt'] = $money;
                        $data['money_plat'] = $money_plat;
                        $data['money_agent'] =$money_agent;
                        $data['money_partner'] = 0;
                        $data['payname']='微信支付';
                        $data['make_level0']=$UserInfo['level0'];
                        $data['make_level1']=$UserInfo['level1'];
                        $data['make_level2']=$UserInfo['level2'];
                        $data['make_level3']=$UserInfo['level3'];
                        $data['make_level4']=$UserInfo['level4'];
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        $order_id=$OrderObj->add($data);

                        $datas['isok']='true';
                        $datas['data']='编辑成功,去支付。';
                        $datas['url']='/topay/wechat/example/jsapi.php?order_id='.$order_id;
                    }else{
                        $datas['isok']='true';
                        $datas['data']='编辑成功';
                        $datas['url']='index.php?a=index&c=lease';
                    }
                }else{
                    $datas['isok']='false';
                    $datas['data']='编辑失败';
                    $datas['url']='index.php?a=edit&c=lease';
                }
                Buddha_Http_Output::makeJson($datas);
            }


        $getshoplistOption = $ShopObj->getShoplistOption($uid, $Lease['shop_id']);
        $Supplycat = $LeasecatObj->goods_thumbgoods_thumb($Lease['leasecat_id']);
        if ($Supplycat) {
            $cat_name = '';
            foreach ($Supplycat as $k => $v) {
                $cat_name .= $v['cat_name'] . ' > ';
            }
            $Lease['cat_name'] = Buddha_Atom_String::toDeleteTailCharacter($cat_name);
        }
        //区域名称拼接
        $Region_name = $RegionObj->getAllArrayAddressByLever($Lease['level3']);
        if ($Region_name) {
            $regionname = '';
            foreach ($Region_name as $k => $v) {
                if ($k != 0) {
                    $regionname .= $v['lease_name'] . ' > ';
                }
            }
            $Lease['region_name'] = Buddha_Atom_String::toDeleteTailCharacter($regionname);
        }

        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('Lease', $Lease);

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function del(){
        $LeaseObj = new Lease();
        $id = (int)Buddha_Http_Input::getParameter('id');
        $LeaseObj->del($id);
        $LeaseObj->delGelleryimage($id);
        $thumimg = array();
        if ($LeaseObj) {
            $thumimg['isok'] = 'true';
            $thumimg['data'] = '删除成功';
        } else {
            $thumimg['isok'] = 'false';
            $thumimg['data'] = '删除失败';
        }
        Buddha_Http_Output::makeJson($thumimg);
    }

    public function leasecat(){
        $LeasecatObj = new Leasecat();
        $fid = Buddha_Http_Input::getParameter('fid');

        $Db_Leasecat = $LeasecatObj->getLeasecatlist($fid);
        $datas = array();
        if ($Db_Leasecat) {
            $datas['isok'] = 'true';
            $datas['data'] = $Db_Leasecat;
        } else {
            $datas['isok'] = 'false';
            $datas['data'] = '';
        }
        Buddha_Http_Output::makeJson($datas);
    }

     public function ajaxadderr(){
         $RegionObj = new Region();
         $fid = Buddha_Http_Input::getParameter('fid');
         if (!$fid) {
             $fid = '1';
         }
         $Db_Region = $RegionObj->getFiledValues(array('id', 'immchildnum', 'name', 'father', 'level'), "father='{$fid}' and isdel=0");
         $datas = array();
         if ($Db_Region) {
             $datas['isok'] = 'true';
             $datas['data'] = $Db_Region;
         } else {
             $datas['isok'] = 'false';
             $datas['data'] = '';
         }
         Buddha_Http_Output::makeJson($datas);
     }

}