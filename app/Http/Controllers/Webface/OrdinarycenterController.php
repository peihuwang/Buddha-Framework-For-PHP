<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/7
 * Time: 18:52
 * author sys
 */

class OrdinarycenterController extends Buddha_App_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

        //权限
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

    /**
     * 普通会员个人中心收货地址列表
     */
    public function shippingAddressMore(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $AddressObj = new Address();
        $RegionObj = new Region();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $addreinfo = $AddressObj->getFiledValues('',"uid={$user_id}");//获取收货地址详情
        foreach($addreinfo as $k => $v){
            if($v['pro']){
                $pro = $RegionObj->getSingleFiledValues(array('name'),"id={$v['pro']}");
                $addreinfo[$k]['pro'] = $pro['name'];
            }
            if($v['city']){
                $city = $RegionObj->getSingleFiledValues(array('name'),"id={$v['city']}");
                $addreinfo[$k]['city'] = $city['name'];
            }
            if($v['area']){
                $area = $RegionObj->getSingleFiledValues(array('name'),"id={$v['area']}");
                $addreinfo[$k]['area'] = $area['name'];
            }
            $addreinfo[$k]['icon_edit'] = $host . "apiuser/menuplus/address_edit.png";
            $addreinfo[$k]['icon_del'] = $host . "apiuser/menuplus/address_del.png";
            if($v['isdef'] == 1){
                $addreinfo[$k]['icon_default'] = $host . "apiuser/menuplus/address_disable.png";
            }else{
                $addreinfo[$k]['icon_default'] = $host . "apiuser/menuplus/address_enable.png";
            }

        }
        $jsondata = array();
        $jsondata['list'] = $addreinfo;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'收货地址列表');
    }

    /**
     * 添加收货地址
     */
    public function add(){
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','realname','mobile','level1','level2','level3','specticloc'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $AddressObj = new Address();
        $RegionObj = new Region();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $num = $AddressObj->countRecords("uid='{$user_id}'");
        $realname = Buddha_Http_Input::getParameter('realname');
        $mobile = Buddha_Http_Input::getParameter('mobile');
        $level1 = Buddha_Http_Input::getParameter('level1');
        $level2 = Buddha_Http_Input::getParameter('level2');
        $level3 = Buddha_Http_Input::getParameter('level3');
        $specticloc = Buddha_Http_Input::getParameter('specticloc');
        $data['uid'] = $user_id;
        $data['mobile'] = $mobile;
        $data['name'] = $realname;
        if($level1 and $level2 and $level3) {
            if (!$RegionObj->isProvince($level1)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000001, '国家、省、市、区中的省的ID不存在');
            }
            if (!$RegionObj->isCity($level2)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000002, '国家、省、市、区中的市的ID不存在');
            }
            if (!$RegionObj->isArea($level3)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000003, '国家、省、市、区中的区的ID不存在');
            }
            $data['pro']=$level1;
            $data['city']=$level2;
            $data['area']=$level3;
        }
        $data['address'] = $specticloc;
        if(!$num){
            $data['isdef'] = 1;
        }
        $addid = $AddressObj->add($data);
        if($addid){
            $jsondata['db_isok'] = '1';
            $jsondata['db_msg'] = '添加成功';
        }else{
            $jsondata['db_isok'] = '0';
            $jsondata['db_msg'] = '服务器忙';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '添加收货地址');
    }
    /**
     * 收货地址编辑之前页面
     */
    public function updatebefore(){
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','address_id'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $AddressObj = new Address();
        $RegionObj = new Region();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $address_id = Buddha_Http_Input::getParameter('address_id');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $AddressInfo = $AddressObj->getSingleFiledValues('',"id='{$address_id}'");
        if(count($AddressInfo)<=0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000033, 'address_id不正确');
        }elseif($AddressInfo['uid']!=$user_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000033, '收货地址的主人不是目前的用户');
        }
        $region = $RegionObj->getDetailOfAdrressByRegionIdStr($AddressInfo['pro'],$AddressInfo['city'],$AddressInfo['area'],'>');
        $AddressInfo['regionstr'] = $region;
        $jsondata = $AddressInfo;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '收货地址编辑前页面');

    }

    /**
     * 收货地址编辑页面
     */
    public function update(){
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','address_id','realname','mobile','level1','level2','level3',
            'specticloc'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $RegionObj = new Region();
        $AddressObj = new Address();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $address_id = Buddha_Http_Input::getParameter('address_id');
        $realname = Buddha_Http_Input::getParameter('realname');
        $mobile = Buddha_Http_Input::getParameter('mobile');
        $level1 = Buddha_Http_Input::getParameter('level1');
        $level2 = Buddha_Http_Input::getParameter('level2');
        $level3 = Buddha_Http_Input::getParameter('level3');
        $specticloc = Buddha_Http_Input::getParameter('specticloc');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $AddressInfo = $AddressObj->getSingleFiledValues('',"id='{$address_id}'");
        if(count($AddressInfo)<=0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000033, 'address_id不正确');
        }elseif($AddressInfo['uid']!=$user_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000033, '收货地址的主人不是目前的用户');
        }
        $data['name'] = $realname;
        $data['mobile'] = $mobile;
        if($level1 and $level2 and $level3) {
            if (!$RegionObj->isProvince($level1)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000001, '国家、省、市、区中的省的ID不存在');
            }
            if (!$RegionObj->isCity($level2)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000002, '国家、省、市、区中的市的ID不存在');
            }
            if (!$RegionObj->isArea($level3)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000003, '国家、省、市、区中的区的ID不存在');
            }
        }
        $data['pro'] = $level1;
        $data['city'] = $level2;
        $data['area'] = $level3;
        $data['address'] = $specticloc;
        $AddressObj->edit($data,$address_id);
        $jsondata['db_isok'] = '1';
        $jsondata['db_msg'] = '编辑成功';
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '编辑收货地址');
    }
    /**
     * 收货地址删除
     */
    public function del(){
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','address_id'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $AddressObj = new Address();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $address_id = Buddha_Http_Input::getParameter('address_id');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $AddressInfo = $AddressObj->getSingleFiledValues('',"id='{$address_id}'");
        if(count($AddressInfo)<=0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000033, 'address_id不正确');
        }elseif($AddressInfo['uid']!=$user_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000033, '收货地址的主人不是目前的用户');
        }
        $AddressObj->del($address_id);//删除对应项
        $num = $AddressObj->countRecords("isdef=1");
        if(!$num){//如果没有默认自动添加一个默认地址
            $addressinfo = $AddressObj->getSingleFiledValues(array('id'),"uid={$user_id}");
            if($addressinfo){
                $data['isdef'] = 1;
                $AddressObj->edit($data,$addressinfo['id']);
            }
        }
        $jsondata['db_isok'] = '1';
        $jsondata['db_msg'] = '删除成功';
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '删除收货地址');
    }

    /**
     * 改变默认收货地址
     */

    public function changeDefaultAddress(){
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','address_id'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $AddressObj = new Address();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $address_id = Buddha_Http_Input::getParameter('address_id');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $AddressInfo = $AddressObj->getSingleFiledValues('',"id='{$address_id}'");
        if(count($AddressInfo)<=0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000033, 'address_id不正确');
        }elseif($AddressInfo['uid']!=$user_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000033, '收货地址的主人不是目前的用户');
        }
        $data['isdef'] = 0;
        $AddressObj->updateRecords($data,"uid={$user_id}");
        $datas['isdef'] = 1;
        $AddressObj->edit($datas,$address_id);
        $jsondata['db_isok'] = '1';
        $jsondata['db_msg'] = '修改成功';
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '修改默认收货地址');

    }
}


