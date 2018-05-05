<?php

/**
 * Class LeaseController
 */
class LeaseController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));


        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if ($webface_access_token == '') {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444002, 'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num = $ApptokenObj->getTokenNum($webface_access_token);
        if ($num == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444003, 'webface_access_token不正确请从新获取');
        }
    }




    public function beforeadd(){

        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $ShopObj = new Shop();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','mobile','realname');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        $shop_id_list=$ShopObj->getUserShopArr($user_id,0);
        $jsondata['shop_id_list'] = $shop_id_list;
        $jsondata['area'] = array(
            'Services'=>'ajaxregion.getBelongFromFatherId',
            'param'=>array('father'=>1),
        );




        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '添加招聘之前的展示页面');
    }

    public function add(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','lease_name','leasecat_id', 'shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $ShopObj=new Shop();
        $UserObj = new User();
        $Leasecat = new Leasecat();
        $LeaseObj = new Lease();
        $RegionObj = new Region();
        $JsonimageObj = new Jsonimage();
        $lease_name=Buddha_Http_Input::getParameter('lease_name');
        $leasecat_id=Buddha_Http_Input::getParameter('leasecat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $rent=Buddha_Http_Input::getParameter('rent');
        $keywords=Buddha_Http_Input::getParameter('keywords');
        //商品促销
        $lease_start_time=Buddha_Http_Input::getParameter('lease_start_time');
        $lease_end_time=Buddha_Http_Input::getParameter('lease_end_time');
        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        //描述
        $lease_brief = Buddha_Http_Input::getParameter('lease_brief');
        $lease_desc = Buddha_Http_Input::getParameter('lease_desc');

        $level1 = Buddha_Http_Input::getParameter('level1');
        $level2 = Buddha_Http_Input::getParameter('level2');
        $level3 = Buddha_Http_Input::getParameter('level3');

        $image_arr=Buddha_Http_Input::getParameter('image_arr');


        if(Buddha_Atom_String::isJson($image_arr)){
            $image_arr = json_decode($image_arr);
        }

        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);

        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色');
        }


         if(!$Leasecat->isHasRecord($leasecat_id)){
             Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000034, '租赁分类不存在');

         }

        if($ShopObj->getShopOfSureToUserTotalInt($shop_id,$user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000028, '您还没用创建店铺，或者店铺还未通过审核！');
        }

        if(!$ShopObj->isShopBelongToUser($shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000029, '此店铺不属于当前用户');
        }

            $data=array();
            $data['lease_name']=$lease_name;
            $data['user_id']=$user_id;
            $data['leasecat_id']=$leasecat_id;
            $data['shop_id']=$shop_id;
            $data['rent']=$rent;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['lease_brief']=$lease_brief;
            $data['lease_desc']=$lease_desc;
            $data['lease_start_time']=strtotime($lease_start_time);
            $data['lease_end_time']=strtotime($lease_end_time);

            if($level1 and $level2 and $level3){
                $data['is_remote']=1;
                $data['level0']=1;

                if(!$RegionObj->isProvince($level1)){
                    Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000001, '国家、省、市、区中的省的ID不存在');
                }

                if(!$RegionObj->isCity($level2)){
                    Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000002, '国家、省、市、区中的市的ID不存在');
                }

                if(!$RegionObj->isArea($level3)){
                    Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000003, '国家、省、市、区中的区的ID不存在');
                }


                $data['level1']=$level1;
                $data['level2']=$level2;
                $data['level3']=$level3;

            }else{
                $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
                $data['is_remote']=0;
                $data['level0']=1;
                $data['level1']=$Db_level['level1'];
                $data['level2']=$Db_level['level2'];
                $data['level3']=$Db_level['level3'];
            }
        $lease_id = $LeaseObj->add($data);
        if(!$lease_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000006, '租赁添加失败！');
        }


        $MoreImage = array();
        $savePath="storage/lease/{$lease_id}/";
        if(!file_exists(PATH_ROOT.$savePath)){
            @mkdir(PATH_ROOT.$savePath, 0777);
        }
        if(Buddha_Atom_Array::isValidArray($image_arr)){
            foreach($image_arr as $k=>$v){
                $temp_img_arr = explode(',', $v);
                $temp_base64_string = $temp_img_arr[1];
                $output_file = date('Ymdhis',time()). "-{$k}.jpg";
                $filePath =PATH_ROOT.$savePath.$output_file;
                Buddha_Atom_File::base64contentToImg($filePath,$temp_base64_string);
                Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
                $result_img = $savePath.''.$output_file;
                $MoreImage[] = "{$result_img}";
            }
        }

        if(Buddha_Atom_Array::isValidArray($MoreImage)){
            $LeaseObj->addImageArrToLeaseAlbum($MoreImage,$lease_id,$savePath,$user_id);
            $LeaseObj->setFirstGalleryImgToLease($lease_id);
        }



                $is_needcreateorder = 0;
                $Services = '';
                $param = array();
                //$remote为1表示发布异地产品添加订单
                if($is_remote==1){
                    $is_needcreateorder = 1;
                    $Services = '';
                    $param = array();


                }





            $jsondata = array();
            $jsondata['user_id'] = $user_id;
            $jsondata['usertoken'] = $usertoken;
            $jsondata['lease_id'] = $lease_id;

            $jsondata['is_needcreateorder'] = $is_needcreateorder;
            $jsondata['Services'] = $Services;
            $jsondata['param'] = $param;


            $jsondata['db_isok'] = '1';
            $jsondata['db_msg'] = '添加成功';


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '添加租赁');




    }
    public function beforeupdate(){




    

        if (Buddha_Http_Input::checkParameter(array('usertoken','lease_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $ShopObj = new Shop();
        $LeaseObj = new Lease();
        $LeasecatObj = new Leasecat();
        $RegionObj= new Region();

        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $lease_id = Buddha_Http_Input::getParameter('lease_id');
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','mobile','realname');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        /*if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色');
        }

        if(!$LeaseObj->isLeaseBelongToUser($lease_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000033, '租赁信息的主人不是目前的用户');
        }*/


        $Db_Lease = $LeaseObj->getSingleFiledValues('' ,"id= '{$lease_id}' ");


        $lease_start_time = $Db_Lease['lease_start_time'];
        $lease_start_timestr = date('Y-m-d',$lease_start_time);
        $lease_end_time = $Db_Lease['lease_end_time'];
        $lease_end_timestr = date('Y-m-d',$lease_end_time);

        $level1 = $Db_Lease['level1'];
        $level2 = $Db_Lease['level2'];
        $level3 = $Db_Lease['level3'];



        //更新所在地
        $regionid=$level3;
        $allarea = '';
        if(strlen($regionid) and $regionid>0) {
            $area = $RegionObj->getAllArrayAddressByLever($regionid);


            $row = array();
            $row['level0'] = 1;
            foreach ($area as $k => $v) {
                if ($k > 0) {
                    $allarea .= $v['name'] . '>';
                    $str = "level" . $k;
                    $row[$str] = $v['id'];
                }


            }
            if ($allarea != '')
                $allarea = Buddha_Atom_String::toDeleteTailCharacter($allarea);
        }

        $leasecat_id = $Db_Lease['leasecat_id'];
        $Db_LeaseCat = $LeasecatObj->getSingleFiledValues(array('cat_name'),"id='{$leasecat_id}' ");


        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['lease_id'] = $lease_id;
        $jsondata['lease_name'] = $Db_Lease['lease_name'];
        $jsondata['shop_id'] = $Db_Lease['shop_id'];
        $jsondata['level1'] = $level1;
        $jsondata['level2'] = $level2;
        $jsondata['level3'] = $level3;
        $jsondata['allarea'] = $allarea;
        $jsondata['lease_start_time'] = $lease_start_time;
        $jsondata['lease_start_timestr'] = $lease_start_timestr;
        $jsondata['lease_end_time'] = $lease_end_time;
        $jsondata['lease_end_timestr'] = $lease_end_timestr;
        $jsondata['leasecat_id']=$leasecat_id;
        $jsondata['cat_name']=$Db_LeaseCat['cat_name'];
        $jsondata['rent']=$Db_Lease['rent'];
        $jsondata['keywords']=$Db_Lease['keywords'];
        $jsondata['lease_brief']=$Db_Lease['lease_brief'];
        $jsondata['lease_desc']=$Db_Lease['lease_desc'];
        $jsondata['is_remote']=$Db_Lease['is_remote'];
        $jsondata['albumlist']=$LeaseObj->getApiLeaseAlbumArr($lease_id,$b_display);

        $shop_id_list=$ShopObj->getUserShopArr($user_id,$Db_Lease['shop_id']);

        if(Buddha_Atom_Array::isValidArray($shop_id_list)){
            $jsondata['shop_id_list'] = $shop_id_list;
        }else{
            $shop_id_list = $ShopObj->getFiledValues(array('name','id as namevalue'),"id='{$Db_Lease['shop_id']}'");
            $jsondata['shop_id_list'] = $shop_id_list;
        }

        $jsondata['shop_id_list'] = $shop_id_list;
        $jsondata['area'] = array(
            'Services'=>'ajaxregion.getBelongFromFatherId',
            'param'=>array('father'=>1),
        );


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '编辑租赁之前的展示页面');
    }
    public function update(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','lease_name','leasecat_id', 'shop_id','lease_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $ShopObj=new Shop();
        $UserObj = new User();
        $Leasecat = new Leasecat();
        $LeaseObj = new Lease();
        $RegionObj = new Region();
        $JsonimageObj = new Jsonimage();
        $lease_id=Buddha_Http_Input::getParameter('lease_id');
        $lease_name=Buddha_Http_Input::getParameter('lease_name');
        $leasecat_id=Buddha_Http_Input::getParameter('leasecat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $rent=Buddha_Http_Input::getParameter('rent');
        $keywords=Buddha_Http_Input::getParameter('keywords');
        //商品促销
        $lease_start_time=Buddha_Http_Input::getParameter('lease_start_time');
        $lease_end_time=Buddha_Http_Input::getParameter('lease_end_time');

        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');



        //描述
        $lease_brief = Buddha_Http_Input::getParameter('lease_brief');
        $lease_desc = Buddha_Http_Input::getParameter('lease_desc');

        $level1 = Buddha_Http_Input::getParameter('level1');
        $level2 = Buddha_Http_Input::getParameter('level2');
        $level3 = Buddha_Http_Input::getParameter('level3');

        $image_arr=Buddha_Http_Input::getParameter('image_arr');

        if(Buddha_Atom_String::isJson($image_arr)){
            $image_arr = json_decode($image_arr);
        }

        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);

        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色');
        }


        if(!$Leasecat->isHasRecord($leasecat_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000034, '租赁分类不存在');

        }

        if($ShopObj->getShopOfSureToUserTotalInt($shop_id,$user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000028, '您还没用创建店铺，或者店铺还未通过审核！');
        }

        if(!$ShopObj->isShopBelongToUser($shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000029, '此店铺不属于当前用户');
        }

        if(!$LeaseObj->isLeaseBelongToUser($lease_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000033, '租赁信息的主人不是目前的用户');
        }


        $data=array();
        $data['lease_name']=$lease_name;
        $data['user_id']=$user_id;
        $data['leasecat_id']=$leasecat_id;
        $data['shop_id']=$shop_id;
        $data['rent']=$rent;
        $data['keywords']=$keywords;
        $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
        $data['lease_brief']=$lease_brief;
        $data['lease_desc']=$lease_desc;
        $data['lease_start_time']=strtotime($lease_start_time);
        $data['lease_end_time']=strtotime($lease_end_time);

        if($level1 and $level2 and $level3){
            $data['is_remote']=1;
            $data['level0']=1;

            if(!$RegionObj->isProvince($level1)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000001, '国家、省、市、区中的省的ID不存在');
            }

            if(!$RegionObj->isCity($level2)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000002, '国家、省、市、区中的市的ID不存在');
            }

            if(!$RegionObj->isArea($level3)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000003, '国家、省、市、区中的区的ID不存在');
            }


            $data['level1']=$level1;
            $data['level2']=$level2;
            $data['level3']=$level3;



        }else{
            $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
            $data['is_remote']=0;
            $data['level0']=1;
            $data['level1']=$Db_level['level1'];
            $data['level2']=$Db_level['level2'];
            $data['level3']=$Db_level['level3'];
        }
        $LeaseObj->edit($data,$lease_id);


        $MoreImage = array();
        $savePath="storage/lease/{$shop_id}/";
        if(!file_exists(PATH_ROOT.$savePath)){
            mkdir(PATH_ROOT.$savePath, 0777);
        }
        if(Buddha_Atom_Array::isValidArray($image_arr)) {
            foreach ($image_arr as $k => $v) {
                $temp_img_arr = explode(',', $v);
                $temp_base64_string = $temp_img_arr[1];
                $output_file = date('Ymdhis', time()) . "-{$k}.jpg";
                $filePath = PATH_ROOT . $savePath . $output_file;
                Buddha_Atom_File::base64contentToImg($filePath, $temp_base64_string);
                Buddha_Atom_File::resolveImageForRotate($filePath, NULL);
                $result_img = $savePath . '' . $output_file;
                $MoreImage[] = "{$result_img}";
            }
        }

        if(Buddha_Atom_Array::isValidArray($MoreImage)){
            $LeaseObj->addImageArrToLeaseAlbum($MoreImage,$lease_id,$savePath,$user_id);
            $LeaseObj->setFirstGalleryImgToLease($lease_id);
        }
        $is_needcreateorder = 0;
        $Services = '';
        $param = array();
        //$remote为1表示发布异地产品添加订单
        if($is_remote==1){
            $is_needcreateorder = 1;
            $Services = 'payment.remoteinfo';
            $param = array('good_id'=>$lease_id,'good_table'=>'lease');
        }
        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['lease_id'] = $lease_id;

        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;


        $jsondata['db_isok'] = '1';
        $jsondata['db_msg'] = '编辑成功';


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '编辑租赁');


    }


}