<?php

/**
 * Class RecruitController
 */
class RecruitController extends Buddha_App_Action
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


    /**
     * 代理商招聘信息审核
     */
    public function verify(){

        if (Buddha_Http_Input::checkParameter(array('recruit_id','usertoken','is_sure'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $RecruitObj = new Recruit();

        $recruit_id=  Buddha_Http_Input::getParameter('recruit_id');
        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $is_sure =  Buddha_Http_Input::getParameter('is_sure');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$RecruitObj->isOwnerBelongToAgentByLeve3($recruit_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $data = array();
        $data['is_sure'] = $is_sure ;
        $RecruitObj->edit($data,$recruit_id);

        $jsondata = array();
        $jsondata['$recruit_id'] = $recruit_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商招聘信息审核');


    }

    /**
     * 代理商招聘信息下架
     */
    public function offshelf(){

        if (Buddha_Http_Input::checkParameter(array('recruit_id','usertoken','shelf'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $RecruitObj = new Recruit();

        $recruit_id =  Buddha_Http_Input::getParameter('recruit_id');
        /*默认下架  0下架 1=上架*/
        $shelf = (int)Buddha_Http_Input::getParameter('shelf') ? (int)Buddha_Http_Input::getParameter('shelf') : 0;

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if($UserObj->isHasAgentPrivilege($user_id)==0){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$RecruitObj->isOwnerBelongToAgentByLeve3($recruit_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $data = array();
        $msg="";
        if($shelf==0){
            $data['buddhastatus'] =1 ;
            $msg="下架";
        }else{
            $data['buddhastatus'] =0 ;
            $msg="上架";
        }
        $RecruitObj->edit($data,$recruit_id);

        $jsondata = array();
        $jsondata['recruit_id'] = $recruit_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['msg'] = $msg;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商招聘信息下架'.$msg);


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

        $jsondata['contacts'] = Buddha_Atom_String::getApiValidStr($Db_User['realname']);
        $jsondata['tel'] = Buddha_Atom_String::getApiValidStr($Db_User['mobile']);
        $educationlist[]=array('name'=>'学历不限','namevalue'=>1,'select'=>1);
        $educationlist[]=array('name'=>'初中学历','namevalue'=>2,'select'=>0);
        $educationlist[]=array('name'=>'高中学历','namevalue'=>3,'select'=>0);
        $educationlist[]=array('name'=>'中专学历','namevalue'=>4,'select'=>0);
        $educationlist[]=array('name'=>'专科学历','namevalue'=>5,'select'=>0);
        $educationlist[]=array('name'=>'本科学历','namevalue'=>6,'select'=>0);
        $educationlist[]=array('name'=>'本科以前学历','namevalue'=>7,'select'=>0);
        $jsondata['educationlist'] = $educationlist;
        $worklist[]=array('name'=>'工作经验不限','namevalue'=>1,'select'=>1);
        $worklist[]=array('name'=>'1-2两年工作经验','namevalue'=>2,'select'=>0);
        $worklist[]=array('name'=>'2-4两年工作经验','namevalue'=>3,'select'=>0);
        $worklist[]=array('name'=>'5年以上相关经验','namevalue'=>4,'select'=>0);
        $jsondata['worklist'] = $worklist;


        $shop_id_list=$ShopObj->getUserShopArr($user_id,0);
        $jsondata['shop_id_list'] = $shop_id_list;




        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '添加招聘之前的展示页面');
    }

    public function add(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','recruit_name','recruitcat_id',
            'education','work','contacts','tel', 'shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $RecruitObj=new Recruit();
        $RecruitcatObj = new Recruitcat();
        $ShopObj=new Shop();
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $recruit_name = Buddha_Http_Input::getParameter('recruit_name');
        $recruitcat_id = Buddha_Http_Input::getParameter('recruitcat_id');
        $pay = Buddha_Http_Input::getParameter('pay');
        $education = Buddha_Http_Input::getParameter('education');
        $work = Buddha_Http_Input::getParameter('work');
        $recruit_start_time = Buddha_Http_Input::getParameter('recruit_start_time');
        $recruit_end_time = Buddha_Http_Input::getParameter('recruit_end_time');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $treatment = Buddha_Http_Input::getParameter('treatment');
        $number = Buddha_Http_Input::getParameter('number');
        $is_remote = Buddha_Http_Input::getParameter('is_remote');
        $contacts = Buddha_Http_Input::getParameter('contacts');
        $tel = Buddha_Http_Input::getParameter('tel');
        $recruit_desc = Buddha_Http_Input::getParameter('recruit_desc');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色');
        }
         if(!$RecruitcatObj->isHasRecord($recruitcat_id)){
             Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000030, '招聘分类不存在');

         }
        if($ShopObj->getShopOfSureToUserTotalInt($shop_id,$user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000028, '您还没用创建店铺，或者店铺还未通过审核！');
        }
        if(!$ShopObj->isShopBelongToUser($shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000029, '此店铺不属于当前用户');
        }
        $Db_level = $ShopObj->getSingleFiledValues(array('level0', 'level1', 'level2', 'level3'), "user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
        $data = array();
        $data['recruit_name'] = $recruit_name;
        $data['user_id'] = $user_id;
        $data['recruit_id'] = $recruitcat_id;
        $data['shop_id'] = $shop_id;
        $data['pay'] = $pay;
        $data['education'] = $education;
        $data['work'] = $work;
        $data['treatment'] = $treatment;
        $data['number'] = $number;
        $data['contacts'] = $contacts;
        $data['tel'] = $tel;
        $data['level0'] = $Db_level['level0'];
        $data['level1'] = $Db_level['level1'];
        $data['level2'] = $Db_level['level2'];
        $data['level3'] = $Db_level['level3'];
        $data['recruit_start_time'] = strtotime($recruit_start_time);
        $data['recruit_end_time'] = strtotime($recruit_end_time);
        $data['recruit_desc'] = $recruit_desc;
        $data['add_time'] = Buddha::$buddha_array['buddha_timestamp'];
        $recruit_id = $RecruitObj->add($data);
        $is_needcreateorder = 0;
        $Services = '';
        $param = array();
        //$remote为1表示发布异地产品添加订单
        if($is_remote==1){
            $is_needcreateorder = 1;
            $Services = 'payment.remoteinfo';
            $param = array('good_id'=>$recruit_id,'good_table'=>'recruit');
        }
        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['db_isok'] = '1';
        $jsondata['db_msg'] = '添加成功';
        $jsondata['recruit_id'] = $recruit_id;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '添加招聘');
    }
    public function beforeupdate(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','recruit_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $ShopObj = new Shop();
        $RecruitObj = new Recruit();
        $RecruitcatObj = new Recruitcat();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $recruit_id = Buddha_Http_Input::getParameter('recruit_id');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','mobile','realname');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
       /* if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色');
        }
        if(!$RecruitObj->isRecruitBelongToUser($recruit_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000031, '招聘信息的主人不是目前的用户或此条信息不存在');
        }*/
        $Db_Recruit = $RecruitObj->getSingleFiledValues('' ,"id= '{$recruit_id}' ");
        $recruit_start_time = $Db_Recruit['recruit_start_time'];
        $recruit_start_timestr = date('Y-m-d',$recruit_start_time);
        $recruit_end_time = $Db_Recruit['recruit_end_time'];
        $recruit_end_timestr = date('Y-m-d',$recruit_end_time);
        $recruitcat_id = $Db_Recruit['recruit_id'];
        $Db_Recruitcat = $RecruitcatObj->getSingleFiledValues(array('cat_name'),"id='{$recruitcat_id}' ");
        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['recruit_id'] = $recruit_id;
        $jsondata['recruitcat_id'] = $recruitcat_id;
        $jsondata['cat_name'] = $Db_Recruitcat['cat_name'];
        $jsondata['shop_id'] = $Db_Recruit['shop_id'];
        $jsondata['recruit_start_time'] = $recruit_start_time;
        $jsondata['recruit_start_timestr'] = $recruit_start_timestr;
        $jsondata['recruit_end_time'] = $recruit_end_time;
        $jsondata['recruit_end_timestr'] = $recruit_end_timestr;
        $jsondata['recruit_name'] = $Db_Recruit['recruit_name'];
        $jsondata['pay'] = $Db_Recruit['pay'];
        $education = $Db_Recruit['education'];
        if(!($education>=1 and $education<=7)){
            $education=1;
        }
        $jsondata['education'] = $education;
        $work = $Db_Recruit['work'];
        $jsondata['work'] = $work;
        if(!($work>=1 and $work<=4)){
            $work=1;
        }
        $jsondata['work'] = $work;
        $jsondata['number'] = $Db_Recruit['number'];
        $jsondata['treatment'] = $Db_Recruit['treatment'];
        $jsondata['tel'] = $Db_Recruit['tel'];
        $jsondata['contacts'] = $Db_Recruit['contacts'];
        $jsondata['recruit_desc'] = $Db_Recruit['recruit_desc'];
        $educationlist[]=array('name'=>'学历不限','namevalue'=>1,'select'=>0);
        $educationlist[]=array('name'=>'初中学历','namevalue'=>2,'select'=>0);
        $educationlist[]=array('name'=>'高中学历','namevalue'=>3,'select'=>0);
        $educationlist[]=array('name'=>'中专学历','namevalue'=>4,'select'=>0);
        $educationlist[]=array('name'=>'专科学历','namevalue'=>5,'select'=>0);
        $educationlist[]=array('name'=>'本科学历','namevalue'=>6,'select'=>0);
        $educationlist[]=array('name'=>'本科以前学历','namevalue'=>7,'select'=>0);
        foreach($educationlist as $k=>$v){

            $namevalue= $v['namevalue'];
            if($namevalue==$education){
                $educationlist[$k]['select'] = 1;
            }else{
                $educationlist[$k]['select'] = 0;
            }
        }
        $jsondata['educationlist'] = $educationlist;
        $worklist[]=array('name'=>'工作经验不限','namevalue'=>1,'select'=>0);
        $worklist[]=array('name'=>'1-2两年工作经验','namevalue'=>2,'select'=>0);
        $worklist[]=array('name'=>'2-4两年工作经验','namevalue'=>3,'select'=>0);
        $worklist[]=array('name'=>'5年以上相关经验','namevalue'=>4,'select'=>0);
        foreach($worklist as $k=>$v){
            $namevalue= $v['namevalue'];
            if($namevalue==$work){
                $worklist[$k]['select'] = 1;
            }else{
                $worklist[$k]['select'] = 0;
            }
        }
        $jsondata['worklist'] = $worklist;
        $shop_id_list=$ShopObj->getUserShopArr($user_id,$Db_Recruit['shop_id'] );
        if(Buddha_Atom_Array::isValidArray($shop_id_list)){
            $jsondata['shop_id_list'] = $shop_id_list;
        }else{
            $shop_id_list = $ShopObj->getFiledValues(array('name','id as namevalue'),"id='{$Db_Recruit['shop_id']}'");
            $jsondata['shop_id_list'] = $shop_id_list;
        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '添加招聘之前的展示页面');
    }

    public function update(){

        if (Buddha_Http_Input::checkParameter(array('usertoken','recruit_name','recruit_id','recruitcat_id',
            'education','work','contacts','tel', 'shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $RecruitObj=new Recruit();
        $RecruitcatObj = new Recruitcat();
        $ShopObj=new Shop();
        $UserObj = new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $recruit_id = (int)Buddha_Http_Input::getParameter('recruit_id');

        $recruit_name = Buddha_Http_Input::getParameter('recruit_name');
        $recruitcat_id = Buddha_Http_Input::getParameter('recruitcat_id');
        $pay = Buddha_Http_Input::getParameter('pay');
        $education = Buddha_Http_Input::getParameter('education');
        $work = Buddha_Http_Input::getParameter('work');
        $recruit_start_time = Buddha_Http_Input::getParameter('recruit_start_time');
        $recruit_end_time = Buddha_Http_Input::getParameter('recruit_end_time');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $treatment = Buddha_Http_Input::getParameter('treatment');
        $number = Buddha_Http_Input::getParameter('number');
        $is_remote = Buddha_Http_Input::getParameter('is_remote');

        $contacts = Buddha_Http_Input::getParameter('contacts');
        $tel = Buddha_Http_Input::getParameter('tel');

        $recruit_desc = Buddha_Http_Input::getParameter('recruit_desc');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色');
        }

       if(!$RecruitObj->isRecruitBelongToUser($recruit_id,$user_id)){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000031, '招聘信息的主人不是目前的用户');
       }



        if(!$RecruitcatObj->isHasRecord($recruitcat_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000030, '招聘分类不存在');

        }

        if($ShopObj->getShopOfSureToUserTotalInt($shop_id,$user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000028, '您还没用创建店铺，或者店铺还未通过审核！');
        }

        if(!$ShopObj->isShopBelongToUser($shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000029, '此店铺不属于当前用户');
        }





      //  $Db_level = $ShopObj->getSingleFiledValues(array('level0', 'level1', 'level2', 'level3'), "user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
        $data = array();
        $data['recruit_name'] = $recruit_name;
        $data['user_id'] = $user_id;
        $data['recruit_id'] = $recruitcat_id;
        $data['shop_id'] = $shop_id;
        $data['pay'] = $pay;
        $data['education'] = $education;
        $data['work'] = $work;
        $data['treatment'] = $treatment;
        $data['number'] = $number;
        $data['contacts'] = $contacts;
        $data['tel'] = $tel;
//        $data['level0'] = $Db_level['level0'];
//        $data['level1'] = $Db_level['level1'];
//        $data['level2'] = $Db_level['level2'];
//        $data['level3'] = $Db_level['level3'];
        $data['recruit_start_time'] = strtotime($recruit_start_time);
        $data['recruit_end_time'] = strtotime($recruit_end_time);
        $data['recruit_desc'] = $recruit_desc;


        $RecruitObj->edit($data,$recruit_id);

        $is_needcreateorder = 0;
        $Services = '';
        $param = array();
        //$remote为1表示发布异地产品添加订单
        if($is_remote==1){
            $is_needcreateorder = 1;
            $Services = 'payment.remoteinfo';
            $param = array('good_id'=>$recruit_id,'good_table'=>'recruit');


        }

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['recruit_id'] = $recruit_id;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;
        $jsondata['db_isok'] = '1';
        $jsondata['db_msg'] = '编辑成功';



        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '编辑招聘');


    }


}