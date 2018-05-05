<?php

/**
 * Class LeaseController
 */
class LeaseController extends Buddha_App_Action{

    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='租赁';
        $this->tablename='lease';
    }

    public function index()
    {
        $LeasecatObj=new Leasecat();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act=Buddha_Http_Input::getParameter('act');
        $uid = Buddha_Http_Cookie::getCookie('uid');

//==================================================================
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):0;
//==================================================================
        if($act=='list'){
            $keyword=Buddha_Http_Input::getParameter('keyword');
                $where = "isdel=0 and user_id='{$uid}'";
                if($keyword){
                    $where.=" and lease_name like '%{$keyword}%'";
                }
                //$rcount = $this->db->countRecords( $this->prefix.'supply', $where);
                $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
                $pagesize = Buddha::$buddha_array['page']['pagesize'];
            /* $pcount = ceil($rcount/$pagesize);
           if($page > $pcount){
                $page=$pcount;
            }*/
//==================================================================
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
                }
            }
//==================================================================
            $orderby = " order by id DESC ";
            $list = $this->db->getFiledValues('', $this->prefix . 'lease', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));

            $UserObj= new User();
            $sure=$UserObj->getSingleFiledValues(array('groupid'),"id='{$uid}' and isdel=0");

            $CommonObj = new Common();
            $UsercommonObj = new Usercommon();

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

                $list[$k]['issureimg'] = $UsercommonObj->businessissurestr($v['is_sure']);

                $list[$k]['lease_img'] =$CommonObj->handleImgSlashByImgurl($v['lease_img']);

                $list[$k]['pay']=trim($v['pay']);
            }
            $CommonObj = new Common();
            $Nws= $CommonObj->page_where($page,$list,$pagesize);
            $datas['info']=$Nws;

            /**↓↓↓↓↓↓↓↓↓↓↓ 置顶参数 ↓↓↓↓↓↓↓↓↓↓↓**/
            $datas['top']['good_table']=$this->tablename;
            $datas['top']['order_type']='info.top';
            $datas['top']['final_amt']=0.2;
            /**↑↑↑↑↑↑↑↑↑↑ 置顶参数 ↑↑↑↑↑↑↑↑↑↑**/

            if (is_array($list) and count($list) > 0)
            {
                $datas['isok'] = 'true';
                $datas['list'] = $list;
            } else {
                $datas['isok'] = 'false';
                $datas['list'] = '没有了';
            }
            Buddha_Http_Output::makeJson($datas);
        }

        $this->smarty->assign('view', $view);
        $this->smarty->assign('c', $this->tablename);
        $this->smarty->assign('title', $this->tablenamestr);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function add()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $OrderObj=new Order();
        $LeaseObj=new Lease();
        $ShopObj=new Shop();
        $GalleryObj=new Gallery();
        $num=$ShopObj->countRecords("isdel=0 and state=0 and is_sure=1 and user_id='{$uid}'");
        if($num==0){
            Buddha_Http_Head::redirectofmobile('您还没用创建店铺，或者店铺还未通过审核！','index.php?a=index&c=shop',2);
        }

		$title='租赁';	

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

        //描述
        $lease_brief = Buddha_Http_Input::getParameter('lease_brief');
        $lease_desc = Buddha_Http_Input::getParameter('lease_desc');


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
//            $data['lease_desc']=$lease_desc;
            $data['lease_start_time']=strtotime($lease_start_time);
            $data['lease_end_time']=strtotime($lease_end_time);

            if($regionstr){
                $level = explode(",", $regionstr);
                $data['is_remote']=0;
                $data['level0']=1;
                $data['level1']=$level[0];
                $data['level2']=$level[1];
                $data['level3']=$level[2];
            }else{
                $Db_level = $ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                $data['is_remote']=0;
                $data['level0']=$Db_level['level0'];
                $data['level1']=$Db_level['level1'];
                $data['level2']=$Db_level['level2'];
                $data['level3']=$Db_level['level3'];
            }

            $lease_id = $LeaseObj->add($data);

            $datas = array();

            if($lease_id)
            {
                $MoreImage = Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/{$this->tablename}/{$lease_id}/", array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')->getAllReturnArray();

                if(is_array($MoreImage) and count($MoreImage)>0){
                    $MoregalleryObj = new Moregallery();
                    $moregallery_id = $MoregalleryObj->pcaddimage('file',$MoreImage, $lease_id,$this->tablename,$uid);
                    $num = $LeaseObj->setFirstGalleryImgToSupply($lease_id,$this->tablename,'file');
                }
                if($lease_desc){//富文本编辑器图片处理
                    $saveData = $MoregalleryObj->base_upload($lease_desc,$lease_id,$this->tablename);
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);
                    $details['lease_desc'] = $saveData;
                    $LeaseObj->edit($details,$lease_id);
                }
               //$remote为1表示发布异地产品添加订单
                if($is_remote==1){
                    $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3'),"id='{$shop_id}' and user_id='{$uid}' and isdel=0");
                    $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($lease_id,0.2);
                    $data=array();
                    $data['good_id']=$lease_id;
                    $data['user_id']=$uid;
                    $data['order_sn']= $OrderObj->birthOrderId($uid);
                    $data['good_table']='lease';
                    $data['pay_type']='third';
                    $data['good_table'] = 'info.market';
                    $data['referral_id']=$Db_referral['referral_id'];
                    $data['partnerrate']=$Db_referral['partnerrate'];
                    $data['agent_id']=$Db_referral['agent_id'];
                    $data['agentrate']=$Db_referral['agentrate'];
                    $data['goods_amt'] = $getMoneyArrayFromShop['goods_amt'];
                    $data['final_amt'] = $getMoneyArrayFromShop['final_amt'];
                    $data['money_plat'] = $getMoneyArrayFromShop['money_plat'];
                    $data['money_agent'] = $getMoneyArrayFromShop['money_agent'];
                    $data['money_partner'] = $getMoneyArrayFromShop['money_partner'];
                    $data['payname'] = '微信支付';
                    $data['make_level0']=$Db_referral['level0'];
                    $data['make_level1']=$Db_referral['level1'];
                    $data['make_level2']=$Db_referral['level2'];
                    $data['make_level3']=$Db_referral['level3'];
                    $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                    $order_id= $OrderObj->add($data);
                    $datas['isok']='true';
                    $datas['data']='添加成功,去支付。';
                    $datas['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id;
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
	    $this->smarty->assign('title', $title);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    /**
     *
     */
    public function edit(){
        $OrderObj=new Order();
        $LeaseObj=new Lease();
        $LeasecatObj=new Leasecat();
        $GalleryObj=new Gallery();
        $RegionObj=new Region();
        $ShopObj=new Shop();
        $OrderObj=new Order();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
		$title='租赁';	

        if(!$id){
          Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=lease',2);
        }
        $Lease=$LeaseObj->getSingleFiledValues('',"id='{$id}' and user_id='{$uid}'");
        if(!$Lease){
            Buddha_Http_Head::redirectofmobile('您没有权限查看或已删除！','index.php?a=index&c=lease',2);
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
//                $data['lease_desc']=$lease_desc;
                $data['lease_start_time']=strtotime($lease_start_time);
                $data['lease_end_time']=strtotime($lease_end_time);

                    if($regionstr){
                        $level = explode(",", $regionstr);
                        $data['is_remote']=0;
                        $data['level0']=1;
                        $data['level1']=$level[0];
                        $data['level2']=$level[1];
                        $data['level3']=$level[2];
                    }else{
                        $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                        $data['is_remote']=0;
                        $data['level0']=$Db_level['level0'];
                        $data['level1']=$Db_level['level1'];
                        $data['level2']=$Db_level['level2'];
                        $data['level3']=$Db_level['level3'];
                    }

                $Db_Demand_num = $LeaseObj->edit($data,$id);

                $MoreImage = Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/{$this->tablename}/{$id}/", array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')->getAllReturnArray();


                $MoregalleryObj = new Moregallery();
                if(is_array($MoreImage) and count($MoreImage)>0){
                    $moregallery_id = $MoregalleryObj->pcaddimage('file',$MoreImage, $id,$this->tablename,$uid);

                    if(count($moregallery_id)>0){
                        $num = $LeaseObj->setFirstGalleryImgToSupply($id,$this->tablename,'file');

                        if($num==0){
                            $datas['err'] = 5;
                        }
                    }else{
                        $datas['err'] = 6;
                    }
                }
                if($lease_desc){//富文本编辑器图片处理
                    $dirs = PATH_ROOT."storage/quill/{$this->tablename}/{$id}/";
                    if(is_dir($dirs)){
                        if ($dh = opendir($dirs)){
                            while (($file = readdir($dh)) !== false){
                                //$filePath = $dirs.$file;
                                if(!strstr($lease_desc,$file) and $file != '.' and $file !='..'){
                                    @unlink($dirs.$file);//删除修改后的图片
                                    /*echo $file;
                                    exit;*/
                                }
                            }
                        }
                    }
                    $saveData = $MoregalleryObj->base_upload($lease_desc,$id);//base64图片上传
                    if($saveData){
                        $saveData = str_replace(PATH_ROOT,'/', $saveData);//替换
                        $details['lease_desc'] = $saveData;
                    }else{
                        $details['lease_desc'] = $lease_desc;
                    }
                    $LeaseObj->edit($details,$id);//更新数据
                }

                $datas = array();

                if($LeaseObj){
                    //$is_remote为1表示发布异地产品添加订单
                    if($is_remote==1){
                        $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3'),"id='{$shop_id}' and user_id='{$uid}' and isdel=0");
                        $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($id,0.2);
                        $data = array();
                        $data['good_id'] = $id;
                        $data['user_id'] = $uid;
                        $data['order_sn'] = $OrderObj->birthOrderId($uid);
                        $data['good_table'] = 'lease';
                        $data['pay_type']='third';
                        $data['good_table'] = 'info.market';
                        $data['referral_id']=$Db_referral['referral_id'];
                        $data['partnerrate']=$Db_referral['partnerrate'];
                        $data['agent_id']=$Db_referral['agent_id'];
                        $data['agentrate']=$Db_referral['agentrate'];
                        $data['payname'] = '微信支付';
                        $data['goods_amt'] = $getMoneyArrayFromShop['goods_amt'];
                        $data['final_amt'] = $getMoneyArrayFromShop['final_amt'];
                        $data['money_plat'] = $getMoneyArrayFromShop['money_plat'];
                        $data['money_agent'] = $getMoneyArrayFromShop['money_agent'];
                        $data['money_partner'] = $getMoneyArrayFromShop['money_partner'];
                        $data['make_level0']=$Db_referral['level0'];
                        $data['make_level1']=$Db_referral['level1'];
                        $data['make_level2']=$Db_referral['level2'];
                        $data['make_level3']=$Db_referral['level3'];
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        $order_id=$OrderObj->add($data);

                        $datas['isok']='true';
                        $datas['data']='编辑成功,去支付。';
                        $datas['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id;
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
                    $regionname .= $v['name'] . ' > ';
                }
            }
            $Lease['region_name'] = Buddha_Atom_String::toDeleteTailCharacter($regionname);
        }

        $this->smarty->assign('Lease', $Lease);
        $getshoplistOption=$ShopObj->getShoplistOption($uid,$Lease['shop_id']);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);

        //详细置顶
        $Top=$OrderObj->getFiledValues(array('final_amt','createtime'),"isdel=0 and good_id='{$id}' and order_type='info.top' and pay_status=1 and user_id='{$uid}'");
        if(count($Top)>0){
            foreach ($Top as $k=>$v){
                $Top[$k]['name']=$Lease['Lease_name'];
            }
        }
        $MoregalleryObj = new Moregallery();
        //产品相册
        $gimages = $MoregalleryObj->getEditGoodsImage($this->tablename,$id,$uid);

        $this->smarty->assign('gimages', $gimages);



        $this->smarty->assign('Top', $Top);
		$infotop=array('id'=>$id,'good_table'=>'Lease','order_type'=>'info.top','final_amt'=>'0.2');

        $this->smarty->assign('infotop', $infotop);
        $this->smarty->assign('title', $title);

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function del()
    {
        $id = (int)Buddha_Http_Input::getParameter('id');
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        //相册删除并且信息删除
        $UsercommonObj=new Usercommon();
        $Db_Usercommon = $UsercommonObj->photoalbumDel('moregallery',$this->tablename,$id,$uid);

        $thumimg=array();
        if($Db_Usercommon){
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

    public function fail(){
        $LeaseObj=new Lease();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        $Db_shop=$LeaseObj->getSingleFiledValues(array('remarks'),"isdel=0 and user_id='{$uid}' and id='{$id}'");
        $failinfo=array();
        if($Db_shop){
            $failinfo['isok']=0;
            $failinfo['remarks']=$Db_shop['remarks'];
        }else{
            $failinfo['isok']=1;
            $failinfo['data']='错误';
            $failinfo['remarks']='';
        }
        Buddha_Http_Output::makeJson($failinfo);
    }



    //相册图片删除
    public  function delimage()
    {
        $MoregalleryObj=new Moregallery();
        $LeaseObj=new Lease();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $thumimg=array();
        if(!$id){
            $thumimg['isok']='false';
            $thumimg['data']='参数错误';
        }
        $gimages = $MoregalleryObj->fetch($id);
        if ($gimages and $gimages['isdefault']==0)
        {
            $MoregalleryObj->del($id);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }else{
            $MoregalleryObj->del($id);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            $LeaseObj->setFirstGalleryImgToSupply($gimages['goods_id']);
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }

        Buddha_Http_Output::makeJson($thumimg);
    }



    //上下架
    public  function shelves()
    {
        $id = (int)Buddha_Http_Input::getParameter('id');
        $thumimg = array();
        $UsercommonObj = new Usercommon();
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $Db_Usercommon = $UsercommonObj->businessshelf($this->tablename,$id,$uid);
        if($Db_Usercommon['is_ok']==1)
        {
            $isok = 'true';
        }else{
            $isok = 'false';
        }

        $thumimg['id'] = $id;
        $thumimg['isok'] = $isok;
        $thumimg['data'] = $Db_Usercommon['is_msg'];
        $thumimg['buttonname'] = $Db_Usercommon['buttonname'];

        Buddha_Http_Output::makeJson($thumimg);
    }









}