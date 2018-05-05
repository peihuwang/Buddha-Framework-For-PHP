<?php

/**
 * Class DemandController
 */
class DemandController extends Buddha_App_Action{

    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='需求';
        $this->tablename='demand';
    }

    public function index()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act=Buddha_Http_Input::getParameter('act');
        $uid = Buddha_Http_Cookie::getCookie('uid');

        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):0;
        $DemandcatObj=new Demandcat();
        if($act=='list'){
            $keyword=Buddha_Http_Input::getParameter('keyword');
                $where = "isdel=0 and user_id='{$uid}'";
                if($keyword){
                    $where.=" and name like '%{$keyword}%'";
                }

              //  $rcount = $this->db->countRecords( $this->prefix.'demand', $where);
                $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
                $pagesize = Buddha::$buddha_array['page']['pagesize'];
            /* $pcount = ceil($rcount/$pagesize);
           if($page > $pcount){
                $page=$pcount;
            }*/
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
            $orderby = " order by id DESC ";
            $list = $this->db->getFiledValues(array('id', 'name', 'demand_thumb', 'budget', 'demandcat_id','is_sure','buddhastatus'), $this->prefix . 'demand', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
            $UserObj= new User();
            $sure=$UserObj->getSingleFiledValues(array('groupid'),"id='{$uid}' and isdel=0");
            $Today=strtotime(date('Y-m-d'));//今天0点时间戳

            $CommonObj = new Common();
            $UsercommonObj = new Usercommon();

            foreach ($list as $k => $v)
            {
                $cat_id = $v['demandcat_id'];
                $Db_demandcat = $DemandcatObj->goods_thumbgoods_thumb($cat_id);
                if ($Db_demandcat) {
                    $cat_name = '';
                    foreach ($Db_demandcat as $k1 => $v1) {
                        $cat_name .= $v1['cat_name'] . ' > ';
                    }
                    $cat_name = Buddha_Atom_String::toDeleteTailCharacter($cat_name,2);
                }
                $jsondata[]=array(
                    'id'=>$v['id'],
                    'demand_thumb'=> Buddha_Atom_Dir::getformatDbStorageDir($v['demand_thumb']),
                    'name'=>$v['name'],
                    'budget'=>$v['budget'],
                    'user_id'=>$v['user_id'],
                    'buddhastatus'=>$v['buddhastatus'],
                    'issureimg'=>$UsercommonObj->businessissurestr($v['is_sure']),
                    'activity_thumb'=>$CommonObj->handleImgSlashByImgurl($v['activity_thumb']),
                    'is_sure'=>$v['is_sure'],
                    'cat_name'=>$cat_name,
                );
//==================================================================
            }
            $CommonObj = new Common();
            $Nws= $CommonObj->page_where($page,$jsondata,$pagesize);
            $datas['info'] = $Nws;

            /**↓↓↓↓↓↓↓↓↓↓↓ 置顶参数 ↓↓↓↓↓↓↓↓↓↓↓**/
            $datas['top']['good_table']=$this->tablename;
            $datas['top']['order_type']='info.top';
            $datas['top']['final_amt']=0.2;
            /**↑↑↑↑↑↑↑↑↑↑ 置顶参数 ↑↑↑↑↑↑↑↑↑↑**/

            if (is_array($list) and count($list) > 0) {
                $datas['isok'] = 'true';
                $datas['data'] = $jsondata;
            } else {
                $datas['isok'] = 'false';
                $datas['data'] = '没有数据了';
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
        $ShopObj=new Shop();
        $DemandObj=new Demand();
        $GalleryObj=new Gallery();
        $OrderObj=new Order();

        $num=$ShopObj->countRecords("isdel=0 and state=0 and is_sure=1 and user_id='{$uid}'");
        if($num==0){
            Buddha_Http_Head::redirectofmobile('您还没用创建店铺，或者店铺还未通过审核！','index.php?a=index&c=shop',2);
        }
		$title='需求';

        $name = Buddha_Http_Input::getParameter('name');
        $demandcat_id = Buddha_Http_Input::getParameter('demandcat_id');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $budget = Buddha_Http_Input::getParameter('budget');
        $demand_start_time = Buddha_Http_Input::getParameter('demand_start_time');
        $demand_end_time = Buddha_Http_Input::getParameter('demand_end_time');
        $keywords = Buddha_Http_Input::getParameter('keywords');
//        $Image = Buddha_Http_Input::getParameter('Image');
        //需求异地发布
        $is_remote = Buddha_Http_Input::getParameter('is_remote');
        $regionstr = Buddha_Http_Input::getParameter('regionstr');

        //描述、图片
        $demand_brief = Buddha_Http_Input::getParameter('demand_brief');
        $demand_desc = Buddha_Http_Input::getParameter('demand_desc');

        if(Buddha_Http_Input::isPost())
        {
            $data=array();
            $data['name']=$name;
            $data['user_id']=$uid;
            $data['demandcat_id']=$demandcat_id;
            $data['shop_id']=$shop_id;
            $data['budget']=$budget;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['demand_brief']=$demand_brief;
//            $data['demand_desc']=$demand_desc;
            $data['demand_start_time']=strtotime($demand_start_time);
            $data['demand_end_time']=strtotime($demand_end_time);

                $data['is_remote']=$is_remote;
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

                $demand_id = $DemandObj->add($data);
                $datas = array();
                if($demand_id)
                {
                    $table_name = Buddha_Http_Input::getParameter('c');
                    $MoreImage = Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/{$table_name}/{$demand_id}/", array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')->getAllReturnArray();

                    $MoregalleryObj = new Moregallery();
                    if(is_array($MoreImage) and count($MoreImage)>0){
                        $MoregalleryObj = new Moregallery();
                        $moregallery_id = $MoregalleryObj->pcaddimage('file',$MoreImage, $demand_id,$table_name,$uid);
                        $num = $DemandObj->setFirstGalleryImgToSupply($demand_id,$table_name,'file');
                    }
                    if($demand_desc){//富文本编辑器图片处理

                        $saveData = $MoregalleryObj->base_upload($demand_desc,$demand_id,$this->tablename);
                        $saveData = str_replace(PATH_ROOT,'/', $saveData);
                        $details['demand_desc'] = $saveData;
                        $DemandObj->edit($details,$demand_id);
                    }

                   //$remote为1表示发布异地产品添加订单
                    if($is_remote==1){
                        $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3','level4','level5'),"id='{$shop_id}' and user_id='{$uid}' and isdel=0");
                        $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($shop_id,0.2);
                        $data=array();
                        $data['good_id']=$demand_id;
                        $data['user_id']=$uid;
                        $data['order_sn']= $OrderObj->birthOrderId($uid);
                        $data['good_table']='demand';
                        $data['referral_id']=$Db_referral['referral_id'];
                        $data['partnerrate']=$Db_referral['partnerrate'];
                        $data['agent_id']=$Db_referral['agent_id'];
                        $data['agentrate']=$Db_referral['agentrate'];
                        $data['pay_type']='third';
                        $data['order_type']='info.market';
                        $data['goods_amt'] = $getMoneyArrayFromShop['goods_amt'];
                        $data['final_amt'] = $getMoneyArrayFromShop['final_amt'];
                        $data['money_plat'] = $getMoneyArrayFromShop['money_plat'];
                        $data['money_agent'] = $getMoneyArrayFromShop['money_agent'];
                        $data['money_partner'] = $getMoneyArrayFromShop['money_partner'];
                        $data['payname']='微信支付';
                        $data['make_level0']=$Db_referral['level0'];
                        $data['make_level1']=$Db_referral['level1'];
                        $data['make_level2']=$Db_referral['level2'];
                        $data['make_level3']=$Db_referral['level3'];
                        $data['make_level4']=$Db_referral['level4'];
                        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                        $order_id=$OrderObj->add($data);
                        $datas['isok']='true';
                        $datas['data']='需求添加成功,去支付。';
                        $datas['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id;
                    }else{
                        $datas['isok']='true';
                        $datas['data']='需求添加成功';
                        $datas['url']='index.php?a=index&c=demand';
                    }
                }else{
                    $datas['isok']='false';
                    $datas['data']='需求添加失败';
                    $datas['url']='index.php?a=add&c=demand';

            }
                Buddha_Http_Output::makeJson($datas);
        }


        $getshoplistOption=$ShopObj->getShoplistOption($uid,0);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('title', $title);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit()
    {
        $DemandObj=new Demand();
        $DemandcatObj=new Demandcat();
        $OrderObj=new Order();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $GalleryObj=new Gallery();
        $MoregalleryObj=new Moregallery();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
          Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=demand',2);
        }
		$title='需求';
        $demand=$DemandObj->getSingleFiledValues('',"id='{$id}' and user_id='{$uid}'");
        if(!$demand){
            Buddha_Http_Head::redirectofmobile('您没有权限查看或商品已删除！','index.php?a=index&c=demand',2);
        }

        $name=Buddha_Http_Input::getParameter('name');

        $demandcat_id=Buddha_Http_Input::getParameter('demandcat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $budget=Buddha_Http_Input::getParameter('budget');
        $demand_start_time=Buddha_Http_Input::getParameter('demand_start_time');
        $demand_end_time=Buddha_Http_Input::getParameter('demand_end_time');
        $keywords=Buddha_Http_Input::getParameter('keywords');
        //需求异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        $regionstr=Buddha_Http_Input::getParameter('regionstr');

            //描述
        $demand_brief = Buddha_Http_Input::getParameter('demand_brief');
        $demand_desc = Buddha_Http_Input::getParameter('demand_desc');

            if(Buddha_Http_Input::isPost())
            {
                $data=array();
                $data['name']=$name;
                $data['user_id']=$uid;
                $data['demandcat_id']=$demandcat_id;
                $data['shop_id']=$shop_id;
                $data['budget']=$budget;
                $data['keywords']=$keywords;
                $data['demand_brief']=$demand_brief;
                $data['demand_desc']=$demand_desc;
                $data['demand_start_time']=strtotime($demand_start_time);
                $data['demand_end_time']=strtotime($demand_end_time);

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


                    $Db_Demand_num = $DemandObj->edit($data,$id);


                $MoreImage = Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/{$this->tablename}/{$id}/", array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')->getAllReturnArray();

                $MoregalleryObj = new Moregallery();

                if(Buddha_Atom_Array::isValidArray($MoreImage))
                {

                    $moregallery_id = $MoregalleryObj->pcaddimage('file',$MoreImage, $id,$this->tablename,$uid);

                    if(count($moregallery_id)>0)
                    {
                        $num = $DemandObj->setFirstGalleryImgToSupply($id,$this->tablename,'file');

                        if($num==0){
                            $datas['err'] = 5;
                        }
                    }else{
                        $datas['err'] = 6;
                    }
                }
                if($demand_desc){//富文本编辑器图片处理
                    $dirs = PATH_ROOT."storage/quill/{$this->tablename}/{$id}/";
                    if(is_dir($dirs)){
                        if ($dh = opendir($dirs)){
                            while (($file = readdir($dh)) !== false){
                                //$filePath = $dirs.$file;
                                if(!strstr($demand_desc,$file) and $file != '.' and $file !='..'){
                                    @unlink($dirs.$file);//删除修改后的图片
                                    /*echo $file;
                                    exit;*/
                                }
                            }
                        }
                    }
                    $saveData = $MoregalleryObj->base_upload($demand_desc,$id);//base64图片上传
                    if($saveData){
                        $saveData = str_replace(PATH_ROOT,'/', $saveData);//替换
                        $details['demand_desc'] = $saveData;
                    }else{
                        $details['demand_desc'] = $demand_desc;
                    }
                    $DemandObj->edit($details,$id);//更新数据
                }


                if($DemandObj){
                    $datas = array();
                    //$is_remote为1表示发布异地产品添加订单
                    if($is_remote==1){
                        $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3','level4','level5'),"id='{$shop_id}' and user_id='{$uid}' and isdel=0");
                        $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($id,0.2);
                        $data=array();
                        $data['good_id']=$id;
                        $data['user_id']=$uid;
                        $data['order_sn']= $OrderObj->birthOrderId($uid);
                        $data['good_table']='demand';
                        $data['referral_id']=$Db_referral['referral_id'];
                        $data['partnerrate']=$Db_referral['partnerrate'];
                        $data['agent_id']=$Db_referral['agent_id'];
                        $data['agentrate']=$Db_referral['agentrate'];
                        $data['pay_type']='third';
                        $data['order_type']='info.market';
                        $data['payname']='微信支付';
                        $data['goods_amt'] = $getMoneyArrayFromShop['goods_amt'];
                        $data['final_amt'] = $getMoneyArrayFromShop['final_amt'];
                        $data['money_plat'] = $getMoneyArrayFromShop['money_plat'];
                        $data['money_agent'] = $getMoneyArrayFromShop['money_agent'];
                        $data['money_partner'] = $getMoneyArrayFromShop['money_partner'];
                        $data['make_level0']=$Db_referral['level0'];
                        $data['make_level1']=$Db_referral['level1'];
                        $data['make_level2']=$Db_referral['level2'];
                        $data['make_level3']=$Db_referral['level3'];
                        $data['make_level4']=$Db_referral['level4'];
                        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                        $order_id= $OrderObj->add($data);

                        $datas['isok']='true';
                        $datas['data']='需求编辑成功,去支付。';
                        $datas['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id;
                    }else{
                        $datas['isok']='true';
                        $datas['data']='需求编辑成功';
                        $datas['url']='index.php?a=index&c=demand';
                    }
                }else{
                    $datas['isok']='false';
                    $datas['data']='需求编辑失败';
                    $datas['url']='index.php?a=edit&c=demand';
                }
                Buddha_Http_Output::makeJson($datas);
            }



        $getshoplistOption=$ShopObj->getShoplistOption($uid,$demand['shop_id']);
        $Demandcat = $DemandcatObj->goods_thumbgoods_thumb($demand['demandcat_id']);
        if($Demandcat)
        {
            $cat_name='';
            foreach ($Demandcat as $k=>$v)
            {
                $cat_name.=$v['cat_name'].' > ';
            }
            $demand['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat_name);
        }
      //区域名称拼接
        $Region_name=$RegionObj->getAllArrayAddressByLever($demand['level3']);
        if($Region_name){
        $regionname='';
        foreach($Region_name as $k=>$v){
            if($k!=0){
                $regionname.=$v['name'].' > ';
            }
        }
            $demand['region_name']=Buddha_Atom_String::toDeleteTailCharacter($regionname);
        }

        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('demand', $demand);
        $Top=$OrderObj->getFiledValues(array('final_amt','createtime'),"isdel=0 and good_id='{$id}' and order_type='info.top' and pay_status=1 and user_id='{$uid}'");
        if(count($Top)>0){
            foreach ($Top as $k=>$v){
                $Top[$k]['name']=$demand['demand_name'];
            }
        }
        //产品相册
        $gimages = $MoregalleryObj->getEditGoodsImage($this->tablename,$id,$uid);


        $this->smarty->assign('gimages', $gimages);

        $this->smarty->assign('Top', $Top);
		$infotop=array('id'=>$id,'good_table'=>'demand','order_type'=>'info.top','final_amt'=>'0.2');
        $this->smarty->assign('title', $title);
        $this->smarty->assign('infotop', $infotop);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del()
    {
        $id=(int)Buddha_Http_Input::getParameter('id');
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        //相册删除并且信息删除
        $UsercommonObj=new Usercommon();
        $Db_Usercommon = $UsercommonObj->photoalbumDel('moregallery',$this->tablename,$id,$uid);

        $thumimg=array();

        if($Db_Usercommon){
            $thumimg['isok']='true';
            $thumimg['data']='删除成功';
        }else{
            $thumimg['isok']='false';
            $thumimg['data']='删除失败';
        }
        Buddha_Http_Output::makeJson($thumimg);
    }



    public function demandcat(){
       $DemandcatObj=new Demandcat();
        $fid = Buddha_Http_Input::getParameter('fid');
        $Db_Demand= $DemandcatObj->getDemandcatlist($fid);
        $datas = array();
        if($Db_Demand){
            $datas['isok']='true';
            $datas['data']=$Db_Demand;
        }else{
            $datas['isok']='false';
            $datas['data']='';
        }
        Buddha_Http_Output::makeJson($datas);

    }

    public function ajaxadderr(){
         $RegionObj=new Region();
         $fid = Buddha_Http_Input::getParameter('fid');
         if(!$fid){$fid='1';}
         $Db_Region= $RegionObj->getFiledValues(array('id','immchildnum','name','father','level'),"father='{$fid}' and isdel=0");
         $datas = array();
         if($Db_Region){
             $datas['isok']='true';
             $datas['data']=$Db_Region;
         }else{
             $datas['isok']='false';
             $datas['data']='';
         }
         Buddha_Http_Output::makeJson($datas);
     }


    public function fail(){
        $Demandbj=new Demand();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        $Db_shop=$Demandbj->getSingleFiledValues(array('remarks'),"isdel=0 and user_id='{$uid}' and id='{$id}'");
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
    public function delimage()
    {
        $MoregalleryObj = new Moregallery();
        $DemandObj=new Demand();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $thumimg = array();
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
            $DemandObj->setFirstGalleryImgToSupply($gimages['goods_id'],$this->tablename,'file');

            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }

        Buddha_Http_Output::makeJson($thumimg);
    }



    //上下架
    public function shelves()
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