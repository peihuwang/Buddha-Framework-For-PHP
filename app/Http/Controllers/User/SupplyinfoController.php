<?php

/**
 * Class SupplyinfoController
 */
class SupplyinfoController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act=Buddha_Http_Input::getParameter('act');
        if($act=='list'){
            $keyword=Buddha_Http_Input::getParameter('keyword');
                $where = "isdel=0 and user_id='{$uid}'";
                if($keyword){
                    $where.=" and goods_name like '%{$keyword}%' or goods_sn like '%{$keyword}%'";
                }
                $rcount = $this->db->countRecords( $this->prefix.'supply', $where);
                $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
                $pagesize = Buddha::$buddha_array['page']['pagesize'];
            /* $pcount = ceil($rcount/$pagesize);
           if($page > $pcount){
                $page=$pcount;
            }*/

                $orderby = " order by id DESC ";
                $list = $this->db->getFiledValues (array('id','goods_thumb','goods_name','promote_price','goods_sn'),  $this->prefix.'supply', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

            if(is_array($list) and count($list)>0){
                $datas['isok']='true';
                 $datas['data']=$list;
            }else{
                    $datas['isok']='false';
                    $datas['data']='没有了';
             }
                Buddha_Http_Output::makeJson($datas);
        }
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function add(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $SupplycatObj=new Supplycat();
        $SupplyObj=new Supply();
        $GalleryObj=new Gallery();
        $OrderObj=new Order();
        $UserObj=new User();
        $act=Buddha_Http_Input::getParameter('act');

        if($act=='goodsadd'){
        $goods_name=Buddha_Http_Input::getParameter('good_name');
        $supplycat_id=Buddha_Http_Input::getParameter('shopcat_id');
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
        $regionstr=Buddha_Http_Input::getParameter('regionstr');

        //描述、图片
        $brief=Buddha_Http_Input::getParameter('brief');
        $Image=Buddha_Http_Input::getParameter('Image');

            if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['goods_name']=$goods_name;
            $data['user_id']=$uid;
            $data['goods_sn']=date('ymdmis',time()).rand(11111,99999);
            $data['supplycat_id']=$supplycat_id;
            $data['goods_unit']=$goods_unit;
            $data['market_price']=$price;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['goods_desc']=$brief;

            if($is_promote==1){
             $data['is_promote']=$is_promote;
            $data['promote_price']=$promote_price;
            $data['promote_start_date']=strtotime($promote_start_date);
            $data['promote_end_date']=strtotime($promote_end_date);
            }


            if($is_remote==1){
                $level = explode(",", $regionstr);
                $data['is_remote']=$is_remote;
                $data['level0']=1;
                $data['level1']=$level[0];
                $data['level2']=$level[1];
                $data['level3']=$level[2];
                $Db_agent=$UserObj->getagent($UserInfo['leve3']);
                $agent_id=$Db_agent['id'];
                $agentrate=$Db_agent['agentrate'];
            }else{
                $data['is_remote']=0;
                $data['level0']=$UserInfo['level0'];
                $data['level1']=$UserInfo['level1'];
                $data['level2']=$UserInfo['level2'];
                $data['level3']=$UserInfo['level3'];
            }


              $good_id=$SupplyObj->add($data);
                $datas = array();
                if($good_id){
                      if(is_array($Image) and count($Image)){
                          $GalleryObj->addimage($Image,$good_id);
                      }
                     $SupplyObj->setFirstGalleryImgToSupply($good_id);
                   //$remote为1表示发布异地产品添加订单
                    if($is_remote==1){
                        $data=array();
                        $data['good_id']=$good_id;
                        $data['user_id']=$uid;
                        $data['order_sn']= $OrderObj->birthOrderId($uid);
                        $data['good_table']='shop';
                        $data['agent_id']=$agent_id;
                        $data['agentrate']=$agentrate;
                        $data['pay_type']='third';
                        $data['order_type']='info.top';
                        $data['goods_amt']='0.2';
                        $data['final_amt']='0.2';
                        $data['payname']='微信支付';
                        $data['level0']=1;
                        $data['level1']=$level[0];
                        $data['level2']=$level[1];
                        $data['level3']=$level[2];
                        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                        $OrderObj->add($data);
                        $datas['isok']='true';
                        $datas['data']='商品添加成功,去支付。';
                        $datas['url']='/topay/wechat/index.php';
                    }else{
                        $datas['isok']='true';
                        $datas['data']='商品添加成功';
                        $datas['url']='index.php?a=index&c=supplyinfo';
                    }
                }else{
                    $datas['isok']='false';
                    $datas['data']='商品添加失败';
                    $datas['url']='index.php?a=add&c=supplyinfo';

            }
                Buddha_Http_Output::makeJson($datas);
        }
        }

        $gettableOption=$SupplycatObj->getunitOption();
        $this->smarty->assign('gettableOption', $gettableOption);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        $SupplyObj=new Supply();
        $SupplycatObj=new Supplycat();
        $GalleryObj=new Gallery();
        $RegionObj=new Region();
        $OrderObj=new Order();
        $UserObj=new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
          Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=shop',2);
        }
        $goods=$SupplyObj->getSingleFiledValues('',"id='{$id}' and user_id='{$uid}'");
        if(!$goods){
            Buddha_Http_Head::redirectofmobile('商品已删除！','index.php?a=index&c=shop',2);
        }

        $act=Buddha_Http_Input::getParameter('act');
        if($act=='goodsedit'){
            $goods_name=Buddha_Http_Input::getParameter('good_name');
            $supplycat_id=Buddha_Http_Input::getParameter('shopcat_id');
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
            $regionstr=Buddha_Http_Input::getParameter('regionstr');

            //描述、图片
            $brief=Buddha_Http_Input::getParameter('brief');
            $Image=Buddha_Http_Input::getParameter('Image');

            if(Buddha_Http_Input::isPost()){
                $data=array();
                $data['goods_name']=$goods_name;
                $data['user_id']=$uid;
                $data['supplycat_id']=$supplycat_id;
                $data['goods_unit']=$goods_unit;
                $data['market_price']=$price;
                $data['keywords']=$keywords;
                $data['goods_desc']=$brief;

                if($is_promote==1){
                    $data['is_promote']=$is_promote;
                    $data['promote_price']=$promote_price;
                    $data['promote_start_date']=strtotime($promote_start_date);
                    $data['promote_end_date']=strtotime($promote_end_date);
                }
                if($is_remote==1){
                    $level = explode(",", $regionstr);
                    $data['is_remote']=$is_remote;
                    $data['level0']=1;
                    $data['level1']=$level[0];
                    $data['level2']=$level[1];
                    $data['level3']=$level[2];
                    $Db_agent=$UserObj->getagent($level[2]);
                    $agent_id=$Db_agent['id'];
                    $agentrate=$Db_agent['agentrate'];
                }else{
                    $data['is_remote']=0;
                    $data['level0']=$UserInfo['level0'];
                    $data['level1']=$UserInfo['level1'];
                    $data['level2']=$UserInfo['level2'];
                    $data['level3']=$UserInfo['level3'];
                }

                $SupplyObj->edit($data,$id);
                $datas = array();
                if($SupplyObj){
                    if(is_array($Image) and count($Image)){
                        $GalleryObj->addimage($Image,$id);
                        $SupplyObj->setFirstGalleryImgToSupply($id);
                    }
                    //$is_remote为1表示发布异地产品添加订单
                    if($is_remote==1){
                        $data=array();
                        $data['good_id']=$id;
                        $data['user_id']=$uid;
                        $data['order_sn']= $OrderObj->birthOrderId($uid);
                        $data['agent_id']=$agent_id;
                        $data['agentrate']=$agentrate;
                        $data['pay_type']='third';
                        $data['order_type']='info.top';
                        $data['goods_amt']='0.2';
                        $data['final_amt']='0.2';
                        $data['payname']='微信支付';
                        $data['level0']=1;
                        $data['level1']=$level[0];
                        $data['level2']=$level[1];
                        $data['level3']=$level[2];
                        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                        $OrderObj->add($data);

                        $datas['isok']='true';
                        $datas['data']='商品编辑成功,去支付。';
                        $datas['url']='/topay/wechat/index.php';
                    }else{
                        $datas['isok']='true';
                        $datas['data']='商品编辑成功';
                        $datas['url']='index.php?a=index&c=supplyinfo';
                    }
                }else{
                    $datas['isok']='false';
                    $datas['data']='商品编辑失败';
                    $datas['url']='index.php?a=edit&c=supplyinfo';
                }
                Buddha_Http_Output::makeJson($datas);
            }
        }


        //产品相册
        $gimages=$GalleryObj->getGoodsImage($id);
        $Supplycat=$SupplycatObj->goods_thumbgoods_thumb($goods['supplycat_id']);
        if($Supplycat){
        $cat_name='';
        foreach ($Supplycat as $k=>$v){
            $cat_name.=$v['cat_name'].' > ';
        }
        $goods['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat_name);
    }
      //区域名称拼接
        if($goods['is_remote']==1)
        $Region_name=$RegionObj->getAllArrayAddressByLever($goods['level3']);
        if($Region_name){
        $regionname='';
        foreach($Region_name as $k=>$v){
            if($k!=0){
                $regionname.=$v['name'].' > ';
            }
        }
        $goods['region_name']=Buddha_Atom_String::toDeleteTailCharacter($regionname);
        }
        $gettableOption=$SupplycatObj->getunitOption($goods['goods_unit']);
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('gimages', $gimages);
        $this->smarty->assign('goods', $goods);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        $GalleryObj=new Gallery();
        $SupplyObj=new Supply();
        $id=(int)Buddha_Http_Input::getParameter('id');
       $SupplyObj->del($id);
        $GalleryObj->delGelleryimage($id);
        $thumimg=array();
        if($SupplyObj){
            $thumimg['isok']='true';
            $thumimg['data']='删除成功';
        }else{
            $thumimg['isok']='false';
            $thumimg['data']='删除失败';
        }
        Buddha_Http_Output::makeJson($thumimg);
    }

    //相册图片删除
    public  function delimage(){
        $GalleryObj=new Gallery();
        $SupplyObj=new Supply();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $thumimg=array();
        if(!$id){
            $thumimg['isok']='false';
            $thumimg['data']='参数错误';
        }
        $gimages=$GalleryObj->fetch($id);
        if ($gimages and $gimages['isdefault']==0){
            $GalleryObj->del($id);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }else{
           $GalleryObj->del($id);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
         $SupplyObj->setFirstGalleryImgToSupply($gimages['goods_id']);
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }

        Buddha_Http_Output::makeJson($thumimg);
    }

    public function supplycat(){
       $SupplycatObj=new Supplycat();
        $json = Buddha_Http_Input::getParameter('json');
        $json_arr =Buddha_Atom_Array::jsontoArray($json);
        $sub = $json_arr['fid'];
        if(!$sub){$sub='0';}
        $Db_Shopcat= $SupplycatObj->getSupplycatlist($sub);

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



     public function ajaxadderr(){
         $RegionObj=new Region();
         $json = Buddha_Http_Input::getParameter('json');
         $json_arr =Buddha_Atom_Array::jsontoArray($json);
         $father = $json_arr['fid'];
         if(!$father){$father='1';}
         $Db_Region= $RegionObj->getFiledValues(array('id','immchildnum','name','father','level'),"father='{$father}' and isdel=0");
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






}