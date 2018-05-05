<?php

/**
 * Class SupplyController
 */
class SupplyController extends Buddha_App_Action{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $SupplycatObj=new Supplycat();
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $where = " (isdel=0 or isdel=4) and user_id='{$uid}'";
        if($keyword){
            $where.=" and goods_name like '%$keyword%'";
        }

        $rcount = $this->db->countRecords( $this->prefix.'supply', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by add_time DESC ";
        $fields=array('*');
        $list = $this->db->getFiledValues ($fields, $this->prefix.'supply', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=supply&', $pagesize );

        foreach($list as $k=>$v){
            $Supplycat=$SupplycatObj->goods_thumbgoods_thumb($v['supplycat_id']);
            if($Supplycat) {
                $cat_name = '';
                foreach ($Supplycat as $k1 => $v1) {
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
        $SupplycatObj=new Supplycat();
        $GalleryObj=new Gallery();
        $ShopObj=new Shop();
        $SupplyObj=new Supply();
        $num=$ShopObj->countRecords("isdel=0 and state=0 and is_sure=1 and user_id='{$uid}'");
        if($num==0){
            Buddha_Http_Head::redirect('您还没用创建店铺，或者店铺还未通过审核！','index.php?a=index&c=shop',2);
        }

        $goods_name=Buddha_Http_Input::getParameter('good_name');
        $supplycat_id=Buddha_Http_Input::getParameter('supplycat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $goods_unit=Buddha_Http_Input::getParameter('goods_unit');
        $price=Buddha_Http_Input::getParameter('price');
        $keywords=Buddha_Http_Input::getParameter('keywords');


        //商品促销
        $is_promote=Buddha_Http_Input::getParameter('is_promote');
        $promote_price=Buddha_Http_Input::getParameter('promote_price');
        $promote_start_date=Buddha_Http_Input::getParameter('promote_start_date');
        $promote_end_date=Buddha_Http_Input::getParameter('promote_end_date');

        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        $level0=Buddha_Http_Input::getParameter('country');
        $level1=Buddha_Http_Input::getParameter('prov');
        $level2=Buddha_Http_Input::getParameter('city');
        $level3=Buddha_Http_Input::getParameter('area');

        //描述、图片
        $goods_brief=Buddha_Http_Input::getParameter('goods_brief');
        $goods_desc=Buddha_Http_Input::getParameter('content');


        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['goods_name']=$goods_name;
            $data['user_id']=$uid;
            $data['goods_sn']=date('ymdmis',time()).rand(10000,99999);
            $data['supplycat_id']=$supplycat_id;
            $data['shop_id']=$shop_id;
            $data['goods_unit']=$goods_unit;
            $data['market_price']=$price;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['goods_brief']=$goods_brief;
            $data['goods_desc']=$goods_desc;

            if ($is_promote == 1) {
                $data['is_promote'] = $is_promote;
                $data['promote_price'] = $promote_price;
                $data['promote_start_date'] = strtotime($promote_start_date);
                $data['promote_end_date'] = strtotime($promote_end_date);
            }

            if($is_remote==1){
                $data['is_remote']=0;
                $data['level0']=$level0;
                $data['level1']=$level1;
                $data['level2']=$level2;
                $data['level3']=$level3;
            }else{
                $data['is_remote']=0;
                $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                $data['level0']=$Db_level['level0'];
                $data['level1']=$Db_level['level1'];
                $data['level2']=$Db_level['level2'];
                $data['level3']=$Db_level['level3'];
            }

           $good_id = $SupplyObj->add($data);
           $jsondata=array();

            if($good_id){
                $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/supply/{$good_id}/",
                    array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                    ->getAllReturnArray();
                if(is_array($MoreImage) and count($MoreImage)>0) {
                    $GalleryObj->pcaddimage($MoreImage, $good_id);
                    $SupplyObj->setFirstGalleryImgToSupply($good_id);
                }
                $jsondata['id'] =$good_id;
                $jsondata['errcode'] = 0;
                $jsondata['errmsg'] = "OK";
                Buddha_Http_Output::makeJson($jsondata);
            }else{
                $jsondata['id'] =$good_id;
                $jsondata['errcode'] =1;
                $jsondata['errmsg'] = "err";
                Buddha_Http_Output::makeJson($jsondata);
            }
        }

        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>'', 'width' => '100', 'height' => 500 )
            ));



        $getCateOption=$SupplycatObj->getOption();
        $getshoplistOption=$ShopObj->getShoplistOption($uid,0);
        $gettableOption=$SupplycatObj->getunitOption();
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('getCateOption', $getCateOption);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }



    public function edit(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $SupplyObj=new Supply();
        $SupplycatObj=new Supplycat();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $GalleryObj=new Gallery();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirect('参数错误',"index.php?a=index&c=supply");
        }
        $supply=$SupplyObj->fetch($id);
        if(!$supply){
            Buddha_Http_Head::redirect('信息存在',"index.php?a=index&c=supply");
        }

        $goods_name=Buddha_Http_Input::getParameter('good_name');
        $supplycat_id=Buddha_Http_Input::getParameter('supplycat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $goods_unit=Buddha_Http_Input::getParameter('goods_unit');
        $price=Buddha_Http_Input::getParameter('price');
        $keywords=Buddha_Http_Input::getParameter('keywords');

        //商品促销
        $is_promote=Buddha_Http_Input::getParameter('is_promote');
        $promote_price=Buddha_Http_Input::getParameter('promote_price');
        $promote_start_date=Buddha_Http_Input::getParameter('promote_start_date');
        $promote_end_date=Buddha_Http_Input::getParameter('promote_end_date');

        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        $level0=Buddha_Http_Input::getParameter('country');
        $level1=Buddha_Http_Input::getParameter('prov');
        $level2=Buddha_Http_Input::getParameter('city');
        $level3=Buddha_Http_Input::getParameter('area');

        //描述、图片
        $goods_brief=Buddha_Http_Input::getParameter('goods_brief');
        $goods_desc=Buddha_Http_Input::getParameter('content');


        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['goods_name']=$goods_name;
            $data['supplycat_id']=$supplycat_id;
            $data['shop_id']=$shop_id;
            $data['goods_unit']=$goods_unit;
            $data['market_price']=$price;
            $data['keywords']=$keywords;
            $data['goods_brief']=$goods_brief;
            $data['goods_desc']=$goods_desc;

            if ($is_promote == 1) {
                $data['is_promote'] = $is_promote;
                $data['promote_price'] = $promote_price;
                $data['promote_start_date'] = strtotime($promote_start_date);
                $data['promote_end_date'] = strtotime($promote_end_date);
            }
            if($is_remote==1){
                $data['is_remote']=0;
                $data['level0']=$level0;
                $data['level1']=$level1;
                $data['level2']=$level2;
                $data['level3']=$level3;
            }else{
                $data['is_remote']='0';
                $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                $data['level0']=$Db_level['level0'];
                $data['level1']=$Db_level['level1'];
                $data['level2']=$Db_level['level2'];
                $data['level3']=$Db_level['level3'];
            }
            $SupplyObj->edit($data,$id);
            $jsondata=array();
           if($SupplyObj){
                $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/supply/{$id}/",
                    array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                    ->getAllReturnArray();
              if(is_array($MoreImage) and count($MoreImage)>0){
                $GalleryObj->pcaddimage($MoreImage, $id);
                $SupplyObj->setFirstGalleryImgToSupply($id);
              }
               $jsondata['id'] =$id;
               $jsondata['errcode'] = 0;
               $jsondata['errmsg'] = "OK";
               Buddha_Http_Output::makeJson($jsondata);
            }else{
               $jsondata['id'] =$id;
               $jsondata['errcode'] = 1;
               $jsondata['errmsg'] = "err";
               Buddha_Http_Output::makeJson($jsondata);
            }
        }


        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>$supply['goods_desc'], 'width' => '100', 'height' => 500 )
            ));

        //相册
        $gallery=$GalleryObj->getFiledValues(array('id','goods_thumb','isdefault'),"isdel=0 and goods_id='{$id}' order by isdefault DESC");
        $getCateOption=$SupplycatObj->getOption($supply['supplycat_id']);
        $getshoplistOption=$ShopObj->getShoplistOption($uid,$supply['shop_id']);
        $gettableOption=$SupplycatObj->getunitOption($supply['goods_unit']);
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('getCateOption', $getCateOption);
        if($supply['is_remote'] ==1){
        $country = $RegionObj->getChildlist(0);
        $prov = $RegionObj->getOptionOfRegionByLevel($supply['level0'],1);
        $city = $RegionObj->getOptionOfRegionByLevel($supply['level1'],2);
        $area = $RegionObj->getOptionOfRegionByLevel($supply['level2'],3);
        $this->smarty->assign('country',$country);
        $this->smarty->assign('prov',$prov);
        $this->smarty->assign('city',$city);
        $this->smarty->assign('area',$area);
        }
        $this->smarty->assign('supply',$supply);
        $this->smarty->assign('gallery',$gallery);



        //消息置顶
        $OrderObj=new Order();
        $Top=$OrderObj->getFiledValues(array('final_amt','createtime'),"isdel=0 and good_id='{$id}' and order_type='info.top' and pay_status=1 and user_id='{$uid}'");
        if(count($Top)>0){
            foreach ($Top as $k=>$v){
                $Top[$k]['name']=$supply['goods_name'];
            }
        }
        $this->smarty->assign('Top', $Top);


        $infotop=array('id'=>$supply['id'],'good_table'=>'supply','order_type'=>'info.top','final_amt'=>'0.2','pc'=>'1');
        $this->smarty->assign('infotop', $infotop);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function thumbimage(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $GalleryObj=new Gallery();
        $SupplyObj=new Supply();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if (!$id) {
            Buddha_Http_Head::redirect('参数错误',"index.php?a=index&c=supply");
        }
        $gimages = $GalleryObj->getSingleFiledValues ( '', "id='{$id}'" );
        $gid = $gimages ['goods_id'];
        if ($gimages) {
            $GalleryObj->updateRecords( array ('isdefault' => 0 ),"goods_id='{$gid}'");
            $GalleryObj->edit( array ('isdefault' => 1 ),$id);
            $data = array ();
            $data ['goods_img'] = $gimages ['goods_img'];
            $data ['goods_thumb'] = $gimages ['goods_thumb'];
            $data ['goods_large'] = $gimages ['goods_large'];
            $data ['sourcepic'] = $gimages ['sourcepic'];
            $SupplyObj->edit( $data,$id);
        }
        Buddha_Http_Head::redirect('默认图片设置成功',"index.php?a=index&c=supply");

    }

    public function delimage(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $GalleryObj=new Gallery();
        $SupplyObj=new Supply();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if (!$id) {
            Buddha_Http_Head::redirect('参数错误',"index.php?a=index&c=supply");
        }
        $gimages = $GalleryObj->getSingleFiledValues ( '', "id='{$id}'");
        if($gimages['isdefault']==1){
            $GalleryObj->del($gimages['id']);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            $SupplyObj->setFirstGalleryImgToSupply($gimages['goods_id']);
        }else{
            $this->del($gimages['id']);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
        }
        Buddha_Http_Head::redirect('图片删除成功',"index.php?a=index&c=supply");
    }


    public function del(){
        $GalleryObj=new Gallery();
        $SupplyObj=new Supply();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if (!$id) {
            Buddha_Http_Head::redirect('参数错误',"index.php?a=index&c=supply");
        }
       $Db_del= $SupplyObj->del($id);
        if($Db_del){
            $GalleryObj->delGelleryimage($id);
            Buddha_Http_Head::redirect('删除成功',"index.php?a=index&c=supply");
        }else{
            Buddha_Http_Head::redirect('删除失败',"index.php?a=index&c=supply");
        }

    }

    public function auditfailure(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $SupplyObj=new Supply();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $num=$SupplyObj->countRecords("id='{$id}' and user_id='{$uid}' and isdel=0 and buddhastatus=1 and is_sure=4");
         $data=array();
        if($num==0){
            $data=array(
                'errcode'=>'1',
                'errmsg'=>'err',
                'data'=>'数据错误，联系管理员',
            );
            Buddha_Http_Output::makeJson($data);
        }
       $remarks= $SupplyObj->getSingleFiledValues(array('remarks'),"id='{$id}' and user_id='{$uid}' and isdel=0 and buddhastatus=1 and is_sure=4");

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
        $RecruitObj=new Recruit();
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