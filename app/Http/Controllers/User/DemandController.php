<?php

/**
 * Class DemandController
 */
class DemandController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $DemandcatObj=new Demandcat();
        $act=Buddha_Http_Input::getParameter('act');
        if($act=='list'){
            $keyword=Buddha_Http_Input::getParameter('keyword');
                $where = "isdel=0 and user_id='{$uid}'";
                if($keyword){
                    $where.=" and name like '%{$keyword}%'";
                }
                $rcount = $this->db->countRecords( $this->prefix.'supply', $where);
                $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
                $pagesize = Buddha::$buddha_array['page']['pagesize'];
            /* $pcount = ceil($rcount/$pagesize);
           if($page > $pcount){
                $page=$pcount;
            }*/

                $orderby = " order by id DESC ";
                $list = $this->db->getFiledValues (array('id','name','demand_thumb','budget','demandcat_id'),  $this->prefix.'demand', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
            foreach ($list as $k=>$v){
                $cat_id=$v['demandcat_id'];
                $Db_demandcat=$DemandcatObj->goods_thumbgoods_thumb($cat_id);
                if($Db_demandcat){
                    $cat_name='';
                foreach($Db_demandcat as $k1=>$v1){
                    $cat_name.=$v1['cat_name'].' > ';
                }
                    $list[$k]['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat_name);
                }
            }

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
        $DemandObj=new Demand();
        $GalleryObj=new Gallery();
        $OrderObj=new Order();
        $UserObj=new User();
        $ShopObj=new Shop();
        $act=Buddha_Http_Input::getParameter('act');

        if($act=='demandadd'){

            $name=Buddha_Http_Input::getParameter('name');
            $demandcat_id=Buddha_Http_Input::getParameter('demandcat_id');
            $budget=Buddha_Http_Input::getParameter('budget');
            $demand_start_time=Buddha_Http_Input::getParameter('demand_start_time');
            $demand_end_time=Buddha_Http_Input::getParameter('demand_end_time');
            $keywords=Buddha_Http_Input::getParameter('keywords');

            //需求异地发布
            $is_remote=Buddha_Http_Input::getParameter('is_remote');
            $regionstr=Buddha_Http_Input::getParameter('regionstr');

            //描述、图片
            $brief=Buddha_Http_Input::getParameter('brief');
            $Image=Buddha_Http_Input::getParameter('Image');

            if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['name']=$name;
            $data['user_id']=$uid;
            $data['demandcat_id']=$demandcat_id;
            $data['budget']=$budget;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['demand_desc']=$brief;
            $data['demand_start_time']=strtotime($demand_start_time);
            $data['demand_end_time']=strtotime($demand_end_time);

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

              $demand_id=$DemandObj->add($data);

                $datas = array();
                if($demand_id){
                    if($Image){
                        if(base64_encode(base64_decode($Image))){
                            $imgurl= explode(',',$Image);
                            @mkdir(PATH_ROOT."storage/demand/".$demand_id.'/'); // 如果不存在则创建
                            $savePath ='storage/demand/'.$demand_id.'/';
                            if(!file_exists($savePath)){
                                @mkdir($savePath, 0777);
                            }
                            $base64_string = $imgurl[1];
                            $output_file = date('ymdhis',time()) . rand(11111, 99999) . '.jpg';
                            $filePath =PATH_ROOT.$savePath.$output_file;

                            $GalleryObj->resolveImageForRotate($filePath,$base64_string);

                            Buddha_Tool_File::thumbImage( $filePath, 320, 640, 'S_' );
                            Buddha_Tool_File::thumbImage( $filePath, 640, 640, 'M_' );
                            Buddha_Tool_File::thumbImage( $filePath, 1200, 640, 'L_' );
                            $data['demand_thumb'] = $savePath . 'S_' . $output_file;
                            $data['demand_img'] = $savePath . 'M_' . $output_file;
                            $data['demand_large'] = $savePath . 'L_' . $output_file;
                            $data['sourcepic'] = $savePath . $output_file;
                        }
                        $DemandObj->edit($data,$demand_id);
                    }
                   //$remote为1表示发布异地产品添加订单
                    if($is_remote==1){
                        $Db_referral=$UserObj->getSingleFiledValues(array('agent_id','agentrate'),"level3='{$UserInfo['level3']}' and isdel=0");
                        $money=0.2;
                        $money_agent=$Db_referral['agentrate']*$money/100;
                        $money_plat=$money-$money_agent;
                        $data=array();
                        $data['good_id']=$demand_id;
                        $data['user_id']=$uid;
                        $data['order_sn']= $OrderObj->birthOrderId($uid);
                        $data['good_table']='demand';
                        $data['referral_id']=0;
                        $data['partnerrate']=0;
                        $data['agent_id']=$Db_referral['agent_id'];
                        $data['agentrate']=$Db_referral['agentrate'];
                        $data['goods_amt'] = $money;
                        $data['final_amt'] =$money;
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
                        $order_id= $OrderObj->add($data);
                        $datas['isok']='true';
                        $datas['data']='需求添加成功,去支付。';
                        $datas['url']='/topay/wechat/example/jsapi.php?order_id='.$order_id;
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
        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        $DemandObj=new Demand();
        $DemandcatObj=new Demandcat();
        $RegionObj=new Region();
        $GalleryObj=new Gallery();
        $OrderObj=new Order();
        $UserObj=new User();
        $ShopObj=new Shop();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
          Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=demand',2);
        }
        $demand=$DemandObj->getSingleFiledValues('',"id='{$id}' and user_id='{$uid}'");
        if(!$demand){
            Buddha_Http_Head::redirectofmobile('商品已删除！','index.php?a=index&c=demand',2);
        }

        $act=Buddha_Http_Input::getParameter('act');

        if($act=='demandedit'){
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

            //描述、图片
            $brief=Buddha_Http_Input::getParameter('brief');
            $Image=Buddha_Http_Input::getParameter('Image');

            if(Buddha_Http_Input::isPost()){
                $data=array();
                $data['name']=$name;
                $data['user_id']=$uid;
                $data['demandcat_id']=$demandcat_id;
                $data['shop_id']=$shop_id;
                $data['budget']=$budget;
                $data['keywords']=$keywords;
                $data['demand_desc']=$brief;
                $data['demand_start_time']=strtotime($demand_start_time);
                $data['demand_end_time']=strtotime($demand_end_time);

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
                if($Image){
                    if (base64_encode(base64_decode($Image))) {
                        $imgurl = explode(',', $Image);
                        @mkdir(PATH_ROOT . "storage/demand/" . $id . '/'); // 如果不存在则创建
                        $savePath = 'storage/demand/' . $id . '/';
                        if (!file_exists($savePath)) {
                            @mkdir($savePath, 0777);
                        }
                        $base64_string = $imgurl[1];
                        $output_file = date('ymdhis', time()) . rand(11111, 99999) . '.jpg';
                        $filePath = PATH_ROOT . $savePath . $output_file;

                        $GalleryObj->resolveImageForRotate($filePath, $base64_string);

                        Buddha_Tool_File::thumbImage($filePath, 320, 640, 'S_');
                        Buddha_Tool_File::thumbImage($filePath, 640, 640, 'M_');
                        Buddha_Tool_File::thumbImage($filePath, 1200, 640, 'L_');
                        //删除图片
                        $DemandObj->deleteFIleOfPicture($id);

                        $data['demand_thumb'] = $savePath . 'S_' . $output_file;
                        $data['demand_img'] = $savePath . 'M_' . $output_file;
                        $data['demand_large'] = $savePath . 'L_' . $output_file;
                        $data['sourcepic'] = $savePath . $output_file;
                    }
                }
                $DemandObj->edit($data,$id);

                if($DemandObj){
                    $datas = array();
                    //$is_remote为1表示发布异地产品添加订单
                    if($is_remote==1){
                        $Db_referral=$UserObj->getSingleFiledValues(array('agent_id','agentrate'),"level3='{$UserInfo['level3']}' and isdel=0");
                        $money=0.2;
                        $money_agent=$Db_referral['agentrate']*$money/100;
                        $money_plat=$money-$money_agent;
                        $data=array();
                        $data['good_id']=$id;
                        $data['user_id']=$uid;
                        $data['order_sn']= $OrderObj->birthOrderId($uid);
                        $data['pay_type']='demand';
                        $data['referral_id']=0;
                        $data['partnerrate']=0;
                        $data['agent_id']=$Db_referral['agent_id'];
                        $data['agentrate']=$Db_referral['agentrate'];
                        $data['goods_amt'] = $money;
                        $data['final_amt'] = $money;
                        $data['money_plat'] = $money_plat;
                        $data['money_agent'] = $money_agent;
                        $data['money_partner'] = 0;
                        $data['payname']='微信支付';
                        $data['make_level0']=$UserInfo['level0'];
                        $data['make_level1']=$UserInfo['level1'];
                        $data['make_level2']=$UserInfo['level2'];
                        $data['make_level3']=$UserInfo['level3'];
                        $data['make_level4']=$UserInfo['level4'];
                        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                        $order_id=$OrderObj->add($data);

                        $datas['isok']='true';
                        $datas['data']='需求编辑成功,去支付。';
                        $datas['url']='/topay/wechat/example/jsapi.php?order_id='.$order_id;
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
        }


        $Demandcat=$DemandcatObj->goods_thumbgoods_thumb($demand['demandcat_id']);
        if($Demandcat){
        $cat_name='';
        foreach ($Demandcat as $k=>$v){
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
        $this->smarty->assign('demand', $demand);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        $DemandObj=new Demand();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $DemandObj->del($id);
        $DemandObj->deleteFIleOfPicture($id);
        $thumimg=array();
        if($DemandObj){
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
        $json = Buddha_Http_Input::getParameter('json');
        $json_arr =Buddha_Atom_Array::jsontoArray($json);
        $sub = $json_arr['fid'];
        if(!$sub){$sub='0';}
        $Db_Demand= $DemandcatObj->getDemandcatlist($sub);

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