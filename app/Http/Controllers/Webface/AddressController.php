<?php

/**
 * Created by PhpStorm.
 * User: mac
 * Date: 2017/8/26
 * Time: 21:39
 */
class AddressController extends Buddha_App_Action
{

    protected $tablenamestr;
    protected $tablename;

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
        $this->tablenamestr='用户的收货地址';
        $this->tablename='address';

    }

    /**
     *  用户的收货地址 列表
     */
    public function more()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $AddressObj = new Address();
        $RegionObj = new Region();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $Db_User = $UserObj->getSingleFiledValues(array('id','usertoken')," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $filed = array('id as address_id','mobile','pro','city','area','address','name','isdef');
        $where = 'uid='.$user_id;

        $Db_Address = $AddressObj->getFiledValues($filed,$where);

        $jsondata = array();

        if(Buddha_Atom_Array::isValidArray($Db_Address))
        {
            foreach ($Db_Address as $k=>$v)
            {
                $Db_Address[$k]['detailedarea'] = $RegionObj->getDetailOfAdrressByRegionIdStr($v['pro'],$v['city'],$v['area'],$Spacer=' ').' '.$v['address'];

                $Db_Address[$k]['add']['Services'] = 'address.addbefore';
                $Db_Address[$k]['add']['param'] = array();

                $Db_Address[$k]['update']['Services'] = 'address.updatebefore';
                $Db_Address[$k]['update']['param'] = array('address_id'=>$v['address_id']);

                $Db_Address[$k]['del']['Services'] = 'address.del';
                $Db_Address[$k]['del']['param'] = array('address_id'=>$v['address_id']);

                $Db_Address[$k]['default']['Services'] = 'address.defaultuopdate';
                $Db_Address[$k]['default']['param'] = array('address_id'=>$v['address_id'],'isdef'=>$v['$isdef']);

                unset($Db_Address[$k]['pro']);
                unset($Db_Address[$k]['city']);
                unset($Db_Address[$k]['area']);
                unset($Db_Address[$k]['address']);
            }

            $jsondata = $Db_Address;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,$this->tablenamestr.'列表');

    }


    /**
     *  用户的收货地址 添加之前
     */
    public function addbefore()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken',''))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $Db_User = $UserObj->getSingleFiledValues(array('id','usertoken')," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        $addreinfo['headertitle'] = $this->tablenamestr.'添加';
        $addreinfo['Services'] = 'ajaxregion.getBelongFromFatherId';
        $addreinfo['param'] = array('father'=>1);
        $jsondata = $addreinfo;


        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,$this->tablenamestr.'添加之前');
    }




    /**
     *  用户的收货地址 添加
     */
    public function add()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $AddressObj = new Address();
        $RegionObj = new Region();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $Db_User = $UserObj->getSingleFiledValues(array('id','usertoken')," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $jsondata = array();

        $Db_Address_count = $AddressObj->countRecords("uid={$user_id}");


        $realname = Buddha_Http_Input::getParameter('realname')?Buddha_Http_Input::getParameter('realname'):'';//收件人
        $mobile = Buddha_Http_Input::getParameter('mobile')?Buddha_Http_Input::getParameter('mobile'):0;//手机号
        $level1 = (int)Buddha_Http_Input::getParameter('level1')?(int)Buddha_Http_Input::getParameter('level1'):0;
        $level2 = (int)Buddha_Http_Input::getParameter('level2')?(int)Buddha_Http_Input::getParameter('level2'):0;
        $level3 = (int)Buddha_Http_Input::getParameter('level3')?(int)Buddha_Http_Input::getParameter('level3'):0;
        $specticloc = Buddha_Http_Input::getParameter('specticloc')?Buddha_Http_Input::getParameter('specticloc'):0;//详细地址

        if(!$RegionObj->isCountries($level1)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中 省的地区内码id不正确');
        }
        if(!$RegionObj->isProvince($level2)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中 区的地区内码id不正确');
        }
        if(!$RegionObj->isCity($level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000004, '国家、省、市、区 中 国家的地区内码id不正确');
        }


        if(!Buddha_Atom_String::isValidString($realname)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000080, '收件人不能为空!');
        }

        if(!Buddha_Atom_String::isValidString($mobile)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000081, '电话不能为空!');
        }

        if(!Buddha_Atom_String::isValidString($specticloc)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000082, '详细地址不能空！');
        }


        $data['uid'] = $user_id;
        $data['mobile'] = $mobile;
        $data['name'] = $realname;
        $data['pro'] = $level1;
        $data['city'] = $level2;
        $data['area'] = $level3;
        $data['address'] = $specticloc;

        if(!$Db_Address_count)
        {
            $data['isdef'] = 1;
        }
       $Address_id = $AddressObj->add($data);

        if($Address_id)
        {
            $jsondata['db_isok'] = 'true';
            $jsondata['db_msg'] = $this->tablenamestr.'添加成功!';
        }else{
            $jsondata['db_isok'] = 'false';
            $jsondata['db_msg'] = $this->tablenamestr.'添加失败!';
        }
        $jsondata['address_id'] = $Address_id;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,$this->tablenamestr.'添加');
    }



    /**
     *  用户的收货地址 编辑之前
     */
    public function updatebefore()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken','address_id')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $AddressObj = new Address();
        $RegionObj = new Region();
        $CommonObj = new Common();

        $address_id = (int)Buddha_Http_Input::getParameter('address_id')?(int)Buddha_Http_Input::getParameter('address_id'):0;

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $Db_User = $UserObj->getSingleFiledValues(array('id','usertoken')," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$CommonObj->isIdByTablenameAndTableid($this->tablename,$address_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000083, '收货地址内码ID无效！');
        }


        $filed = array('id as address_id','name','mobile','pro','city','area','address','isdef');

        $addreinfo = $AddressObj->getSingleFiledValues($filed,"id='{$address_id}' AND uid='{$user_id}'");


        if(Buddha_Atom_String::isValidString($addreinfo['pro'])  AND Buddha_Atom_String::isValidString($addreinfo['city'])  AND Buddha_Atom_String::isValidString($addreinfo['area']))
        {
            $addreinfo['addre'] = $RegionObj->getDetailOfAdrressByRegionIdStr($addreinfo['pro'],$addreinfo['city'],$addreinfo['area'],$Spacer='>');
        }

        $jsondata = array();
        $jsondata['headertitle'] = $this->tablenamestr.'编辑';
        $addreinfo['Services'] = 'ajaxregion.getBelongFromFatherId';
        $addreinfo['param'] = array('father'=>1);

        if(Buddha_Atom_Array::isValidArray($addreinfo))
        {
            $jsondata = $addreinfo;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,$this->tablenamestr.'编辑之前');
    }



    /**
     *  用户的收货地址 编辑
     */
    public function update()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken','address_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $AddressObj = new Address();
        $RegionObj = new Region();
        $CommonObj = new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $Db_User = $UserObj->getSingleFiledValues(array('id','usertoken')," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $jsondata = array();

        $Db_Address_count = $AddressObj->countRecords("uid={$user_id}");
        $address_id = (int)Buddha_Http_Input::getParameter('address_id')?(int)Buddha_Http_Input::getParameter('address_id'):0;
        $realname = Buddha_Http_Input::getParameter('realname')?Buddha_Http_Input::getParameter('realname'):'';//收件人
        $mobile = Buddha_Http_Input::getParameter('mobile')?Buddha_Http_Input::getParameter('mobile'):0;//手机号
        $level1 = (int)Buddha_Http_Input::getParameter('level1')?(int)Buddha_Http_Input::getParameter('level1'):0;
        $level2 = (int)Buddha_Http_Input::getParameter('level2')?(int)Buddha_Http_Input::getParameter('level2'):0;
        $level3 = (int)Buddha_Http_Input::getParameter('level3')?(int)Buddha_Http_Input::getParameter('level3'):0;
        $specticloc = Buddha_Http_Input::getParameter('specticloc')?Buddha_Http_Input::getParameter('specticloc'):0;//详细地址

        if(!$CommonObj->isIdByTablenameAndTableid($this->tablename,$address_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000083, '收货地址内码ID无效！');
        }


        if(!$RegionObj->isCountries($level1)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中 省的地区内码id不正确');
        }
        if(!$RegionObj->isProvince($level2)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中 区的地区内码id不正确');
        }
        if(!$RegionObj->isCity($level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000004, '国家、省、市、区 中 国家的地区内码id不正确');
        }


        if(!Buddha_Atom_String::isValidString($realname)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000080, '收件人不能为空!');
        }

        if(!Buddha_Atom_String::isValidString($mobile)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000081, '电话不能为空!');
        }

        if(!Buddha_Atom_String::isValidString($specticloc)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000082, '详细地址不能空！');
        }


        $data['mobile'] = $mobile;
        $data['name'] = $realname;
        $data['pro'] = $level1;
        $data['city'] = $level2;
        $data['area'] = $level3;
        $data['address'] = $specticloc;

        if(!$Db_Address_count)
        {
            $data['isdef'] = 1;
        }

        if($AddressObj->updateRecords($data,$address_id))
        {
            $jsondata['db_isok'] = 'true';
            $jsondata['db_msg'] = $this->tablenamestr.'编辑成功!';
        }else{
            $jsondata['isok'] = 'false';
            $jsondata['db_msg'] = $this->tablenamestr.'编辑失败!';
        }
        $jsondata['address_id'] = $address_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,$this->tablenamestr.'编辑');
    }



    /**用户的收货地址：修改默认收货地址***/
    public function defaultuopdate()
    {

        if (Buddha_Http_Input::checkParameter(array('usertoken','address_id','isdef'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $AddressObj = new Address();
        $CommonObj = new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $Db_User = $UserObj->getSingleFiledValues(array('id','usertoken')," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $address_id = (int)Buddha_Http_Input::getParameter('address_id')?(int)Buddha_Http_Input::getParameter('address_id'):0;
        $isdef = (int)Buddha_Http_Input::getParameter('isdef')?(int)Buddha_Http_Input::getParameter('isdef'):0;

        if(!$CommonObj->isIdByTablenameAndTableid($this->tablename,$address_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000083, '收货地址内码ID无效！');
        }


        /**↓↓↓↓↓↓↓↓↓↓↓ 查询用户现有默认地址的个数：并改为非默认 ↓↓↓↓↓↓↓↓↓↓↓**/
        $Address_id = $AddressObj->getFiledValues(array('id'),"uid='{$user_id}' AND isdef=1 ");

        if(Buddha_Atom_Array::isValidArray($Address_id))
        {
            $Address_id_str = '';
            foreach ($Address_id as $k=>$v){
                $Address_id_str .= $v['id'].',';
            }
            $Address_id_str = trim($Address_id_str,',');
            $Address_data_isdef['isdef'] = 0;
            $AddressObj->updateRecords($Address_data_isdef," id IN ($Address_id_str)");
        }
        /**↑↑↑↑↑↑↑↑↑↑ 查询用户现有默认地址的个数：并改为非默认 ↑↑↑↑↑↑↑↑↑↑**/

        $data['isdef'] = $isdef;
        $jsondata = array();
        if($AddressObj->updateRecords($data,$address_id))
        {
            $jsondata['db_isok'] = 'true';
            $jsondata['db_msg'] = $this->tablenamestr.'默认地址设置成功!';
        }else{
            $jsondata['isok'] = 'false';
            $jsondata['db_msg'] = $this->tablenamestr.'默认地址设置失败!';
        }
        $jsondata['address_id'] = $address_id;


        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,$this->tablenamestr.'默认地址设置!');
    }



    /**
     *  用户的收货地址 删除
     */
    public function del()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken' ))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $AddressObj = new Address();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $Db_User = $UserObj->getSingleFiledValues(array('id','usertoken')," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $address_id = (int)Buddha_Http_Input::getParameter('address_id')?(int)Buddha_Http_Input::getParameter('address_id'):0;


        $Db_Address_num = $AddressObj->del($address_id);//删除对应项

        $num = $AddressObj->countRecords("isdef=1");
        if(!$num){//如果没有默认自动添加一个默认地址
            $addressinfo = $AddressObj->getSingleFiledValues(array('id'),"uid={$user_id}");
            if($addressinfo)
            {
                $data['isdef'] = 1;
                $AddressObj->edit($data,$addressinfo['id']);
            }
        }

        $jsondata  = array();

        if($Db_Address_num)
        {
            $datas['db_isok'] = 'true';
            $datas['db_msg'] = $this->tablenamestr.'删除成功！';
        }else{
            $datas['db_msg'] = 'false';
            $datas['db_msg'] = $this->tablenamestr.'删除失败!';
        }
        $datas['address_id'] = $address_id;



        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,$this->tablenamestr.'删除');


    }














}