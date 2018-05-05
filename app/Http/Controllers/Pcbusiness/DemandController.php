<?php

/**
 * Class DemandController
 */
class DemandController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $DemandcatObj=new Demandcat();
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $where = " (isdel=0 or isdel=4) and user_id='{$uid}'";
        if($keyword){
            $where.=" and name like '%$keyword%'";
        }
        $rcount = $this->db->countRecords( $this->prefix.'demand', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by add_time DESC ";
        $fields=array('*');
        $list = $this->db->getFiledValues ($fields, $this->prefix.'demand', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=demand&', $pagesize );
    foreach($list as $k=>$v){
        $demandcat=$DemandcatObj->goods_thumbgoods_thumb($v['demandcat_id']);
        if($demandcat) {
            $cat_name = '';
            foreach ($demandcat as $k1 => $v1) {
                $cat_name .= $v1['cat_name'] . ', ';
            }
        }
        $list[$k]['cat_name'] = Buddha_Atom_String::toDeleteTailCharacter($cat_name);
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
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $DemandObj=new Demand();
        $DemandcatObj=new Demandcat();
        $num=$ShopObj->countRecords("isdel=0 and state=0 and is_sure=1 and user_id='{$uid}'");
        if($num==0){
            Buddha_Http_Head::redirect('您还没用创建店铺，或者店铺还未通过审核！','index.php?a=index&c=shop',2);
        }

        $name = Buddha_Http_Input::getParameter('name');
        $demandcat_id = Buddha_Http_Input::getParameter('demandcat_id');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $budget = Buddha_Http_Input::getParameter('budget');
        $demand_start_time = Buddha_Http_Input::getParameter('demand_start_time');
        $demand_end_time = Buddha_Http_Input::getParameter('demand_end_time');
        $keywords = Buddha_Http_Input::getParameter('keywords');

        //需求异地发布
        $is_remote = Buddha_Http_Input::getParameter('is_remote');
        $regionstr = Buddha_Http_Input::getParameter('regionstr');

        //描述、图片
        $demand_brief = Buddha_Http_Input::getParameter('demand_brief');
        $demand_desc = Buddha_Http_Input::getParameter('content');


        if(Buddha_Http_Input::isPost()) {
            $data = array();
            $data['name'] = $name;
            $data['user_id'] = $uid;
            $data['demandcat_id'] = $demandcat_id;
            $data['shop_id'] = $shop_id;
            $data['budget'] = $budget;
            $data['keywords'] = $keywords;
            $data['add_time'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['demand_desc'] = $demand_desc;
            $data['demand_brief'] = $demand_brief;
            $data['demand_start_time'] = strtotime($demand_start_time);
            $data['demand_end_time'] = strtotime($demand_end_time);

            $data['is_remote'] = $is_remote;
            if ($regionstr) {
                $level = explode(",", $regionstr);
                $data['is_remote'] = 0;
                $data['level0'] = 1;
                $data['level1'] = $level[0];
                $data['level2'] = $level[1];
                $data['level3'] = $level[2];
            } else {
                $Db_level = $ShopObj->getSingleFiledValues(array('level0', 'level1', 'level2', 'level3'), "user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                $data['is_remote'] = 0;
                $data['level0'] = $Db_level['level0'];
                $data['level1'] = $Db_level['level1'];
                $data['level2'] = $Db_level['level2'];
                $data['level3'] = $Db_level['level3'];
            }

            $demand_id = $DemandObj->add($data);
            if ($demand_id) {
                $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/demand/{$demand_id}/",
                    array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                    ->getOneReturnArray();
                if ($Image) {
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 800, 800, 'L_');
                }
                $sourcepic = str_replace("storage/demand/{$demand_id}/", '', $Image);
                if ($Image) {
                    $data['demand_thumb'] = "storage/demand/{$demand_id}/S_" . $sourcepic;
                    $data['demand_img'] = "storage/demand/{$demand_id}/M_" . $sourcepic;
                    $data['demand_large'] = "storage/demand/{$demand_id}/L_" . $sourcepic;
                    $data['sourcepic'] = "storage/demand/{$demand_id}/" . $sourcepic;
                }
                $DemandObj->edit($data,$demand_id);
                Buddha_Http_Output::makeValue(1);
            }else{
                Buddha_Http_Output::makeValue(0);
            }
        }



        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>'', 'width' => '100', 'height' => 500 )
            ));
        $getCateOption=$DemandcatObj->getOption();
        $getshoplistOption=$ShopObj->getShoplistOption($uid,0);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('getCateOption', $getCateOption);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $DemandObj=new Demand();
        $ShopObj=new Shop();
        $DemandcatObj=new Demandcat();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirect('参数错误','index.php?a=index&c=demand');
        }
        $demand=$DemandObj->fetch($id);
        if(!$demand){
            Buddha_Http_Head::redirect('信息不存在','index.php?a=index&c=demand');
        }

        $name = Buddha_Http_Input::getParameter('name');
        $demandcat_id = Buddha_Http_Input::getParameter('demandcat_id');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $budget = Buddha_Http_Input::getParameter('budget');
        $demand_start_time = Buddha_Http_Input::getParameter('demand_start_time');
        $demand_end_time = Buddha_Http_Input::getParameter('demand_end_time');
        $keywords = Buddha_Http_Input::getParameter('keywords');

        //需求异地发布
        $is_remote = Buddha_Http_Input::getParameter('is_remote');
        $regionstr = Buddha_Http_Input::getParameter('regionstr');

        //描述、图片
        $demand_brief = Buddha_Http_Input::getParameter('demand_brief');
        $demand_desc = Buddha_Http_Input::getParameter('content');

        if(Buddha_Http_Input::isPost()) {
            $data = array();
            $data['name'] = $name;
            $data['user_id'] = $uid;
            $data['demandcat_id'] = $demandcat_id;
            $data['shop_id'] = $shop_id;
            $data['budget'] = $budget;
            $data['keywords'] = $keywords;
            $data['add_time'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['demand_desc'] = $demand_desc;
            $data['demand_brief'] = $demand_brief;
            $data['demand_start_time'] = strtotime($demand_start_time);
            $data['demand_end_time'] = strtotime($demand_end_time);

            $data['is_remote'] = $is_remote;
            if ($regionstr) {
                $level = explode(",", $regionstr);
                $data['is_remote'] = 0;
                $data['level0'] = 1;
                $data['level1'] = $level[0];
                $data['level2'] = $level[1];
                $data['level3'] = $level[2];
            } else {
                $Db_level = $ShopObj->getSingleFiledValues(array('level0', 'level1', 'level2', 'level3'), "user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                $data['is_remote'] = 0;
                $data['level0'] = $Db_level['level0'];
                $data['level1'] = $Db_level['level1'];
                $data['level2'] = $Db_level['level2'];
                $data['level3'] = $Db_level['level3'];
            }

             $DemandObj->edit($data,$id);
            if ($DemandObj) {
                $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/demand/{$id}/",
                    array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                    ->getOneReturnArray();
                if ($Image) {
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 800, 800, 'L_');
                }
                $sourcepic = str_replace("storage/demand/{$id}/", '', $Image);
                if ($Image) {
                    $data['demand_thumb'] = "storage/demand/{$id}/S_" . $sourcepic;
                    $data['demand_img'] = "storage/demand/{$id}/M_" . $sourcepic;
                    $data['demand_large'] = "storage/demand/{$id}/L_" . $sourcepic;
                    $data['sourcepic'] = "storage/demand/{$id}/" . $sourcepic;
                    $DemandObj->deleteFIleOfPicture($id);
                    $DemandObj->edit($data,$id);
                }

                Buddha_Http_Output::makeValue(1);
            }else{
                Buddha_Http_Output::makeValue(0);
            }
        }

        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>$demand['demand_desc'], 'width' => '100', 'height' => 500 )
            ));
        $getCateOption=$DemandcatObj->getOption($demand['demandcat_id']);
        $getshoplistOption=$ShopObj->getShoplistOption($uid,$demand['shop_id']);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('getCateOption', $getCateOption);
        $this->smarty->assign('demand', $demand);

        //消息置顶
        $OrderObj=new Order();
        $Top=$OrderObj->getFiledValues(array('final_amt','createtime'),"isdel=0 and good_id='{$id}' and order_type='info.top' and pay_status=1 and user_id='{$uid}'");
        if(count($Top)>0){
            foreach ($Top as $k=>$v){
                $Top[$k]['name']=$demand['demand_name'];
            }
        }
        $this->smarty->assign('Top', $Top);
        $infotop=array('id'=>$id,'good_table'=>'shop','order_type'=>'info.top','final_amt'=>'0.2','pc'=>'1');
        $this->smarty->assign('infotop', $infotop);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        $DemandObj=new Demand();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $DemandObj->del($id);
        $DemandObj->deleteFIleOfPicture($id);
        if($DemandObj){
            Buddha_Http_Head::redirect('删除成功','index.php?a=index&c=demand');
        }else{
            Buddha_Http_Head::redirect('删除失败','index.php?a=index&c=demand');
        }
    }

    public function auditfailure(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $DemandObj=new Demand();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $num=$DemandObj->countRecords("id='{$id}' and user_id='{$uid}' and isdel=0  and is_sure=4");

        if($num==0){
            $data=array(
                'errcode'=>'1',
                'errmsg'=>'err',
                'data'=>'数据错误，联系管理员',
            );
            Buddha_Http_Output::makeJson($data);
        }
        $remarks= $DemandObj->getSingleFiledValues(array('remarks'),"id='{$id}' and user_id='{$uid}' and isdel=0  and is_sure=4");

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
            $jsondata['errcode'] = 0;
            $jsondata['errmsg'] ='ok';
            Buddha_Http_Output::makeJson($jsondata);
        }
    }
}