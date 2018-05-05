<?php

/**
 * Class ActivityController
 */
class ActivityController extends Buddha_App_Action
{

    protected $tablenamestr;
    protected $tablename;
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
        $this->tablenamestr='活动';
        $this->tablename='activity';
    }



    /**
     * 代理商：活动审核之前必须请求详情页面
     */

    public function beforeverify()
    {
        $host= Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('activity_id','usertoken','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $SingleinformationObj = new Singleinformation();
        $ShopObj = new Shop();
        $CommonObj = new Common();
        $ActivityObj = new Activity();
        $CustomObj = new Custom();

        $activity_id =  (int)Buddha_Http_Input::getParameter('activity_id')?(int)Buddha_Http_Input::getParameter('activity_id'):0;
        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if(!$UserObj->isHasAgentPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$SingleinformationObj->isOwnerBelongToAgentByLeve3($activity_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000021, '此单页信息不属于当前的代理商管理');
        }

        if(!$CommonObj->isIssureByTableid($activity_id,'singleinformation')){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 50000002	, '已经审核过了，请不要重复审核！');
        }


        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $shop_id =(int) Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;


//      $fields = ' id AS activity_id,add_time,click_count, shop_id, name, brief,`desc`,is_sure';
        $fields = 'id AS activity_id, shop_id, name , address, brief, prize,
                    type as typeid,vode_type as api_vodetypeid,start_date,end_date,sign_start_time,sign_end_time,add_time,click_count, `desc` ,is_sure';
        if($b_display==1){

            $fields.=' , activity_img AS img ';

        }elseif($b_display==2){

            $fields.=' , activity_thumb AS img ';
        }

        $where=" id ='{$activity_id}' ";

        if($shop_id>0){

            $where.=" AND shop_id='{$shop_id}' ";
        }

        $sql =" SELECT {$fields} FROM {$this->prefix}activity  WHERE {$where} ";



        $Db_Activity_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_Activity_arr)){

            $Db_Activity=$Db_Activity_arr[0];
            if($Db_Activity['img']){
                $Db_Activity['img']=$host.$Db_Activity['img'];
            }else{
                $Db_Activity['img'] = '';
            }

            $Db_Activity['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['desc']);
            $Db_Activity['shop_name']=$ShopObj->getShopnameFromShopid($Db_Activity['shop_id']);
            $Db_Activity['shop_img']=$host.$ShopObj->getShopImgFromShopid($Db_Activity['shop_id'],$b_display);
            $Db_Activity['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['desc']);
            $Db_Activity['api_startdate']=$CommonObj->getDateStrOfTime($Db_Activity['start_date'],0,1);
            $Db_Activity['api_enddate']=$CommonObj->getDateStrOfTime($Db_Activity['end_date'],0,1);
            $Db_Activity['api_add_time']=$CommonObj->getDateStrOfTime($Db_Activity['add_time'],0,1,1);
            $signtime=$ActivityObj->getRegistrationtime($Db_Activity);

            $Db_Activity['api_signstarttime']=$signtime['api_signstarttime'];

            $Db_Activity['api_signendtime']=$signtime['api_signendtime'];
            /*活动：审核*/
            $Db_Activity['issureServices']=array(
                'Services' => 'activity.verify',
                'param'=> array('is_sure'=>$Db_Activity['is_sure'],'activity_id'=>$Db_Activity['activity_id'])
            );







            /*查询该活动的自定义表单*/
            $Customfiledarr=array('id as custom_id','t_id as table_id','t_name as table_name','arrkey','add_time','click_num','sub','c_type','sort','sub_1','c_title');
            $Db_Custom = $CustomObj->getFiledValues($Customfiledarr," t_name='{$this->tablename}' AND t_id='{$activity_id}' ORDER BY sort ASC");

            $Db_Activity['custom'] = array();

            if(Buddha_Atom_Array::isValidArray($Db_Custom)){

                foreach ($Db_Custom as $k=>$v){
                    $Db_Custom[$k]['api_addtime']=$CommonObj->getDateStrOfTime($v['add_time'],0,1);
                    $Db_Custom[$k]['CustomServices']=array(
                        'Services'=>'activity.userregistration',
                        'param'=> array('custom_id' => $v['custom_id']),
                    );
                }
                $Db_Activity['custom'] = $Db_Custom;
            }

            $jsondata['list'] = $Db_Activity;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商进行{$this->tablenamestr}之前必须请求的详情页面");

    }


    /**
     *
     * @author csh
     *  代理商：活动管理列表
     */

    public function agentmanageactivitymore()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj=new Common();
        $UserObj=new User();
        $ShopObj= new Shop();
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasAgentPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商角色！');
        }


        $api_keyword = Buddha_Http_Input::getParameter('api_keyword')?Buddha_Http_Input::getParameter('api_keyword'):'';

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $view = Buddha_Http_Input::getParameter('view')?Buddha_Http_Input::getParameter('view'):0;


        $where="  level3='{$Db_User['level3']}' ";


        if(Buddha_Atom_String::isValidString($api_keyword)){

            $where.= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword,array('name','number'));

        }

        if($view){
            switch($view){
                case 2;

                    $where.=' and is_sure=0';
                    break;
                case 3;

                    $where.=" and is_sure=1";
                    break;
                case 4;

                    $where.=" and is_sure=4 ";
                    break;
                case 5;

                    $where.=" and isdel=0 and is_sure=1 and buddhastatus=1";
                    break;
            }
        }
        $fileds = ' id AS activity_id,name, is_sure, shop_id,shop_name, buddhastatus, brief, state ,level3 ';

        if($b_display==1){
            $fileds.=' , activity_img AS img ';
        }elseif($b_display==2){
            $fileds.=' , activity_thumb AS  img ';
        }

        $orderby = " ORDER BY add_time DESC ";


        $sql =" SELECT  {$fileds}  
                FROM {$this->prefix}activity WHERE {$where} 
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $Db_Activity = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();

        if(Buddha_Atom_Array::isValidArray($Db_Activity)){

            $jsondata['nav'] = $ShopObj->getPersonalCenterHeaderMenu('activity.agentmanageactivitymore');

            foreach($Db_Activity as $k=>$v){

                if($v['shop_id']!=0){
                    $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                    $name='商家：'.$shop_name['name'];
                }else{
                    $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                    $name='个人：'.$shop_name['name'];
                }



                if($v['is_sure']==0){

                    $Db_Activity[$k]['api_auditstateimg']=$host.'apistate/menuplus/weishenhe.png';

                    /*活动：审核状态（只有未审核的活动才显示）*/
                    $Db_Activity[$k]['issureServices']=array(
                        'Services' => 'activity.beforeverify',
                        'param'=> array('activity_id'=>$v['activity_id'])
                    );

                }elseif($v['is_sure']==4){

                    $Db_Activity[$k]['api_auditstateimg']=$host.'apistate/menuplus/weitonguo.png';

                }elseif($v['is_sure']==1){

                    $Db_Activity[$k]['api_auditstateimg']=$host.'apistate/menuplus/yitonguo.png';


                    /*单页信息：上下架（只有正常的单页信息才显示）*/
                    $Db_Activity[$k]['buddhastatusServices']=array(
                        'Services' => 'activity.offshelf',
                        'param'=> array('shelf'=>$v['buddhastatus'],'activity_id'=>$v['activity_id'])
                    );

                    if($v['buddhastatus']==1){

                        $Db_Activity[$k]['api_buddhastatus']='上 架';

                    }else if($v['buddhastatus']==0){

                        $Db_Activity[$k]['api_buddhastatus']='下 架';
                    }
                }

                if(Buddha_Atom_String::isValidString($v['img'])){

                    $Db_Activity[$k]['api_img']=$host.$v['img'];

                }else{

                    $Db_Activity[$k]['api_img']='';
                }


//                if($v['state']==1){
//
//                    $Db_Singleinformation[$k]['api_state']='停 用';
//
//                }else if($v['state']==0){
//
//                    $Db_Singleinformation[$k]['api_state']='启 用';
//
//                }


                if(!Buddha_Atom_String::isValidString($v['shop_name'])){

                    $Db_Activity[$k]['shop_name']=$ShopObj->getShopnameFromShopid($v['shop_id']);

                }
                unset( $Db_Activity[$k]['img']);
                unset( $Db_Activity[$k]['level3']);

                $Db_Activity[$k]['view']=array(
                    "Services"=> "activity.verify",
                    "param"=>array('activity_id'=>$v['activity_id']),
                );
            }


            $tablewhere=$this->prefix.'singleinformation';

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);


            $jsondata['page'] =  $temp_Common['page'];
            $jsondata['pagesize'] =  $temp_Common['pagesize'];
            $jsondata['totalrecord'] =  $temp_Common['totalrecord'];
            $jsondata['totalpage'] =  $temp_Common['totalpage'];
            $jsondata['list'] = $Db_Activity;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商：{$this->tablenamestr}管理列表");

    }



    /**
     * 个人中心：代理商活动审核
     */
    public function verify(){

        if (Buddha_Http_Input::checkParameter(array('activity_id','usertoken','is_sure'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ActivityObj = new Activity();
        $CommonObj = new Common();

        $activity_id =  Buddha_Http_Input::getParameter('activity_id');
        /*审核状态：1通过审核  ；4未通过审核*/
        $is_sure = (int) Buddha_Http_Input::getParameter('is_sure')?(int) Buddha_Http_Input::getParameter('is_sure'):0;


        $remarks = Buddha_Http_Input::getParameter('remarks')? Buddha_Http_Input::getParameter('remarks'):'';
        /*4未通过审核 必须填写备注*/
        if($is_sure==4 AND !Buddha_Atom_String::isValidString($remarks))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        /*判断$is_sure审核状态码 是否属于 1,4*/
        if(!$CommonObj->isIdInDataEffectiveById($is_sure,array(1,4))){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }
        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if(!$UserObj->isHasAgentPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$ActivityObj->isOwnerBelongToAgentByLeve3($activity_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $data = array();
        $data['is_sure'] = $is_sure ;
        $data['remarks'] = $remarks ;
        $Db_Activity_num=$ActivityObj->edit($data,$activity_id);


        $jsondata = array();
        $jsondata['data'] = array();
        $datas=array();
        if($Db_Activity_num){
            $datas['is_ok']=1;
            $datas['is_msg']=$this->tablenamestr.'审核成功！';
        }else{
            $datas['is_ok']=0;
            $datas['is_msg']=$this->tablenamestr.'审核失败！';
        }

        $jsondata['activity_id'] = $activity_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商{$this->tablenamestr}审核");

    }

    /**
     *  个人中心：代理商活动下架
     * @author wph 2017-09-14
     */
    public function offshelf(){

        if (Buddha_Http_Input::checkParameter(array('activity_id','usertoken','shelf'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ActivityObj = new Activity();

        $activity_id =  Buddha_Http_Input::getParameter('activity_id');
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

        if(!$UserObj->isHasAgentPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$ActivityObj->isOwnerBelongToAgentByLeve3($activity_id,$level3)){
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
        $ActivityObj->edit($data,$activity_id);

        $jsondata = array();
        $jsondata['activity_id'] = $activity_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['msg'] = $msg;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商'.$this->tablenamestr.$msg);


    }

    /**
     * 个人中心： 活动合作对象列表（商家、产品、个人）
     */
    public function coomore()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('activity_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ActivityObj=new Activity();


        $activity_id = Buddha_Http_Input::getParameter('activity_id');
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')? Buddha_Http_Input::getParameter('b_display'):2;
        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        /*是否从个人中心相应的功能块点出的；0为否；1为是*/
        $is_usercenter = (int) Buddha_Http_Input::getParameter('is_usercenter')?Buddha_Http_Input::getParameter('is_usercenter'):0;

        $Db_Activity = $ActivityObj->getSingleFiledValues(array('id','type','vode_type'),"  id ='{$activity_id}'");

        if($Db_Activity['type']==1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误，商家个人没有合作对象!');
        }

        if(strlen($usertoken)>2 && $is_usercenter==1){
            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
            $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
            $user_id = $Db_User['id'];
            $where=" user_id='{$user_id}' ";
            if(!$UserObj->isHasMerchantPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
            }

            if(!$UserObj->isHasAgentPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商会员角色！');
            }else{
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000027, '代理商角色不允许查看，代理商员角色不具备该功能！');
            }

            if(!$UserObj->isHasPartnerPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000023, '你还未申请合伙人会员角色！');
            }else{
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000024, '合伙人角色不允许查看，合伙人角色不具备该功能！');
            }

            if(!$UserObj->isHasUserPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000021, '你还未申请普通会员角色！');
            }else{
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000025, '普通会员角色不允许查看，普通会员角色不具备该功能！');
            }
        }


        $where.=" c.act_id = '{$activity_id}' ";

        /*是否需要审核*/
        $api_isExamine = Buddha_Http_Input::getParameter('api_isExamine')? Buddha_Http_Input::getParameter('api_isExamine'):1;
        if($api_isExamine==2){
            $where.=' AND c.is_sure=1 ';
        }

        $fields = 'c.id AS activitycooperation_id, c.shop_id, c.shop_name, c.praise_num ';
        if(strlen($usertoken)>2 && $is_usercenter==1){
            $fields.=" , c.message, c.u_name, c.u_phone, c.is_sure, c.sore ";
        }

        $api_keyword = Buddha_Http_Input::getParameter('api_keyword');
        if($api_keyword){
            $where.=" AND  shop_name LIKE '%$api_keyword%' ";
        }
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);


        $orderby=' ORDER BY c.praise_num DESC';


        if(($Db_Activity['type'] == 3 && $Db_Activity['vode_type'] == 2)){

            $sql ="SELECT {$fields},u.logo AS img 
                   FROM {$this->prefix}activitycooperation AS c
                   LEFT JOIN {$this->prefix}user AS u 
                   ON c.shop_id = u.id 
                   WHERE {$where} {$orderby} ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize);
            $Db_Activitycooperation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            $temp_sql ="SELECT count(*) AS total 
                        FROM {$this->prefix}activitycooperation AS c
                        LEFT JOIN {$this->prefix}user AS u 
                        ON c.shop_id = u.id 
                        WHERE {$where} ";
            $count_arr = $this->db->query($temp_sql)->fetchAll(PDO::FETCH_ASSOC);

        }elseif($Db_Activity['type']==2 || ($Db_Activity['type'] ==3 && $Db_Activity['vode_type'] == 1)){
            if($b_display==1){
                $fields.=' , s.medium AS img  ';
            }elseif($b_display==2){
                $fields.=' , s.small AS img ';
            }
            $sql =" SELECT {$fields} 
                    FROM {$this->prefix}activitycooperation AS c 
                    LEFT JOIN {$this->prefix}shop AS s 
                    ON s.id = c.shop_id 
                    WHERE {$where} {$orderby} ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize);

            $Db_Activitycooperation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            $temp_sql =" SELECT count(*) AS total 
                         FROM {$this->prefix}activitycooperation AS c 
                         LEFT JOIN {$this->prefix}shop AS s 
                         ON s.id = c.shop_id 
                         WHERE {$where} {$orderby} ";
            $count_arr = $this->db->query($temp_sql)->fetchAll(PDO::FETCH_ASSOC);

        }elseif($Db_Activity['type']==3 || ($Db_Activity['type'] == 3 && $Db_Activity['vode_type'] == 3)){
            if($Db_Activity['type']==2){
                if($b_display==1){
                    $fields.=' , s.goods_img AS img ';
                }elseif($b_display==2){
                    $fields.=' , s.goods_thumb AS img ';
                }
                $sql =" SELECT {$fields} 
                        FROM {$this->prefix}activitycooperation AS c
                        LEFT JOIN {$this->prefix}supply AS s
                        ON s.id = c.shop_id 
                        WHERE {$where} {$orderby} ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize);
                $Db_Activitycooperation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

                $temp_sql ="SELECT count(*) AS total 
                            FROM {$this->prefix}activitycooperation AS c
                            LEFT JOIN {$this->prefix}supply AS s
                            ON s.id = c.shop_id 
                            WHERE {$where} {$orderby} ";
                $count_arr = $this->db->query($temp_sql)->fetchAll(PDO::FETCH_ASSOC);

            }
        }


        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Activitycooperation)){


            if($Db_Activity['type']==1 || ($Db_Activity['type'] == 3 && $Db_Activity['vode_type'] == 2)){
                foreach ($Db_Activitycooperation as $k => $v) {
                    if(Buddha_Atom_String::isValidString($v['img'])){
                        $Db_Activitycooperation[$k]['img'] = $host.$v['img'];
                    }else{
                        $img=$UserObj->DefaultUserLogo();
                        $Db_Activitycooperation[$k]['img'] = $host.$img;
                    }
                }
            }else{
                foreach ($Db_Activitycooperation as $k => $v) {
                    if(Buddha_Atom_String::isValidString($v['img'])){
                        $Db_Activitycooperation[$k]['img'] = $host.$v['img'];
                    }else{
                        $Db_Activitycooperation[$k]['img'] = '';
                    }
                }
            }

            foreach ($Db_Activitycooperation as $k=>$v){
                if(($Db_Activity['type'] == 3 && $Db_Activity['vode_type'] == 2)){
                    $Db_Activitycooperation[$k]['view']=array(
                        'server'=>'',
                        'param'=>array(),
                    );
                }elseif($Db_Activity['type']==2 || ($Db_Activity['type'] ==3 && $Db_Activity['vode_type'] == 1)){
                    $Db_Activitycooperation[$k]['view']=array(
                        'server'=>'shop.view',
                        'param'=>array('shop_id'=>$v['shop_id']),
                    );
                }elseif($Db_Activity['type']==3 || ($Db_Activity['type'] == 3 && $Db_Activity['vode_type'] == 3)) {
                    $Db_Activitycooperation[$k]['view']=array(
                        'server'=>'multisingle.supplysingle',
                        'param'=>array('supply_id'=>$v['shop_id']),
                    );
                }
            }


            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }

            $jsondata['page'] = $page;
            $jsondata['pagesize'] = $pagesize;
            $jsondata['totalrecord'] = $rcount;
            $jsondata['totalpage'] = $pcount;
            $jsondata['activity_id'] = $activity_id;
            $jsondata['list'] = $Db_Activitycooperation;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'合作对象列表（商家、产品、个人）');

    }


    /**
     *  个人中心：活动合作对象详情（商家、产品、个人）
     */
    public function cooview()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('activitycooperation_id','activity_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ActivityObj=new Activity();


        $activitycooperation_id = Buddha_Http_Input::getParameter('activitycooperation_id');
        $where =' c.id='.$activitycooperation_id.' ';

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')? Buddha_Http_Input::getParameter('b_display'):2;


        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        if(strlen($usertoken)>2){
            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
            $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
            $user_id = $Db_User['id'];
            $where.=" AND c.u_id='{$user_id}' ";
        }


        $activity_id = Buddha_Http_Input::getParameter('activity_id');
        $Db_Activity=$ActivityObj->getSingleFiledValues(array('id','type','vode_type','start_date','sign_start_time','sign_end_time','end_date')," id ='{$activity_id}' ");


        $fields = 'c.id AS activitycooperation_id, c.shop_id, c.shop_name, c.praise_num, c.message, c.u_name, c.u_phone, c.is_sure, c.sore ';


        if($Db_Activity['type']==1 || ($Db_Activity['type'] == 3 && $Db_Activity['vode_type'] == 2)){
            $sql ="SELECT {$fields},u.logo AS img  
                    FROM {$this->prefix}activitycooperation AS c 
                    LEFT JOIN {$this->prefix}user AS u 
                    ON c.shop_id = u.id 
                    WHERE {$where} ";
            $Db_Activitycooperation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        }elseif($Db_Activity['type']==2 || ($Db_Activity['type'] ==3 && $Db_Activity['vode_type'] == 1)){
            if($b_display==1){
                $fields.=' , s.medium AS img  ';
            }elseif($b_display==2){
                $fields.=' , s.small AS img ';
            }
            $sql =" SELECT {$fields} 
                    FROM {$this->prefix}activitycooperation AS c 
                    LEFT JOIN {$this->prefix}shop AS s 
                    ON s.id = c.shop_id 
                    WHERE {$where} ";
            $Db_Activitycooperation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }elseif($Db_Activity['type']==3 || $Db_Activity['vode_type'] == 3){
            if($Db_Activity['vode_type'] ==2){
                if($b_display==1){
                    $fields.=' , s.goods_img AS img ';
                }elseif($b_display==2){
                    $fields.=' , s.goods_thumb AS img ';
                }
                $sql =" SELECT {$fields} 
                    FROM {$this->prefix}activitycooperation AS c
                    LEFT JOIN {$this->prefix}supply AS s
                    ON s.id = c.shop_id 
                    WHERE {$where} ";
                $Db_Activitycooperation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        if($Db_Activity['type']==1 || ($Db_Activity['type'] == 3 && $Db_Activity['vode_type'] == 2)){
            if(Buddha_Atom_Array::isValidArray($Db_Activitycooperation)){
                $Db_Activitycooperation[0]['img']=$host.$Db_Activitycooperation[0]['img'];
            }else{
                $img=$UserObj->DefaultUserLogo();
                $Db_Activitycooperation[0]['img'] = $host.$img;
            }
        }else{
            if(Buddha_Atom_Array::isValidArray($Db_Activitycooperation)){
                $Db_Activitycooperation[0]['img']=$host.$Db_Activitycooperation[0]['img'];
            }else{
                $Db_Activitycooperation[0]['img'] = '';
            }
        }


        /*活动状态标志位 1=未开始 2=进行中 3=已结束 0=系统出错 */
        $api_activestatus = $ActivityObj->getActiveStatusInt($Db_Activity['start_date'],$Db_Activity['end_date']);

        /**
         * 0 ：不能报名   1：可以报名
         */
        $api_isenrol  =$ActivityObj->isActivityEnrole(
            $Db_Activity['start_date'],$Db_Activity['end_date'],
            $Db_Activity['sign_start_time'],$Db_Activity['sign_end_time']
        );



        $jsondata=array();
        $jsondata=$Db_Activitycooperation[0];
        $jsondata['api_isenrol'] = $api_isenrol;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动合作对象详情（商家、产品、个人）');

    }
///////////////////////////////////////////////////////////////////////////////////////////////

    /**
     *  首页活动投票：申请成为合作对象 前（当活动类型为：个人时没有 申请成为合作对象 前）
     */
    public function beforeapplicationcooperation()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken','table_id','table_name'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $CommonObj = new Common();
        $UserObj = new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 AND usertoken='{$usertoken}' ");
        if(!Buddha_Atom_Array::isValidArray($Db_User)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000051, '你还未登陆，请登陆后再操作!');
        }

        $user_id = $Db_User['id'];

        $table_id = (int)Buddha_Http_Input::getParameter('table_id') ? (int)Buddha_Http_Input::getParameter('table_id') : 0;
        $table_name = Buddha_Http_Input::getParameter('table_name') ? Buddha_Http_Input::getParameter('table_name') : '';
        $api_keyword = Buddha_Http_Input::getParameter('api_keyword') ? Buddha_Http_Input::getParameter('api_keyword') : '';

        if(!$CommonObj->isIdByTablenameAndTableid($table_name,$table_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效！');
        }

        /**获取活动的类型*/
        $Db_Table =$this->db->getSingleFiledValues(array('type','vode_type'), $table_name,"id={$table_id} AND isdel=0");
            /*是否显示下拉列表模块(选择框)*/
        $isshowselect = 0;
        $selectname = '';
            /*是否显示下拉列表模块下的店铺选择模块(选择框)*/
        $isshowshopselect = 0;

        /**活动的类型：商家 **/
        if(($Db_Table['type']==2) OR ($Db_Table['type']==3 AND $Db_Table['vode_type']==1)){
            $tablename = 'shop';
            $filed = array('id','name');
            $where = " user_id='{$user_id}' AND isdel=0 ";
            if(Buddha_Atom_String::isValidString($api_keyword)){
                $where.=" AND name=%'{$api_keyword}'% ";
            }
            $order = ' ORDER BY id DESC';
            $isshowselect = 1;
            $selectname = '店铺';

            /**活动的类型：个人 (同个人报名) **/
        }else if($Db_Table['type']==3 AND $Db_Table['vode_type']==2){
//            $tablename='user';
//            $filed=array('id','realname AS name');
//            $where= $locdata['sql'] ;
//            if(Buddha_Atom_String::isValidString($api_keyword)){
//                $where.=" AND realname LIKE'%{$api_keyword}'% ";
//            }
        /**活动的类型：产品**/
        }else if($Db_Table['type']==3 AND $Db_Table['vode_type']==3){
            $selecttablename = 'supply';
            $tablename = 'shop';

//            $filed = array('id','goods_name AS name');
//            $where = ' isdel=0 ';
//            if(Buddha_Atom_String::isValidString($shop_id)){
//                $where.=" AND shop_id=%'{$shop_id}'% ";
//            }
//            if(Buddha_Atom_String::isValidString($api_keyword)){
//                $where.=" AND goods_name=%'{$api_keyword}'% ";
//            }
//            /*产品列表最大显示100条*/
//            $where.=' limit 100';
            $filed = array('id','name');
            $where = " user_id='{$user_id}' AND isdel=0 ";
            if(Buddha_Atom_String::isValidString($api_keyword)){
                $where.=" AND name=%'{$api_keyword}'% ";
            }
            $order = ' ORDER BY id DESC';

            $isshowselect = 1;
            $isshowshopselect = 1;
            $selectname = '产品';

        }
        if(Buddha_Atom_String::isValidString($selectname)){
            $selectname .= '选择';
        }


        $Db_Table_S = $this->db->getFiledValues($filed, $tablename, $where.$order);

        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_Table_S)){
            foreach ($Db_Table_S as $k=>$v){
                if($k==0){
                    $Db_Table_S[$k]['select']=1;
                }else{
                    $Db_Table_S[$k]['select']=0;
                }
                if($Db_Table['type']==3 AND $Db_Table['vode_type']==3){
                    $Db_Table_S[$k]['shopinformationselect']=array(
                        'Services'=>'ajaxregion.getBelongShopInformationByShopid',
                        'param'=>array('table_name'=>$selecttablename,'shop_id'=>$v['id']),
                    );
                }else{
                    $Db_Table_S[$k]['shopinformationselect']=array(
                        'Services'=>'',
                        'param'=>array(),
                    );
                }
            }
            $jsondata['isshowselect']=$isshowselect;
            $jsondata['selectname']=$selectname;
            $jsondata['isshowshopselect']=$isshowshopselect;
            $jsondata['application']=array(
                'Services'=>'activity.applicationcooperation',
                'param'=>array('table_id'=>$table_id,'table_name'=>$table_name),
            );
            $jsondata['list'] = $Db_Table_S;
        }else{
            /*除了个人以外：没有信息的提示*/
            if(!($Db_Table['type']==3 AND $Db_Table['vode_type']==2)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效！');
            }
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '首页活动投票：申请成为合作对象前');
    }

    /**
     *  首页活动投票：申请成为合作对象
     */
    public function applicationcooperation()
    {

        if (Buddha_Http_Input::checkParameter(array('usertoken','table_id','table_name','shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3','tel');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 AND usertoken='{$usertoken}' ");
        if(!Buddha_Atom_Array::isValidArray($Db_User)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000051, '你还未登陆，请登陆后再操作!');
        }

        $user_id = $Db_User['id'];

        $table_id = (int)Buddha_Http_Input::getParameter('table_id') ? (int)Buddha_Http_Input::getParameter('table_id') : 0;
        $table_name = Buddha_Http_Input::getParameter('table_name') ? Buddha_Http_Input::getParameter('table_name') : '';
        $name = Buddha_Http_Input::getParameter('name') ? Buddha_Http_Input::getParameter('name') : '';
        $phone = Buddha_Http_Input::getParameter('phone') ? Buddha_Http_Input::getParameter('phone') : '';
        $massage = Buddha_Http_Input::getParameter('massage') ? Buddha_Http_Input::getParameter('massage') : '';
        /**投票合作对象为：商家、个人、产品；这里合作店铺ID 或者 产品 或者 个人的内码ID*/
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id') ? (int)Buddha_Http_Input::getParameter('shop_id') : 0;
        /**投票合作对象为：商家、个人、产品；这里合作店铺ID 或者 产品 或者 个人的名称*/
        $shop_name = Buddha_Http_Input::getParameter('shop_name') ? Buddha_Http_Input::getParameter('shop_name') : '';


////////////////////////////////////////////////////////////////////////////////

        $activitycooperationwhere = " act_id={$table_id} and shop_id={$shop_id}";

        $ActivitycooperationObj = new Activitycooperation();

        $Db_Activitycooperation_num = $ActivitycooperationObj->countRecords($activitycooperationwhere);
        if($Db_Activitycooperation_num){//该对象已经报名
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000052, '该对象已经在该活动中申请过合作对象了，请不要重复申请，或者请选择其他的合作对象后再申请!');
        }

////////////////////////////////////////////////////////////////////////////////


        /**获取活动的类型*/
        $Db_Table = $this->db->getSingleFiledValues(array('type','vode_type'), $table_name,"id={$table_id} AND isdel=0");

        /**活动的类型：商家 **/
        if(($Db_Table['type']==2) OR ($Db_Table['type']==3 AND $Db_Table['vode_type']==1)){
            $tablename = 'shop';
            $filed=array('name');
            /**活动的类型：个人 (同个人报名) **/
        }else if($Db_Table['type']==3 AND $Db_Table['vode_type']==2){

            /**活动的类型：产品**/
        }else if($Db_Table['type']==3 AND $Db_Table['vode_type']==3) {
            $tablename = 'supply';
            $filed=array('goods_name as name');
        }
        /****验证投票合作对象内码ID是否有效****/
        if(!$this->db->countRecords($tablename," id='{$shop_id}' ")){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000053, '内码ID无效!');
        }

        /**活动的类型：商家 或者 产品    合作对象的内码ID 和 投票合作对象名称 不能为空**/
        if((($Db_Table['type']==2) OR ($Db_Table['type']==3 AND $Db_Table['vode_type']==1)) OR ($Db_Table['type']==3 AND $Db_Table['vode_type']==3)){
            if(!Buddha_Atom_String::isValidString($shop_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误');
            }
        }

        /**获取合作对象的名称(根据合作对象的ID)**/
        $Db_Tabe=$this->db->getSingleFiledValues($filed, $table_name,"shop_id={$shop_id} AND isdel=0 AND user_id='{$user_id}'");
        $shop_name=$Db_Tabe['name'];

        $data['u_id'] = $user_id;
        $data['act_id'] = $table_id;
        $data['sore'] = 1;
        $data['message'] = $massage;
        if(Buddha_Atom_String::isValidString($phone) ){
            if(Buddha_Atom_String::isValidString($Db_User['mobile'])){
                $data['u_phone'] = $Db_User['mobile'];
            }else{
                $data['u_phone'] = $Db_User['tel'];
            }
        }
        if(Buddha_Atom_String::isValidString($name)){
            $data['u_name'] = $Db_User['realname'];
        }else{
            $data['u_name'] = $name;
        }

        /*投票对象为个人时*/
        if($Db_Table['type'] == 3 AND $Db_Table['vode_type'] == 2){
            $data['shop_id'] = $user_id;
            $data['shop_name'] = $Db_User['realname'];
        }else{
            $data['shop_id'] = $shop_id;
            $data['shop_name'] = $shop_name;
        }
        $data['add_time'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['sore_time'] = Buddha::$buddha_array['buddha_timestamp'];
        $Db_Activitycooperation_id = $ActivitycooperationObj->add($data);
        if($Db_Activitycooperation_id){
            $jsondata['db_isok'] = 1;
            $jsondata['db_msg'] ='申请成为合作对象成功，正在等待活动发起人审核中....';
        }else{
            $jsondata['db_isok'] = 0;
            $jsondata['db_msg'] = '申请成为合作对象失败！';
        }

        $jsondata['activitycooperation_id']=$Db_Activitycooperation_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '首页活动投票：申请成为合作对象');
    }


    /**
     *  首页活动投票：合作对象投票排名
     */

    public function voderanking()
    {
        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('activity_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $CommonObj= new Common();
        $activity_id= (int)Buddha_Http_Input::getParameter('activity_id')?(int)Buddha_Http_Input::getParameter('activity_id'):0;
        if(!$CommonObj->isIdByTablenameAndTableid($this->tablename,$activity_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效！');
        }

//======查询商家排名===
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $orderby=' ORDER BY praise_num DESC';
        $filed=array('id AS activitycooperation_id','shop_id','shop_name','praise_num');
        $ActivitycooperationObj=new Activitycooperation();
        $where=' act_id='.$activity_id;
        $Db_Activitycooperation = $ActivitycooperationObj->getFiledValues ($filed, $where . $orderby.Buddha_Tool_Page::sqlLimit ( $page, $pagesize));//查询商家排名
        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_Activitycooperation)){

            foreach($Db_Activitycooperation as $k=>$v){
                $Db_Activitycooperation[$k]['shop_name']=mb_substr($v['shop_name'],0,15) ;
                $Db_Activitycooperation[$k]['ranking']=$k+1;
            }


            $ActivityObj =new Activity();
            $Db_Activity=$ActivityObj->getSingleFiledValues(array('activity_img'),'id='.$activity_id);//查询活动的图片
            if(Buddha_Atom_Array::isValidArray($Db_Activity)){
                $jsondata['api_img']=$host.$Db_Activity['activity_img'];
            }else{
                $jsondata['api_img']='';
            }

            $tablewhere=$this->prefix.'activitycooperation';
            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];
            $jsondata['list']=$Db_Activitycooperation;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '首页活动投票：合作对象投票排名');

    }


    /**
     * 首页活动投票：规则 / 奖品
     */
    public function vodeprize()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('activity_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $ActivityObj = new Activity();
        $CommonObj = new Common();
        $MoregalleryObj =  new Moregallery();

        $activity_id = (int)Buddha_Http_Input::getParameter('activity_id')?(int)Buddha_Http_Input::getParameter('activity_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):0;
        if(!$CommonObj->isIdByTablenameAndTableid($this->tablename,$activity_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效！');
        }

        $where=" id='{$activity_id}' AND buddhastatus=0 AND is_sure=1 AND isdel=0";
        $Db_Activity = $ActivityObj->getSingleFiledValues(array('start_date','end_date'),$where);

        /**↓↓↓↓↓↓↓↓↓↓↓ 检查活动是否过期：过期要下架 ↓↓↓↓↓↓↓↓↓↓↓**/
        $newtime = Buddha::$buddha_array['buddha_timestamp'];
        if(!($Db_Activity['start_date']<$newtime AND $newtime<$Db_Activity['end_date'])){
            $data['buddhastatus']=1;
            $ActivityObj->edit($data,$activity_id);
        }
        /**↑↑↑↑↑↑↑↑↑↑ 检查活动是否过期：过期要下架 ↑↑↑↑↑↑↑↑↑↑**/

        $Activityfiled=array('id AS activity_id','brief','`desc`','prize');

        if($b_display==1){
            array_push($Activityfiled,'activity_large AS img');
        }elseif($b_display==2){
            array_push($Activityfiled,'activity_img AS img');
        }
        $Db_Activity = $ActivityObj->getSingleFiledValues($Activityfiled,$where);


        $jsondata = array();
        $jsondata['activity_id'] =  $activity_id;
        $jsondata['brief'] =  '';
        $jsondata['desc'] =  '';
        $jsondata['prize'] =  '';
        $jsondata['img'] =  array();

        if(Buddha_Atom_Array::isValidArray($Db_Activity))
        {
            $Db_Activity['desc'] = Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['desc']);
            $jsondata=$Db_Activity;
    //     ↓↓↓↓↓↓冠名商家查询↓↓↓↓↓↓
            $Moregalleryfiled=array('id AS moregallery_id','shop_id');
            if($b_display==1){
                array_push($Moregalleryfiled,'goods_large AS img');
            }elseif($b_display==2){
                array_push($Moregalleryfiled,'goods_img AS img');
            }
            $Db_Moregallery= $MoregalleryObj->getFiledValues($Moregalleryfiled,"goods_id={$activity_id} and tablename='{$this->tablename}' and webfield='file_title'");
            //     ↑↑↑↑↑↑↑↑↑↑冠名商家查询 ↑↑↑↑↑↑↑↑↑↑
            //     ↓↓↓↓↓↓ 头部轮播图组装 ↓↓↓↓↓↓

            $carousel=array();
            $carousel[0]['img']=$Db_Activity['img'];
            unset($Db_Activity['img']);
            $carousel[0]['shop_id']=0;
            foreach($Db_Moregallery as $k=>$v){
                array_push($carousel,$v);
            }

            foreach($carousel as $k=>$v){
                $carousel[$k]['img']=$host.$v['img'];
                $carousel[$k]['Services']='shop.view';
                $carousel[$k]['param']=array('shop_id'=>$v['shop_id']);
            }
            //     ↑↑↑↑↑↑↑↑↑↑头部轮播图组装 ↑↑↑↑↑↑↑↑↑↑
            $jsondata['img']=$carousel;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '首页活动投票：规则/奖品');
    }

    /**
     * 活动报名列表
     */

    public function appmore()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('activity_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $fields='';$where='';
        $UserObj=new User();
        $CommonObj=new Common();

        $api_keyword = (int)Buddha_Http_Input::getParameter('api_keyword')?Buddha_Http_Input::getParameter('api_keyword'):0;
        if(!empty($api_keyword)){
            $where.=" (ap.username LIKE  '%{$api_keyword}%' OR  ap.phone LIKE  '%{$api_keyword}%') ";
        }

        $page = (int)Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):15;

        $activity_id = Buddha_Http_Input::getParameter('activity_id');
        $where.=" ap.ac_id = '{$activity_id}' ";


        $usertoken = Buddha_Http_Input::getParameter('usertoken');


        /*在活动报名列表中是否需要给每一个报名者加入查看详情的链接 （只有在个人中心点击进入的时候才能查看报名者详情）  0为否 ； 1为是 */
        /* 如果传入 usertoken 表示是个人中心点击进入*/
        $api_islink=0;
        if(strlen($usertoken)>2){
            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
            $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
            $user_id = $Db_User['id'];
        }

        $fields .= ' ap.id AS cooperation_id, ap.username ';

        /* api_soure 个人商家报名查询   $source来源 0 为首页 1为人人中心 默认为0*/
        $api_soure= (int)Buddha_Http_Input::getParameter('api_soure')?Buddha_Http_Input::getParameter('api_soure'):0;
        if($api_soure==1){
            $fields = ' AND ap.message, ap.state, ap.phone,';
        }

        $orderby=' ORDER BY ap.addtime DESC ';
        $sql =" SELECT {$fields},u.logo AS img 
                FROM {$this->prefix}activityapplication AS ap 
                LEFT JOIN {$this->prefix}user AS u 
                ON ap.u_id = u.id 
                WHERE {$where} {$orderby} ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize);
        $Db_Activityapplication = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Activityapplication)){

            $pagearray=  $CommonObj->pagination(
                " {$this->prefix}activityapplication AS ap
                  LEFT JOIN {$this->prefix}user AS u
                  ON ap.u_id = u.id ",
                  $where,$pagesize,$page);


            foreach($Db_Activityapplication as $k=>$v){

                if($api_soure==0){
                    $Db_Activityapplication[$k]['username']=mb_substr($v['username'],0,1).'**';
                }
                if(empty($v['img'])){

                    /*非注册用户或没有头像 给默认头像*/
                    $img=$UserObj->DefaultUserLogo();
                    $Db_Activityapplication[$k]['img']=$host.$img;

                }else{

                    $Db_Activityapplication[$k]['img']=$host.$v['img'];
                }

            }

            $tablewhere="{$this->prefix}activityapplication AS ap 
                LEFT JOIN {$this->prefix}user AS u 
                ON ap.u_id = u.id";

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];

            $jsondata['activity_id'] = $activity_id;
            $jsondata['list'] = $Db_Activityapplication;
        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动报名列表');
    }



    /**
     * 活动列表
     */
    public function more()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('api_number','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $RegionObj = new Region();
        $CommonObj = new Common();
        $ActivityObj = new Activity();
        $ShopObj=new Shop();

        /*城市编号*/
        $api_number = (int)Buddha_Http_Input::getParameter('api_number');
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }
        $locdata=$RegionObj->getApiLocationByNumberArr($api_number);


        $api_keyword = Buddha_Http_Input::getParameter('api_keyword');/*关键字*/
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $where ='';

        /*活动分类  (0全部（默认）；活动类型：1单个个人商家发布，2多商家联合发布，3投票,4点赞)<*/
        $api_activitytype =(int) Buddha_Http_Input::getParameter('api_activitytype')?Buddha_Http_Input::getParameter('api_activitytype'):0;

        /*当为投票时 必选 投票合作对象类型ID：1商家、2个人、3产品；)<*/
        $api_vodetype =(int) Buddha_Http_Input::getParameter('api_vodetype')?Buddha_Http_Input::getParameter('api_vodetype'):0;

        $api_isnearby = Buddha_Http_Input::getParameter('api_isnearby');
        /* 按照附近显示的距离默认为1km(如果api_isnearby==1时)*/
        $api_nearbydistance = Buddha_Http_Input::getParameter('api_nearbydistance')?Buddha_Http_Input::getParameter('api_nearbydistance'):1;


        $shop_id = Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;

        $lats=(int)Buddha_Http_Input::getParameter('lats')?Buddha_Http_Input::getParameter('lats'):0;
        $lngs=(int)Buddha_Http_Input::getParameter('lngs')?Buddha_Http_Input::getParameter('lngs'):0;


        $fields = 'id AS activity_id, shop_id, name AS activity_name, address, brief, type,vode_type,start_date,end_date';
        if($b_display==1)
        {
            $fields.=' , activity_img AS img ';
        }elseif($b_display==2){
            $fields.=' , activity_thumb AS img ';
        }


        $time=time();
        $where .= " isdel=0 AND is_sure=1 AND state=0 AND buddhastatus=0 AND {$time}<=`end_date` {$locdata['sql']} ";

        if($shop_id>0){
            $where .= " AND shop_id='{$shop_id}'";
        }


        /*屏蔽原因： 没有经纬度无法显示附近的*/
        //   $where.=$RegionObj->whereJoinNearby($api_nearbydistance,$lats,$lngs,$api_number);

        /*活动分类  (0全部（默认）；活动类型：1单个个人商家发布，2多商家联合发布，3投票,4点赞)<*/
            switch ($api_activitytype){
                case 1;
                    $where .= " AND type=1 ";
                    break;
                case 2;
                    $where .= " AND type=2 ";
                    break;
                case 3;
                    $where .= " AND type=3";
                    if($api_vodetype){
                        switch ($api_activitytype){
                            case 1;
                                $where .= " AND vode_type=1 ";
                                break;
                            case 2;
                                $where .= " AND vode_type=2 ";
                                break;
                            case 3;
                                $where .= " AND vode_type=3 ";
                                break;
                        }
                    }
                    break;
                case 4;
                    $where.=" AND type=4 ";
                    if($api_vodetype){
                        switch ($api_activitytype){
                            case 1;
                                $where .= " AND vode_type=1 ";
                                break;
                            case 2;
                                $where .= " AND vode_type=2 ";
                                break;
                            case 3;
                                $where .= " AND vode_type=3 ";
                                break;
                        }
                    }
                    break;
                case 5;
                    $where_hot=" AND is_hot=1 ";

            }


        if ($api_keyword) {
            $where .= " AND (name like '%$api_keyword%' OR number LIKE '%$api_keyword%')  ";
        }

        $orderby = " ORDER BY toptime,add_time DESC ";
        $sql =" SELECT  {$fields} FROM {$this->prefix}activity  WHERE {$where}{$where_hot} {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize);
        $Db_Activity = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if($api_activitytype==5)
        {
            $orderby = " ORDER BY click_count DESC ";

            $sql =" SELECT  {$fields} FROM {$this->prefix}activity  WHERE {$where} {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize);
            $Db_Activity = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }

        $jsondata = array();
        $jsondata['list'] = array();
        $jsondata['page'] = 0;
        $jsondata['pagesize'] = 0;
        $jsondata['totalrecord'] = 0;
        $jsondata['totalpage'] = 0;
        $jsondata['cat'] = array(
            'Services'=>'multilist.leaseclassmore',
            'param'=>array(),
        );
        if(Buddha_Atom_Array::isValidArray($Db_Activity)){
            foreach ($Db_Activity as $k => $v) {
                $temp_shop_id = (int)$v['shop_id'];
                if ($temp_shop_id>0) {

                    $Db_shop = $ShopObj->getSingleFiledValues(array('name', 'specticloc', 'lng', 'lat'), "id='{$temp_shop_id}'");

                    $Db_Activity[$k]['shop_name'] = $Db_shop['name'];

                    if (!$v['address']) {

                        $Db_Activity[$k]['address'] = $v['address'];

                    }
                    if(Buddha_Atom_String::isValidString($v['img'])){

                        $Db_Activity[$k]['img']=$host.$v['img'];

                    }else{
                        $Db_Activity[$k]['img'] = '';
                    }

                    /*是否过期：过期就要下架*/
                    $newtime = time();
                    if(!($v['start_date']<$newtime AND $newtime<$v['end_date'])){
                        $data['buddhastatus']=1;
                        $ActivityObj->edit($data,$v['activity_id']);
                    }

                    unset($v['start_date']);
                    unset($v['end_date']);

                    $Db_Activity[$k]['api_typename'] = $ActivityObj->getActivitytypenameByActivitytypeid($v['type']);
                    $Db_Activity[$k]['api_vodetypename']=$ActivityObj->getActivityvodetypenameByActivityvodetypeid($v['vode_type']);

                    $Db_Activity[$k]['icon_shop'] = $host. "style/images/shopgray.png";

                    $Db_Activity[$k]['services'] = 'activity.view';
                    $Db_Activity[$k]['param'] = array('activity_id'=>$v['activity_id']);
                }
            }

            $jsondata['list'] = $Db_Activity;
            $tablewhere=$this->prefix.'activity';
            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];

        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动列表');

    }
    /**
     * 首页活动：活动详情
     */
    public function view()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('activity_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $ShopObj =new Shop();
        $CommonObj =new Common();
        $ActivityObj =new Activity();
        $CustomObj =new Custom();
        $MoregalleryObj =new Moregallery();
        $where='';

        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $activity_id =(int) Buddha_Http_Input::getParameter('activity_id');

        $fields = 'id AS activity_id, shop_id, name AS activity_name, address, brief, prize,
                  type as typeid,vode_type as api_vodetypeid,start_date,end_date,sign_start_time
                  ,sign_end_time,add_time,click_count, `desc` ';

        if($b_display==1)
        {
            $fields.=' , activity_large AS activity_img ';
        }elseif($b_display==2)
        {
            $fields.=' , activity_img AS activity_img ';
        }
        $where = " id='{$activity_id}' ";

        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $sql = " SELECT {$fields} FROM {$this->prefix}activity  WHERE {$where} ";
        $Db_Activity_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();

        if(Buddha_Atom_Array::isValidArray($Db_Activity_arr))
        {
            $Db_Activity = $Db_Activity_arr[0];

            /**当活动类型为单家(typeid=1)或多家(typeid=2)时,才有报名和多选*/
            if($Db_Activity['typeid']==1 OR $Db_Activity['typeid']==2)
            {
                /*获取活动状态*/

                $state = $ActivityObj->getActivityState($Db_Activity);

                /*活动状态标题 0活动已经结束; 1距离开始; 2 距离结束距离结束*/
                $Db_Activity['api_activitystatetitle'] = $state['activitystatetitle'];

                /*报名状态：0 可以报名  1活动已结束不可以报名 3 报名未开始（不可报名）; 4活动已结束（不可报名）( 活动开始了就不能报名了；0可以报名，1 不可以报名)*/
                $Db_Activity['api_signstate'] = $state['signstate'];
                /***↓↓↓↓↓↓↓↓↓↓↓ 是否显示报名按钮 ↓↓↓↓↓↓↓↓↓↓↓**/
                if($state['signstate']==0)
                {
                    $Db_Activity['api_isshowregistrationbutton'] = 1;
                }else{
                    $Db_Activity['api_isshowregistrationbutton'] = 0;
                }
                /**↑↑↑↑↑↑↑↑↑↑ 是否显示报名按钮 ↑↑↑↑↑↑↑↑↑↑**/


                /***↓↓↓↓↓↓↓↓↓↓↓ 活动状态标题 0活动已经结束; 1 距离开始; 2 距离结束距离结束 ↓↓↓↓↓↓↓↓↓↓↓*****/
                $Db_Activity['api_activitystatetitle'] = $state['activitystatetitle'];
                $Db_Activity['api_startdate']=$CommonObj->getDateStrOfTime($Db_Activity['start_date'],0,1);
                $Db_Activity['api_enddate']=$CommonObj->getDateStrOfTime($Db_Activity['end_date'],0,1);

                $signtime = $ActivityObj->getRegistrationtime($Db_Activity);

                $Db_Activity['api_signstarttime']=$signtime['api_signstarttime'];

                $Db_Activity['api_signendtime']=$signtime['api_signendtime'];

                $Db_Activity['api_add_time']=$CommonObj->getDateStrOfTime($Db_Activity['add_time'],0,1,1);
                /*判断自定义表中有没有该自定义表*/
                $Db_Custom_num = $CustomObj->countRecords(" t_name='{$this->tablename}' AND t_id='{$activity_id}'");


                /*这里为了兼容:把1.0的自定义表单数组拆分到自定义数据表中*/
               if(Buddha_Atom_String::isValidString($Db_Activity['form_desc']) AND $Db_Custom_num==0)
               {

                   $Db_Activity['form_desc'] = unserialize($Db_Activity['form_desc']);

                   /*这里为了兼容:把1.0的自定义表单数组拆分到自定义数据表中(这里为了防止恶意添加，这里只更新2017-10-21 00:00:00之前的;只够的就不在更新)*/
                   ;
                    $newtime=time();
                    if(1508515200>$newtime)
                    {

                        $temp_data['t_id']=$activity_id;
                        $temp_data['t_name']=$this->tablename;
                        $temp_data['add_time']=$newtime;

                        $form_desc = unserialize($Db_Activity['form_desc']);
                        $formdesc = $form_desc['desc'];
                        /*单选*/
                        if(Buddha_Atom_Array::isValidArray($formdesc['radioname'])){
                            foreach ($formdesc['radioname'] as $k=>$v)
                            {
                                /*标题更新*/
                                $temp_data['arrkey']=array_keys($formdesc['radioname'][0]);
                                $temp_data['sub']=$v['sub0'];
                                $temp_data['c_type']=$v['sub1'];
                                $temp_data['sort']=$v['sub2'];
                                $temp_data['c_title']=$v['val'];
                                $CustomObj->add($temp_data);
                                /*标题内容更新*/
                                foreach ($v['son'] as $kk=>$vv)
                                {
                                    $temp_data['arrkey']=array_keys($v['son'][0]);
                                    $temp_data['sub']=$vv['sub0'];
                                    $temp_data['c_type']=$vv['sub1'];
                                    $temp_data['sort']=$vv['sub2'];
                                    $temp_data['c_title']=$vv['val'];
                                    $temp_data['sub_1']=$vv['sub3'];
                                    $CustomObj->add($temp_data);
                                }
                            }
                        }
                        /*多选*/
                        if(Buddha_Atom_Array::isValidArray($formdesc['checkname']))
                        {
                            foreach ($formdesc['checkname'] as $k=>$v)
                            {
                                /*标题更新*/
                                $temp_data['arrkey']=array_keys($formdesc['checkname'][0]);
                                $temp_data['sub']=$v['sub0'];
                                $temp_data['c_type']=$v['sub1'];
                                $temp_data['sort']=$v['sub2'];
                                $temp_data['c_title']=$v['val'];
                                $CustomObj->add($temp_data);
                                /*标题内容更新*/
                                foreach ($v['son'] as $kk=>$vv) {
                                    $temp_data['arrkey']=array_keys($v['son'][0]);
                                    $temp_data['sub']=$vv['sub0'];
                                    $temp_data['c_type']=$vv['sub1'];
                                    $temp_data['sort']=$vv['sub2'];
                                    $temp_data['c_title']=$vv['val'];
                                    $temp_data['sub_1']=$vv['sub3'];
                                    $CustomObj->add($temp_data);
                                }
                            }
                        }

                        /*多行*/
                        if(Buddha_Atom_Array::isValidArray($formdesc['text']))
                        {
                            foreach ($formdesc['text'] as $k=>$v)
                            {
                                /*标题更新*/
                                $temp_data['arrkey']=array_keys($formdesc['text'][0]);
                                $temp_data['sub']=$v['sub0'];
                                $temp_data['c_type']=$v['sub1'];
                                $temp_data['sort']=$v['sub2'];
                                $temp_data['c_title']=$v['val'];
                                $CustomObj->add($temp_data);
                            }
                        }

                        /*单行*/
                        if(Buddha_Atom_Array::isValidArray($formdesc['txt']))
                        {
                            foreach ($formdesc['txt'] as $k=>$v)
                            {
                                /*标题更新*/
                                $temp_data['arrkey']=array_keys($formdesc['txt'][0]);
                                $temp_data['sub']=$v['sub0'];
                                $temp_data['c_type']=$v['sub1'];
                                $temp_data['sort']=$v['sub2'];
                                $temp_data['c_title']=$v['val'];
                                $CustomObj->add($temp_data);
                            }
                        }
                    }
                    unset($Db_Activity['form_desc']);
                    $up_data['form_desc']='';
                    $ActivityObj->edit($up_data,$activity_id);
                }

                /*查询该活动的自定义表单*/
               $Customfiledarr=array('id as custom_id','t_id as table_id','t_name as table_name','arrkey','add_time','click_num','sub','c_type','sort','sub_1','c_title');
               $Db_Custom = $CustomObj->getFiledValues($Customfiledarr," t_name='{$this->tablename}' AND t_id='{$activity_id}' ORDER BY sort ASC");


////、、、、、、、、、、、、、、、、、、、、、、
//                if($activity['type']==2){//合作商家(多商家)
//                    $ActivitycooperationObj = new Activitycooperation();
//                    $coopwhere="act_id={$id} and is_sure=1 and sure=1";
//                    $acoonum = $ActivitycooperationObj->countRecords($coopwhere);
//                    $ShopObj = new Shop();
//                    if ($acoonum) {
//                        $acoo = $ActivitycooperationObj->getFiledValues('',$coopwhere);
//                        foreach ($acoo as $k => $v) {//查询店铺名称和logo
//                            $shop = $ShopObj->getSingleFiledValues(array('name', 'small'), "id={$v['shop_id']} and is_sure=1 and state=0");
//                            $acoo[$k]['shop_name'] = mb_substr($shop['name'],0,6) ;
//                            $acoo[$k]['shop_logo'] = $shop['small'];
//                        }
//                        $aco['aco'] = $acoo;
//                        $aco['surl'] = $ShopObj->shop_url();
//                    } else {
//                        $aco = '';
//                    }
//                    $this->smarty->assign('aco', $aco);
//                }
//
////、、、、、、、、、、、、、、、、、、、、
            }
            $Db_Activity['custom'] = array();

            if(Buddha_Atom_Array::isValidArray($Db_Custom))
            {
                foreach ($Db_Custom as $k=>$v)
                {
                    $Db_Custom[$k]['api_addtime']=$CommonObj->getDateStrOfTime($v['add_time'],0,1);
                    $Db_Custom[$k]['CustomServices']=array(
                        'Services'=>'activity.userregistration',
                        'param'=> array('custom_id' =>$v['custom_id']),
                    );
                }
                $Db_Activity['custom'] = $Db_Custom;
            }

            if(Buddha_Atom_String::isValidString($Db_Activity['activity_img']))
            {
                $Db_Activity['activity_img'] = $host.$Db_Activity['activity_img'];
            }else{
                $Db_Activity['activity_img'] = '';
            }

            if(!Buddha_Atom_Array::isValidArray($Db_Activity['address']))
            {
                $Db_Activity['address'] = '';
            }

            $shopFiled = array('name');

            if($b_display==2)
            {
                array_push($shopFiled,'small as img');
            }elseif($b_display==1){
                array_push($shopFiled,'medium as img');
            }


            $Db_Shop = $ShopObj->getSingleFiledValues($shopFiled," id='{$Db_Activity['shop_id']}' ");

            $Db_Activity['shop_name'] = $Db_Shop['name'];

            if(Buddha_Atom_String::isValidString($Db_Shop['img']))
            {
                $Db_Activity['shop_img'] = $host.$Db_Shop['img'];
            }else{
                $Db_Activity['shop_img'] = '';
            }

            $Db_Activity['mobile']=$ShopObj->isShowPhpone($Db_Activity['shop_id'],$usertoken);
            $Db_Activity['api_startdate']=$CommonObj->getDateStrOfTime($Db_Activity['start_date'],0,1);
            $Db_Activity['api_enddate']=$CommonObj->getDateStrOfTime($Db_Activity['end_date'],0,1);

            /**↓↓↓↓↓↓ 报名开始时间 和 结束时间 ↓↓↓↓↓↓**/
            $signtime = $ActivityObj->getRegistrationtime($Db_Activity);
            $Db_Activity['api_signstarttime']=$signtime['api_signstarttime'];
            $Db_Activity['api_signendtime']=$signtime['api_signendtime'];
            /***↑↑↑↑↑↑↑↑↑↑ 报名开始时间 和 结束时间 ↑↑↑↑↑↑↑↑↑↑***/

            $Db_Activity['api_add_time']=$CommonObj->getDateStrOfTime($Db_Activity['add_time'],0,1,1);
            $Db_Activity['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['desc']);
            $Db_Activity['api_type'] = $ActivityObj->getactivitytypenameforactivitytyid($Db_Activity['typeid']);


            $jsondata = $Db_Activity;

            $jsondata['img'] = array();
            /***↓↓↓↓↓↓ 投票才有轮播图 ↓↓↓↓↓↓**/
            if($Db_Activity['typeid']==3)
            {
                /**↓↓↓↓↓↓ 冠名商家查询 ↓↓↓↓↓↓***/
                $Moregalleryfiled=array('id AS moregallery_id','shop_id');
                if($b_display==1){
                    array_push($Moregalleryfiled,'goods_large AS img');
                }elseif($b_display==2){
                    array_push($Moregalleryfiled,'goods_img AS img');
                }
                $Db_Moregallery= $MoregalleryObj->getFiledValues($Moregalleryfiled,"goods_id={$activity_id} and tablename='{$this->tablename}' and webfield='file_title'");
                /***↑↑↑↑↑↑↑↑↑↑冠名商家查询 ↑↑↑↑↑↑↑↑↑↑***/

                /***↓↓↓↓↓↓ 头部轮播图组装 ↓↓↓↓↓↓***/
                $carousel = array();
                if(Buddha_Atom_Array::isValidArray($Db_Moregallery)){
                    $carousel[0]['img'] = $Db_Activity['img'];
                    unset($Db_Activity['img']);
                    $carousel[0]['shop_id'] = 0;

                    foreach($Db_Moregallery as $k=>$v)
                    {
                        array_push($carousel,$v);
                    }

                    foreach($carousel as $k=>$v)
                    {
                        $carousel[$k]['img'] = $host.$v['img'];
                        $carousel[$k]['Services'] = 'shop.view';
                        $carousel[$k]['param'] = array('shop_id'=>$v['shop_id']);
                    }
                }
                /**↑↑↑↑↑↑↑↑↑↑头部轮播图组装 ↑↑↑↑↑↑↑↑↑↑****/

                $jsondata['img'] = $carousel;

            }

            $jsondata['isshowcellphone'] = array(
                'services' =>'shop.isshowcellphone',
                'param' => array('shop_id'=>$Db_Activity['shop_id']),
            );

            /**↓↓↓↓↓↓↓↓↓↓↓ 是否显示电话号码 ↓↓↓↓↓↓↓↓↓↓↓**/
            $jsondata['isshowcellphone'] = array(
                'services' =>'shop.isshowcellphone',
                'param' => array('shop_id'=>$Db_Activity['shop_id']),
            );
            /**↑↑↑↑↑↑↑↑↑↑ 是否显示电话号码 ↑↑↑↑↑↑↑↑↑↑**/


            /***↓↓↓↓↓↓ 是否过期：过期就要下架 ↓↓↓↓↓↓***/

            $newtime = Buddha::$buddha_array['buddha_timestamp'];
            if(!($Db_Activity['start_date']<$newtime AND $newtime<$Db_Activity['end_date']))
            {
                $data['buddhastatus']=0;
                $ActivityObj->edit($data,$Db_Activity['demand_id']);
            }
            /***↑↑↑↑↑↑↑↑↑↑ 是否过期：过期就要下架 ↑↑↑↑↑↑↑↑↑↑***/

            /***↓↓↓↓↓↓ 更新点击量 ↓↓↓↓↓↓***/
            $clickdata['click_count']=$Db_Activity['click_count']+1;
            $ActivityObj->edit($clickdata,$Db_Activity['activity_id']);
            /***↑↑↑↑↑↑↑↑↑↑更新点击量 ↑↑↑↑↑↑↑↑↑↑***/


            /**↓↓↓↓↓↓↓↓↓↓↓ 分享 ↓↓↓↓↓↓↓↓↓↓↓**/
            if($Db_Activity['brief'])
            {
                $brief = strip_tags($Db_Activity['brief']);
                if(mb_strlen($brief) > 20)
                {
                    $share_desc = mb_substr($brief,0,20) . '...';
                }else{
                    $share_desc= $brief;
                }
            }else{
                $share_desc = "快速发布您的{$this->tablenamestr}，快速解决您的问题，万人同时在线，为您排忧解难";
            }

            if(Buddha_Atom_String::isValidString($Db_Activity['activity_img']))
            {
                $share_imgUrl = $Db_Activity['activity_img'];
            }else{
                $share_imgUrl = '';
            }

            $detail='';

            if($Db_Activity['typeid']==1 or $Db_Activity['typeid']==2)
            {
                $detail = 'mylistdetail';
            }elseif($Db_Activity['typeid']==3 or $Db_Activity['typeid']==4)
            {
                $detail = 'vodelistdetail';
            }

            $sharearr = array(
                'share_title'=>$Db_Activity['activity_name'],
                'share_desc'=>$share_desc,
                'share_link'=> Buddha_Atom_Share::getShareUrl('activity.'.$detail,$activity_id),
                'share_imgUrl'=> $share_imgUrl,
            );
            $jsondata['sharearr'] = $sharearr;
            /**↑↑↑↑↑↑↑↑↑↑ 分享 ↑↑↑↑↑↑↑↑↑↑**/

            $shopinfo = $ShopObj->getSingleFiledValues(array('id as shop_id','user_id','small','name','is_verify','specticloc','lat','lng','mobile','createtimestr','level2','level3','is_verify','user_id'),"id='{$Db_Activity['shop_id']}' and isdel=0");

            /**↓↓↓↓↓↓↓↓↓↓↓ 店铺是否有转发有赏 ↓↓↓↓↓↓↓↓↓↓↓**/
            $rechargeObj = new Recharge();//充值表
            $rechargeinfo = $rechargeObj->getSingleFiledValues('',"uid={$shopinfo['user_id']} and shop_id='{$shopinfo['shop_id']} and is_open=1'");
            unset($shopinfo['user_id']);
            $is_reward = 0;//是否转发有赏：0否；1是
            $is_reward_img = '';
            $is_reward_url = array();
            if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money']){
                $is_reward = 1;
                $is_reward_img = $host.'style/images/zhuanfayoushang.png';
                $is_reward_url = array(
                    'services' =>'shop.sharingmoney',
                    'param' => array('shop_id'=>$Db_Activity['shop_id']),
                );
            }
            $issharearr = array(
                'is_reward'=>$is_reward,        //  是否转发有赏：0否；1是
                'is_reward_img'=>$is_reward_img,//  转发的图标
                'is_reward_url'=>$is_reward_url,//  转发的后访问发的有赏接口
            );
            $jsondata['issharearr'] = $issharearr;
            /**↑↑↑↑↑↑↑↑↑↑ 店铺是否有转发有赏 ↑↑↑↑↑↑↑↑↑↑**/

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动详情');
    }

    /**
     * 个人中心：商家 活动详情
     */

    public function merchantsmanagementview()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','activity_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $CommonObj = new Common();
        $UserObj = new User();
        $ActivityObj = new Activity();
        $ShopObj = new Shop();
        $CustomObj = new Custom();

        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $activity_id =(int) Buddha_Http_Input::getParameter('activity_id');

        /**判断该 $tableid 通过 $tablename 、 $tableid 和 $uid 是否属于该用户*/
        if (!$CommonObj->isToUserByTablenameAndTableid($this->tablename,$activity_id,$user_id)) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误');
        }


        $fields = 'id AS activity_id, shop_id, name AS activity_name, address, brief, 
                    type as typeid,vode_type as api_vodetypeid,start_date,end_date,sign_start_time,sign_end_time,add_time,click_count, `desc` ';

        if($b_display==1){
            $fields.=' , activity_large AS img ';
        }elseif($b_display==2){
            $fields.=' , activity_img AS img ';
        }
        $where= " id='{$activity_id}' ";

        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $sql =" SELECT {$fields} FROM {$this->prefix}activity  WHERE {$where} ";
        $Db_Activity_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_Activity_arr)){
            $Db_Activity= $Db_Activity_arr[0];
            if(Buddha_Atom_Array::isValidArray($Db_Activity)){
                $Db_Activity['img']=$host.$Db_Activity['img'];
            }else{
                $Db_Activity['img'] = '';
            }

            $Db_Activity['mobile']=$ShopObj->isShowPhpone($Db_Activity['shop_id'],$usertoken);
            $Db_Activity['api_startdate']=$CommonObj->getDateStrOfTime($Db_Activity['start_date'],0,1);
            $Db_Activity['api_enddate']=$CommonObj->getDateStrOfTime($Db_Activity['end_date'],0,1);

            $signtime=$ActivityObj->getRegistrationtime($Db_Activity);

            $Db_Activity['api_signstarttime']=$signtime['api_signstarttime'];

            $Db_Activity['api_signendtime']=$signtime['api_signendtime'];

            $Db_Activity['api_add_time']=$CommonObj->getDateStrOfTime($Db_Activity['add_time'],0,1,1);

            $Db_Activity['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['desc']);

            $Db_Activity['api_type']=$ActivityObj->getactivitytypenameforactivitytyid((int)$Db_Activity['typeid']);

            $jsondata['list'] = $Db_Activity;

            /*查询该活动的自定义表单*/
            $Customfiledarr=array('id as custom_id','t_id as table_id','t_name as table_name','arrkey','add_time','click_num','sub','c_type','sort','sub_1','c_title');
            $Db_Custom = $CustomObj->getFiledValues($Customfiledarr," t_name='{$this->tablename}' AND t_id='{$activity_id}' ORDER BY sort ASC");
            /*自定义表单*/
            $Db_Activity['custom'] = array();
            if(Buddha_Atom_Array::isValidArray($Db_Custom)){

                foreach ($Db_Custom as $k=>$v){
                    $Db_Custom[$k]['api_addtime']=$CommonObj->getDateStrOfTime($v['add_time'],0,1);
                }
                $jsondata['custom'] = $Db_Custom;
            }

            /*报名列表*/
            $jsondata['signlist']=array(
                'Services' => 'activity.merchantssignuplist',
                'param'=> array('tablename'=>$this->tablename,'activity_id'=>$activity_id)
            );

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动详情');
    }


    /**
     *  个人中心：活动管理列表
     */
    public function managementmore ()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display', 'usertoken','groupid'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj = new Common();
        $UserObj = new User();
        $ActivityObj = new Activity();
        $ShopObj = new Shop();
        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $groupid = Buddha_Http_Input::getParameter('groupid') ? Buddha_Http_Input::getParameter('groupid') : 0;

        $typeid = Buddha_Http_Input::getParameter('typeid') ? Buddha_Http_Input::getParameter('typeid') : 0;


        if($groupid==1 || $typeid==1){
            if(!$UserObj->isHasMerchantPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
            }
        }elseif($groupid==2 || $typeid==2){
            if(!$UserObj->isHasAgentPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商会员角色！');
            }
        }elseif($groupid==3 || $typeid==3){

            if(!$UserObj->isHasPartnerPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000023, '你还未申请合伙人会员角色！');
            }
        }elseif($groupid==4 || $typeid==4){
            if(!$UserObj->isHasUserPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000021, '你还未申请普通会员角色！');
            }
        }else{
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }


        $api_keyword = Buddha_Http_Input::getParameter('api_keyword') ? Buddha_Http_Input::getParameter('api_keyword') : '';


        $b_display = (int)Buddha_Http_Input::getParameter('b_display') ? Buddha_Http_Input::getParameter('b_display') : 2;

        $page = Buddha_Http_Input::getParameter('page') ? Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $view = Buddha_Http_Input::getParameter('view') ? Buddha_Http_Input::getParameter('view') : 0;

        $shop_id = Buddha_Http_Input::getParameter('shop_id') ? Buddha_Http_Input::getParameter('shop_id') : 0;


        if($groupid==1 || $typeid==1){

            /*商家：商家只能查看自己的活动信息*/

            $where = " isdel=0 and user_id='{$user_id}'";

        }elseif($groupid==2 || $typeid==2){

            /*代理商：只能查看自己区域内的活动信息*/
            $where = "  level3='{$Db_User['level3']}' ";

        }elseif($groupid==3 || $typeid==3){

            /*合伙人：合伙人角色不具备该功能*/
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000024, '合伙人角色不允许查看！');

        }elseif($groupid==4 || $typeid==4){

            /*普通会员：普通会员角色不具备该功能*/
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000025, '普通会员角色不具备该功能！');

        }


        if (Buddha_Atom_String::isValidString($api_keyword)) {

            $where .= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword, array('name', 'number'));

        }

        if($shop_id>0){
            $where .=" AND shop_id='{$shop_id}'";
        }

        if ($view) {
            switch ($view) {
                case 2;
                    $where .= ' AND is_sure=0 ';
                    break;
                case 3;
                    $where .= ' AND is_sure=1 ';
                    break;
                case 4;
                    $where .= ' AND is_sure=4 ';
                    break;
            }
        }


        $isShowStop=0;
        if($groupid==2 || $typeid==2){

            /*只有代理商可以查看已停用功能*/
            if($view==5){
                $where.=" and isdel=0 and is_sure=1 and buddhastatus=1";
            }

            /*只有代理商可以查看拥有已停用导航按钮*/
            $isShowStop=1;
        }


        $fileds = ' id AS activity_id, name, buddhastatus,is_sure, number, brief ,shop_id ,state,type as api_typeid, vode_type as api_vodetypeid ';

        if ($b_display == 1) {

            $fileds .= ' , activity_img AS img ';
        } elseif ($b_display == 2) {

            $fileds .= ' , activity_thumb AS  img ';
        }

        $orderby = " ORDER BY add_time DESC ";


        $sql = " SELECT  {$fileds}  
                 FROM {$this->prefix}activity WHERE {$where} 
                 {$orderby}  " . Buddha_Tool_Page::sqlLimit($page, $pagesize);


        $Db_Activity = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $jsondata = array();

        if (Buddha_Atom_Array::isValidArray($Db_Activity)) {

            $jsondata['nav'] = $ShopObj->getPersonalCenterHeaderMenu('activity.managementmore',$isShowStop);
            $jsondata['ser'] = array(
                'Services'=>'activity.managementview',
                'param'=>array(
                    'groupid'=>$groupid,
                    'typeid'=>$typeid,
                ),
            );


            foreach ($Db_Activity as $k => $v) {
                if($groupid==2 || $typeid==2){

                    if($v['shop_id']!=0){
                        $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                        $Db_Activity[$k]['shop_name']  = '商家：'. $shop_name['name'];
                    }else{
                        $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                        $Db_Activity[$k]['shop_name']  = '个人：'. $shop_name['name'];
                    }

                }else{
                    if ($v['shop_id'] != 0) {
                        $shop_name = $ShopObj->getSingleFiledValues(array('name'), "id='{$v['shop_id']}' AND user_id='{$v['user_id']}'");
                        $Db_Activity[$k]['shop_name']  =  $shop_name['name'];
                    } else {
                        $shop_name = $UserObj->getSingleFiledValues(array('name'), "id='{$v['user_id']}'");
                        $Db_Activity[$k]['shop_name']   =  $shop_name['name'];
                    }
                }



                if ($v['is_sure'] == 0) {

                    $Db_Activity[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/weishenhe.png';

                } elseif ($v['is_sure'] == 4) {

                    $Db_Activity[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/weitonguo.png';

                } elseif ($v['is_sure'] == 1) {

                    $Db_Activity[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/yitonguo.png';

                }


                if (Buddha_Atom_String::isValidString($v['img'])) {

                    $Db_Activity[$k]['api_img'] = $host . $v['img'];

                } else {

                    $Db_Activity[$k]['api_img'] = '';
                }

                if ($v['buddhastatus'] == 1) {

                    $Db_Activity[$k]['api_buddhastatus'] = '上 架';

                } else if ($v['buddhastatus'] == 0) {

                    $Db_Activity[$k]['api_buddhastatus'] = '下 架';

                }

                if ($v['state'] == 1) {

                    $Db_Activity[$k]['api_buddhastatus'] = '停 用';

                } else if ($v['state'] == 0) {

                    $Db_Activity[$k]['api_buddhastatus'] = '启 用';

                }


                if (!Buddha_Atom_String::isValidString($v['shop_name'])) {

                    $Db_Activity[$k]['shop_name'] = $ShopObj->getShopnameFromShopid($v['shop_id']);

                }


                $Db_Activity[$k]['api_typename'] = $ActivityObj->getactivitytypenameforactivitytyid($v['api_typeid']);


                if($v['api_typeid']==3){
                    $Db_Activity[$k]['api_vodetypename'] = $ActivityObj->getActivityvodetypenameByActivityvodetypeid($v['api_vodetypeid']);
                    unset($v['api_vodetypeid']);
                }
                unset($Db_Activity[$k]['img']);
                unset($Db_Activity[$k]['level3']);
            }


            $tablewhere = $this->prefix . 'activity';

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);


            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];
            $jsondata['list'] = $Db_Activity;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动管理列表');


    }



    /**
     *  个人中心： 商家 活动管理列表
     */
    public function merchantsmanagementmore ()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display', 'usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj = new Common();
        $UserObj = new User();
        $ActivityObj = new Activity();
        $ShopObj = new Shop();
        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $groupid = Buddha_Http_Input::getParameter('groupid') ? Buddha_Http_Input::getParameter('groupid') : 0;

        $typeid = Buddha_Http_Input::getParameter('typeid') ? Buddha_Http_Input::getParameter('typeid') : 0;


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        $api_keyword = Buddha_Http_Input::getParameter('api_keyword') ? Buddha_Http_Input::getParameter('api_keyword') : '';


        $b_display = (int)Buddha_Http_Input::getParameter('b_display') ? Buddha_Http_Input::getParameter('b_display') : 2;

        $page = Buddha_Http_Input::getParameter('page') ? Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $view = Buddha_Http_Input::getParameter('view') ? Buddha_Http_Input::getParameter('view') : 0;
        $view2 = Buddha_Http_Input::getParameter('view2') ? Buddha_Http_Input::getParameter('view2') : 0;

        $shop_id = Buddha_Http_Input::getParameter('shop_id') ? Buddha_Http_Input::getParameter('shop_id') : 0;


        /*商家：商家只能查看自己的活动信息和没有被删除的活动*/

        $where = " isdel=0 and user_id='{$user_id}' ";


        if (Buddha_Atom_String::isValidString($api_keyword)) {

            $where .= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword, array('name', 'number'));

        }

        if($shop_id>0){
            $where .=" AND shop_id='{$shop_id}'";
        }

        if(Buddha_Atom_String::isValidString($view2)){
            if(!$CommonObj->isIdInDataEffectiveById($view2,array(5,6,7,8))){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
            }
        }


        if(!$CommonObj->isIdInDataEffectiveById($view,array(0,2,3,4,5,6,7,8))){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        if($view2){
            switch($view){
                case 5;//单商家
                    $where.=' and type=1 ';
                    break;
                case 6;//多商家
                    $where.=' and type=2 ';
                    break;
                case 7;//我申请的
                    $where.=' and user_id='.$user_id;
                    break;
                case 8;//投票
                    $where.=' and type=3 ';
                    break;
                case 9;//点赞
                    $where.=' and type=4 ';
                    break;
            }
        }


        if ($view) {
            switch($view){
                case 2;
                    $where.=' and is_sure=0 ';
                case 3;
                    $where.=' and is_sure=1 ';
                    break;
                case 4;
                    $where.=' and is_sure=4 ';
                    break;
                case 5;//单商家
                    $where.=' and type=1 ';
                    break;
                case 6;//多商家
                    $where.=' and type=2 ';
                    break;
                case 7;//我申请的
                    $where.=' and user_id='.$user_id;
                    break;
                case 8;//投票
                    $where.=' and type=3 ';
                    break;
                case 9;//点赞
                    $where.=' and type=4 ';
                    break;
            }
        }

        $isShowStop=0;

        $fileds = ' id AS activity_id, name, buddhastatus,is_sure, number, brief ,shop_id ,state,type as api_typeid, vode_type as api_vodetypeid ,start_date,end_date,start_date_str,end_date_str';

        if ($b_display == 1) {

            $fileds .= ' , activity_img AS img ';
        } elseif ($b_display == 2) {

            $fileds .= ' , activity_thumb AS  img ';
        }

        $orderby = " ORDER BY add_time DESC ";

        $sql = " SELECT  {$fileds}  
                 FROM {$this->prefix}activity WHERE {$where} 
                 {$orderby}  " . Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $Db_Activity = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

////////////////////////////////////////////////////////////////////////////////////////

        $jsondata = array();
        $jsondata['page'] = 0;
        $jsondata['pagesize'] = 0;
        $jsondata['totalrecord'] = 0;
        $jsondata['totalpage'] = 0;
        $jsondata['list'] = array();
////////////////////////////////////////////////////////////////////////////////////////
        $Services='activity.merchantsmanagementmore';
        $jsondata['nav1']=array(
            1=>array( 'select'=>0,'name'=>'新添加','pageflag'=>'','type'=>2,
                'Services'=>$Services,'param'=>array('view'=>2)),

            2=>array( 'select'=>0,'name'=>'已通过','pageflag'=>'','type'=>3,
                'Services'=>$Services,'param'=>array('view'=>3)),

            3=>array( 'select'=>0,'name'=>'未通过','pageflag'=>'','type'=>4,
                'Services'=>$Services,'param'=>array('view'=>4)),
        );

        $jsondata['nav2']=array(
            0=>array( 'select'=>1,'name'=>'全部','pageflag'=>'','type'=>0,
                'Services'=>$Services,'param'=>array('view'=>0)),

            1=>array( 'select'=>0,'name'=>'单商家','pageflag'=>'','type'=>5,
                'Services'=>$Services,'param'=>array('view'=>5)),

            2=>array( 'select'=>0,'name'=>'多商家','pageflag'=>'','type'=>6,
                'Services'=>$Services,'param'=>array('view'=>6)),

            3=>array( 'select'=>0,'name'=>'我申请的','pageflag'=>'','type'=>7,
                'Services'=>$Services,'param'=>array('view'=>7)),

            4=>array( 'select'=>0,'name'=>'投票','pageflag'=>'','type'=>8,
                'Services'=>$Services,'param'=>array('view'=>8)),
        );
        $jsondata['add'] = array(
            'Services'=>'activity.beforeadd',
            'param'=>array(),
        );

        if (Buddha_Atom_Array::isValidArray($Db_Activity)) {

            foreach ($Db_Activity as $k => $v) {
                if($groupid==2 || $typeid==2){

                    if($v['shop_id']!=0){
                        $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                        $Db_Activity[$k]['shop_name']  = '商家：'. $shop_name['name'];
                    }else{
                        $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                        $Db_Activity[$k]['shop_name']  = '个人：'. $shop_name['name'];
                    }

                }else{
                    if ($v['shop_id'] != 0) {
                        $shop_name = $ShopObj->getSingleFiledValues(array('name'), "id='{$v['shop_id']}' AND user_id='{$v['user_id']}'");
                        $Db_Activity[$k]['shop_name']  =  $shop_name['name'];
                    } else {
                        $shop_name = $UserObj->getSingleFiledValues(array('name'), "id='{$v['user_id']}'");
                        $Db_Activity[$k]['shop_name']   =  $shop_name['name'];
                    }
                }


                if ($v['is_sure'] == 0) {

                    $Db_Activity[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/weishenhe.png';

                } elseif ($v['is_sure'] == 4) {

                    $Db_Activity[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/weitonguo.png';

                } elseif ($v['is_sure'] == 1) {

                    $Db_Activity[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/yitonguo.png';

                }


                if (Buddha_Atom_String::isValidString($v['img'])) {

                    $Db_Activity[$k]['api_img'] = $host . $v['img'];

                } else {

                    $Db_Activity[$k]['api_img'] = '';
                }

                if ($v['buddhastatus'] == 1) {

                    $Db_Activity[$k]['api_buddhastatus'] = '上 架';

                } else if ($v['buddhastatus'] == 0) {

                    $Db_Activity[$k]['api_buddhastatus'] = '下 架';

                }

                if ($v['state'] == 1) {

                    $Db_Activity[$k]['api_buddhastatus'] = '停 用';

                } else if ($v['state'] == 0) {

                    $Db_Activity[$k]['api_buddhastatus'] = '启 用';

                }


                if (!Buddha_Atom_String::isValidString($v['shop_name'])) {

                    $shop_name= $ShopObj->getShopnameFromShopid($v['shop_id']);
                    $Db_Activity[$k]['shop_name'] =$shop_name;
                    $Activity_data['shop_name']=$shop_name;
                    $ActivityObj->edit($Activity_data,$v['activity_id']);

                }

                $Db_Activity[$k]['api_typename'] = $ActivityObj->getactivitytypenameforactivitytyid($v['api_typeid']);


                if(!Buddha_Atom_String::isValidString($v['start_date_str'])){
                    $Db_Activity[$k]['start_date_str']=$CommonObj->getDateStrOfTime($v['start_date'],1,0,0);
                }

                if(!Buddha_Atom_String::isValidString($v['end_date_str'])){
                    $Db_Activity[$k]['end_date_str']=$CommonObj->getDateStrOfTime($v['end_date'],1,0,0);
                }
                $Db_Activity[$k]['api_typename'] = $ActivityObj->getactivitytypenameforactivitytyid($v['api_typeid']);

                if($v['api_typeid']==3){
                    $Db_Activity[$k]['api_vodetypename'] = $ActivityObj->getActivityvodetypenameByActivityvodetypeid($v['api_vodetypeid']);
                    unset($v['api_vodetypeid']);
                }

                $Db_Activity[$k]['view'] = array(
                    'Services'=>'activity.merchantsmanagementview',
                    'param'=>array(
                        'activity_id'=>$v['activity_id'],
                    ),
                );

                $Db_Activity[$k]['update'] = array(
                    'Services'=>'activity.beforeupdate',
                    'param'=>array(
                        'activity_id'=>$v['activity_id'],
                    ),
                );

                $Db_Activity[$k]['del'] = array(
                    'Services'=>'activity.del',
                    'param'=>array(
                        'activity_id'=>$v['activity_id'],
                    ),
                );

                $Db_Activity[$k]['top'] = array(
                    'Services'=>'payment.infotop',
                    'param'=>array(
                        'activity_id'=>$v['activity_id'],
                        'good_table'=>'activity',
                    ),
                );

                unset($Db_Activity[$k]['img']);
                unset($Db_Activity[$k]['level3']);
            }


            $tablewhere = $this->prefix . 'activity';

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];
            $jsondata['list'] = $Db_Activity;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "个人中心： 商家 {$this->tablenamestr}管理列表");


    }


    /**
     *  个人中心：活动管理详情
     */
    public function managementview ()
    {

        header("Content-Type: text/html; charset=utf8 ");
        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken','activity_id','groupid'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $ShopObj= new Shop();
        $ActivityObj= new Activity();
        $CommonObj= new Common();
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        $groupid = Buddha_Http_Input::getParameter('groupid') ? Buddha_Http_Input::getParameter('groupid') : 0;

        $typeid = Buddha_Http_Input::getParameter('typeid') ? Buddha_Http_Input::getParameter('typeid') : 0;

        if($groupid==1 || $typeid==1){
            if(!$UserObj->isHasMerchantPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
            }
        }elseif($groupid==2 || $typeid==2){
            if(!$UserObj->isHasAgentPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商会员角色！');
            }
        }elseif($groupid==3 || $typeid==3){

            if(!$UserObj->isHasPartnerPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000023, '你还未申请合伙人会员角色！');
            }
        }elseif($groupid==4 || $typeid==4){
            if(!$UserObj->isHasUserPrivilege($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000021, '你还未申请普通会员角色！');
            }
        }else{
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }



        $activity_id = (int)Buddha_Http_Input::getParameter('activity_id')?Buddha_Http_Input::getParameter('activity_id'):0;

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;


        if($groupid==1 || $typeid==1){

            /*商家：商家只能查看自己的活动信息*/

            $where = " isdel=0 and user_id='{$user_id}'";

        }elseif($groupid==2 || $typeid==2){

            /*代理商：只能查看自己区域内的活动信息*/
            $where = "  level3='{$Db_User['level3']}' ";

        }elseif($groupid==3 || $typeid==3){

            /*合伙人：合伙人角色不具备该功能*/
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000024, '合伙人角色不允许查看！');

        }elseif($groupid==4 || $typeid==4){

            /*普通会员：普通会员角色不具备该功能*/
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000025, '普通会员角色不具备该功能！');

        }


        $Db_Activity_type=  $ActivityObj->getSingleFiledValues(array('type','vode_type'),$where);


        $fileds=' id AS activity_id, type, name, start_date, end_date
                  , sign_start_time, sign_end_time, shop_id, shop_name
                  , brief,`desc`, buddhastatus, is_remote, level1,level2,level3
                  , add_time,state, click_count ';
        if($Db_Activity_type['type']==1){
            $fileds.=' ,form_desc,address';
        }elseif($Db_Activity_type['type']==2){
            $fileds.=' ,form_desc';

        }elseif($Db_Activity_type['type']==3){
            $fileds.=' ,prize, vode_type AS api_vodetypeid ';
        }

        if($b_display==2){
            $fileds.=' ,activity_thumb AS img ';
        }elseif($b_display==1){
            $fileds.=' ,activity_img AS img';
        }

        $where.=" AND id='{$activity_id}'";
        $sql =" SELECT  {$fileds} 
                FROM {$this->prefix}activity WHERE {$where} ";

        $Db_Activity_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata =array();

        if(Buddha_Atom_Array::isValidArray($Db_Activity_arr)){

            $Db_Activity=  $Db_Activity_arr[0];


            if($Db_Activity['shop_id']!=0){
                $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$Db_Activity['shop_id']}' and user_id='{$Db_Activity['user_id']}'");
                $name='商家：'.$shop_name['name'];
            }else{
                $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$Db_Activity['user_id']}'");
                $name='个人：'.$shop_name['name'];
            }



            if($Db_Activity['is_sure']==0){

                $Db_Activity['api_auditstateimg'] = $host.'apistate/menuplus/weishenhe.png';

            }elseif($Db_Activity['is_sure']==4){

                $Db_Activity['api_auditstateimg'] = $host.'apistate/menuplus/weitonguo.png';

            }elseif($Db_Activity['is_sure']==1){

                $Db_Activity['api_auditstateimg'] = $host.'apistate/menuplus/yitonguo.png';

            }


            if(Buddha_Atom_String::isValidString($Db_Activity['img'])){

                $Db_Activity['api_img'] = $host.$Db_Activity['img'];

            }else{

                $Db_Activity['api_img'] = '';
            }

            if($Db_Activity['buddhastatus']==1){

                $Db_Activity['api_buddhastatus'] = '上 架';

            }else if($Db_Activity['buddhastatus']==0){

                $Db_Activity['api_buddhastatus'] = '下 架';

            }

            if($Db_Activity['state']==1){

                $Db_Activity['api_buddhastatus'] = '停 用';

            }else if($Db_Activity['state']==0){

                $Db_Activity['api_buddhastatus'] = '启 用';

            }


            if(!Buddha_Atom_String::isValidString($Db_Activity['shop_name'])){

                $Db_Activity['shop_name'] = $ShopObj->getShopnameFromShopid($Db_Activity['shop_id']);

            }




            $Db_Activity['typeid'] = $Db_Activity['type'];
            $Db_Activity['typename'] = $ActivityObj->getActivitytypenameByActivitytypeid($Db_Activity['type']);



            $signtime = $ActivityObj->getRegistrationtime($Db_Activity);

            $Db_Activity['api_startdate'] = $CommonObj->getDateStrOfTime($Db_Activity['start_date'],0,1) ;
            $Db_Activity['api_enddate'] = $CommonObj->getDateStrOfTime($Db_Activity['end_date'],0,1) ;

            $Db_Activity['api_add_time'] = $CommonObj->getDateStrOfTime($Db_Activity['add_time'],0,1) ;

            $Db_Activity['api_signstarttime'] = $signtime['api_signstarttime'];
            $Db_Activity['api_signendtime'] = $signtime['api_signendtime'];

            $Db_Activity['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['desc']);

            unset( $Db_Activity['img']);
            unset( $Db_Activity['activity_img']);
            unset( $Db_Activity['activity_large']);
            unset( $Db_Activity['activity_thumb']);

            unset( $Db_Activity['type']);
            unset( $Db_Activity['level0']);
            unset( $Db_Activity['level1']);
            unset( $Db_Activity['level2']);
            unset( $Db_Activity['level3']);
            unset( $Db_Activity['level3']);
            unset( $Db_Activity['sourcepic']);


            $jsondata=$Db_Activity;

        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动管理详情');



    }


    /**
     *   首页活动单家或多家：个人报名列表
     */

    public function signuplist(){

        header("Content-Type: text/html; charset=utf8 ");
        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('activity_id','tablename'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $activity_id = (int)Buddha_Http_Input::getParameter('activity_id')?(int)Buddha_Http_Input::getParameter('activity_id'):0;
        $ActivityapplicationObj = new Activityapplication();
        $ActivityObj = new Activity();

        $Actwhere = " id={$activity_id} AND buddhastatus=0 AND is_sure=1";

        /*判断活动是否存在*/
        if(!$ActivityObj->countRecords($Actwhere)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效');
        }

        $Db_Activityapplication = $ActivityapplicationObj->getFiledValues('',"ac_id={$activity_id} order by id desc");
        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Activityapplication)){
            $UserObj = new User();
            foreach($Db_Activityapplication as $k=>$v){
                if($v['u_id']>0){//注册用户
                    $Db_User= $UserObj->getSingleFiledValues(array('logo'),"id={$v['u_id']}");
                    if($Db_User['logo']){
                        $Db_Activityapplication[$k]['logo']=$host.$Db_User['logo'];
                    }else{
                        $Db_Activityapplication[$k]['logo']=$host.'style/images/im.png';//没有头像给默认头像
                    }
                }else{
                    $Db_Activityapplication[$k]['logo']=$host.'style/images/im.png';//非注册用户给默认头像
                }

                $Db_Activityapplication[$k]['username']=mb_substr($v['username'],0,1).'**';

                $Db_Activityapplication[$k]['view']=array(
                    'server'=>'activity.merchantssignuplist',
                    'param'=>array('activityapplication_id'=>$v['id']),
                );
            }
            $jsondata['isok']='true';
            $jsondata['data']=$Db_Activityapplication;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, ' 首页活动单家或多家：个人报名列表');

    }


    /**
     *   个人中心：商家 活动单家或多家 个人报名列表
     */

    public function merchantssignuplist(){

        header("Content-Type: text/html; charset=utf8 ");
        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('activity_id','table_name','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj = new Common();
        $UserObj = new User();
        $ActivityObj = new Activity();
        $ShopObj = new Shop();
        $ActivityapplicationObj = new Activityapplication();

        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }


        $activity_id = (int)Buddha_Http_Input::getParameter('activity_id')?(int)Buddha_Http_Input::getParameter('activity_id'):0;
        $Actwhere = "id={$activity_id}";

        /*判断活动是否存在*/
        if(!$ActivityObj->countRecords($Actwhere)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效');
        }


        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);


        $Db_Activityapplication = $ActivityapplicationObj->getFiledValues(array('id as activityapplication_id','u_id'),"ac_id={$activity_id} order by id desc".Buddha_Tool_Page::sqlLimit ( $page, $pagesize));
        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Activityapplication)){
            $UserObj = new User();
            foreach($Db_Activityapplication as $k=>$v){
                if($v['u_id']>0){//注册用户
                    $Db_User= $UserObj->getSingleFiledValues(array('logo'),"id={$v['u_id']}");
                    if($Db_User['logo']){
                        $Db_Activityapplication[$k]['logo']=$host.$Db_User['logo'];
                    }else{
                        $Db_Activityapplication[$k]['logo']=$host.'style/images/im.png';//没有头像给默认头像
                    }
                }else{
                    $Db_Activityapplication[$k]['logo']=$host.'style/images/im.png';//非注册用户给默认头像
                }
                $Db_Activityapplication[$k]['Services'] = 'activity.merchantssignupview';

                $Db_Activityapplication[$k]['param'] = array('activityapplication_id '=>$v['activityapplication_id']);

            }
            $jsondata['list']=$Db_Activityapplication;
            $tablewhere=$this->prefix.'activityapplication';
            $temp_Common = $CommonObj->pagination($tablewhere, "ac_id={$activity_id}", $pagesize, $page);

            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, ' 个人中心：商家 活动单家或多家 个人报名列表');

    }



    /**
     *   个人中心：商家 活动单家或多家 个人报名列表 商家查看报名者详情（姓名+电话+留言）
     */
    public function merchantssignupview(){

        header("Content-Type: text/html; charset=utf8 ");
        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','activityapplication_id','table_name','table_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $CommonObj = new Common();
        $UserObj = new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        $activityapplication_id = (int)Buddha_Http_Input::getParameter('activityapplication_id')?(int)Buddha_Http_Input::getParameter('activityapplication_id'):0;
        $ActivityapplicationObj = new Activityapplication();

        $table_name = Buddha_Http_Input::getParameter('table_name') ? Buddha_Http_Input::getParameter('table_name') : '';
        $table_id = (int)Buddha_Http_Input::getParameter('table_id') ? (int)Buddha_Http_Input::getParameter('table_id') : 0;

        $ActivityapplicationWhere=" id='{$activityapplication_id}' AND tablename='{$table_name}' AND ac_id='{$table_id}'";

        /*判断活动是否存在*/
        if(!$CommonObj->isToUserByTablenameAndTableid($table_name,$table_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效');
        }

        /*判断个人报名列表是否存在*/
        if(!$ActivityapplicationObj->countRecords($ActivityapplicationWhere)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000046, '活动报名内码ID无效');
        }
        $filed=array('id as activityapplication_id','u_id','username','phone','addtime','message','state');

        $Db_Activityapplication=$ActivityapplicationObj->getSingleFiledValues($filed,$ActivityapplicationWhere);
        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_Activityapplication)){

            $Db_Activityapplication['api_addtime']= $CommonObj->getDateStrOfTime($Db_Activityapplication['addtime'],1,0,0);
            if(!Buddha_Atom_String::isValidString($Db_Activityapplication['state'])){
                $Db_Activityapplication['state']='';
            }
            if(!Buddha_Atom_String::isValidString($Db_Activityapplication['username'])){
                $User_filed=array('realname','mobile','tel');
                $Db_User= $UserObj->getSingleFiledValues($User_filed,"id='{$Db_Activityapplication['u_id']}'");
                $Db_Activityapplication['username']=$Db_User['username'];
                if(Buddha_Atom_String::isValidString($Db_User['mobile'])){
                    $Db_Activityapplication['phone']=$Db_User['mobile'];
                }else{
                    $Db_Activityapplication['phone']=$Db_User['tel'];
                }
            }
            $jsondata=$Db_Activityapplication;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '个人中心：商家 活动单家或多家 个人报名列表 商家查看报名者详情');

    }


    /**
     *   个人中心：商家 合作对象列表
     */

    public function merchantscooperativelist()
    {
        header("Content-Type: text/html; charset=utf8 ");
        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','activity_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $CommonObj = new Common();
        $UserObj = new User();
        $ActivityObj= new Activity();

        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        $activity_id = (int)Buddha_Http_Input::getParameter('activity_id')?(int)Buddha_Http_Input::getParameter('activity_id'):0;

        $api_keyword = Buddha_Http_Input::getParameter('api_keyword')?Buddha_Http_Input::getParameter('api_keyword'):'';/*关键字*/
        $page = (int)Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        /*是否属于该存在*/
        if(!$CommonObj->isIdByTablenameAndTableid($this->tablename,$activity_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效！');
        }

        /*是否属于该用户*/
        if(!$CommonObj->isToUserByTablenameAndTableid($this->tablename,$activity_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000046, '该条记录不属于当前用户！');
        }

        $Db_Activity= $ActivityObj->getSingleFiledValues(array('id','type','vode_type'),"id={$activity_id}");//查询当前活动的活动类型（如果是投票也要查询投票类型）

        $where=' a.act_id='.$activity_id;

        if(Buddha_Atom_String::isValidString($api_keyword)){
            $where.=" AND (s.name like '%{$api_keyword}%' OR s.number LIKE '%{$api_keyword}%')";
        }


        //对应商品（supply:goods_thumb 照片）、个人（user：logo照片）、店铺（shop：small）的（cooID、票数 、名称、 在activitycooperation表中）和活动ID


        $filed="a.id AS activitycooperation_id,a.shop_id,a.shop_name,a.praise_num,a.sure,a.is_sure,a.sore";//在 activitycooperation 表中要显示的字段有： 商品、个人、店铺
        //在 activitycooperation 表中要显示的字段有：在activitycooperation中要显示当前 商品、个人、店铺 的所在行的ID、票数、名称
//        if($title==2){//2人气、3最新
//            $orderby=' order by a.praise_num desc';
//        }elseif($title==3){//2人气、3最新
//            $orderby=' order by a.add_time desc';
//        }
        if(($Db_Activity['type']==3 || $Db_Activity['type']==4) && $Db_Activity['vode_type']==2) {//
            $filed.=',u.logo AS api_img';
            $table='user';
            $as_f='u';
        }else if(($Db_Activity['type']==3 || $Db_Activity['type']==4) && $Db_Activity['vode_type']==3) {

            if($b_display==1){
                $filed.=',s.goods_img AS api_img';
            }else if($b_display==2){
                $filed.=',s.goods_thumb AS api_img';
            }
            $table='supply';
            $as_f='s';
        }else{
            if($b_display==1){
                $filed.=',s.medium AS api_img';
            }else if($b_display==2){
                $filed.=',s.small AS api_img';
            }
            $table='shop';
            $as_f='s';
        }

        $sql ="SELECT {$filed}
               FROM {$this->prefix}activitycooperation AS a 
               INNER join {$this->prefix}{$table} AS {$as_f} 
               ON {$as_f}.id = a.shop_id  
               where {$where} ORDER BY activitycooperation_id DESC ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize);

        $Db_Activitycooperation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata=array();
        $jsondata['list']=array();
        if(Buddha_Atom_Array::isValidArray($Db_Activitycooperation)){
            foreach($Db_Activitycooperation as $k=>$v){

                if(($Db_Activity['type']==3 || $Db_Activity['type']==4) && $Db_Activity['vode_type']==2 AND !Buddha_Atom_String::isValidString($v['api_img'])) {//个人
                    $Db_Activitycooperation[$k]['api_img']=$host.'style/images/im.png';

                }else {
                    if(!Buddha_Atom_String::isValidString($v['api_img'])){
                        $Db_Activitycooperation[$k]['api_img']='';
                    }else{
                        $Db_Activitycooperation[$k]['api_img']=$host.$v['api_img'];
                    }

                }

                $Db_Activitycooperation[$k]['view']=array(
                    'server'=>'activity.merchantscooperativeview',
                    'param'=>array('activitycooperation_id'=>$v['activitycooperation_id']),
                );

                $jsondata['list']=$Db_Activitycooperation;

            }

            $tablewhere="{$this->prefix}activitycooperation as a 
                           INNER join {$this->prefix}{$table} as {$as_f} 
                           on {$as_f}.id = a.shop_id ";
            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];

        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '个人中心：合作对象列表');

    }

    /**
     *   个人中心：商家 合作对象详情
     */

    public function merchantscooperativeview()
    {
        header("Content-Type: text/html; charset=utf8 ");
        $host=Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken','activityapplication_id','activity_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $CommonObj = new Common();
        $UserObj = new User();
        $ActivityObj = new Activity();
        $ActivitycooperationObj = new Activitycooperation();

        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $activityapplication_id = Buddha_Http_Input::getParameter('activityapplication_id') ? Buddha_Http_Input::getParameter('activityapplication_id') : 0;
        $b_display = Buddha_Http_Input::getParameter('b_display') ? Buddha_Http_Input::getParameter('b_display') : 0;
        $activity_id = Buddha_Http_Input::getParameter('activity_id') ? Buddha_Http_Input::getParameter('activity_id') : 0;

        /*查询当前活动的活动类型（如果是投票也要查询投票类型）*/
        $Db_Activity= $ActivityObj->getSingleFiledValues(array('id','type','vode_type','user_id'),"id={$activity_id}");

        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }
        /*是否属于该存在*/
        if(!$CommonObj->isIdByTablenameAndTableid($this->tablename,$activity_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效！');
        }
        /*是否属于该存在*/
        if(!$CommonObj->isIdByTablenameAndTableid('activitycooperation',$activityapplication_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000047, '活动合作对象内码ID无效!！');
        }
        /*是否属于该用户*/

        if($user_id!=$Db_Activity['user_id']){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000046, '该条记录不属于当前用户！');
        }

        $where=' a.id='.$activityapplication_id;
        /*在 activitycooperation 表中要显示的字段有： 商品、个人、店铺*/
        $filed="a.id AS activitycooperation_id,a.shop_id,a.shop_name,a.praise_num,a.sure,a.is_sure,a.sore,a.message,a.u_name,a.u_phone,a.state,a.add_time";
        //在 activitycooperation 表中要显示的字段有：在activitycooperation中要显示当前 商品、个人、店铺 的所在行的ID、票数、名称
//        if($title==2){//2人气、3最新
//            $orderby=' order by a.praise_num desc';
//        }elseif($title==3){//2人气、3最新
//            $orderby=' order by a.add_time desc';
//        }
        if(($Db_Activity['type']==3 || $Db_Activity['type']==4) && $Db_Activity['vode_type']==2) {//
            $filed.=',u.logo AS api_img,u.id AS u_uid ';
            $table='user';
            $as_f='u';
        }else if(($Db_Activity['type']==3 || $Db_Activity['type']==4) && $Db_Activity['vode_type']==3) {

            if($b_display==1){
                $filed.=',s.goods_img AS api_img,s.user_id AS u_uid ';
            }else if($b_display==2){
                $filed.=',s.goods_thumb AS api_img,s.user_id AS u_uid ';
            }
            $table='supply';
            $as_f='s';
        }else{
            if($b_display==1){
                $filed.=',s.medium AS api_img,s.user_id AS u_uid ';
            }else if($b_display==2){
                $filed.=',s.small AS api_img,s.user_id AS u_uid ';
            }
            $table='shop';
            $as_f='s';
        }

        $sql = "SELECT {$filed}
                FROM {$this->prefix}activitycooperation AS a 
                INNER join {$this->prefix}{$table} AS {$as_f} 
                ON {$as_f}.id = a.shop_id  
                WHERE {$where} ORDER BY activitycooperation_id DESC ";

        $Db_Activitycooperation_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Activitycooperation_arr)){
            $Db_Activitycooperation = $Db_Activitycooperation_arr[0];
            $Db_Activitycooperation['api_addtime'] = $CommonObj->getDateStrOfTime($Db_Activitycooperation['add_time']);
            $Db_Activitycooperation['api_img'] = $host.$Db_Activitycooperation['api_img'];

            if(!Buddha_Atom_String::isValidString($Db_Activitycooperation['u_name'])){
                $Db_User= $UserObj->getSingleFiledValues(array('realname', 'mobile','tel'),"id='{$Db_Activitycooperation['u_uid']}'");

                $Db_Activitycooperation['u_name'] = $Db_User['realname'];
                if(Buddha_Atom_String::isValidString($Db_User['mobile'])){

                    $Db_Activitycooperation['u_phone'] = $Db_User['mobile'];
                    $data['u_phone']=$Db_User['mobile'];

                }else{

                    $Db_Activitycooperation['u_phone'] = $Db_User['tel'];
                    $data['u_phone']=$Db_User['tel'];
                }
                $data['u_name']=$Db_User['realname'];
                $ActivitycooperationObj->edit($data,$activityapplication_id);
            }

            $Db_Activitycooperation['audit']=array(
                'server'=>'activity.merchantscooperativeauditing',
                'param'=>array('activitycooperation_id'=>$Db_Activitycooperation['activitycooperation_id']),
            );
            $jsondata=$Db_Activitycooperation;

        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '个人中心：合作对象详情');

    }
    /**
     *   个人中心：商家 发起人审核申请成为合作商家
     */

    public function merchantscooperativeauditing()
    {
        header("Content-Type: text/html; charset=utf8 ");
        $host=Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken','activityapplication_id','activity_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $CommonObj = new Common();
        $UserObj = new User();
        $ActivityObj = new Activity();

        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $activityapplication_id = (int)Buddha_Http_Input::getParameter('activityapplication_id') ? (int)Buddha_Http_Input::getParameter('activityapplication_id') : 0;


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }


        $jsondata = array();

        $sure = (int)Buddha_Http_Input::getParameter('sure');
        $state =Buddha_Http_Input::getParameter('state');
        $ActivitycooperationObj=new Activitycooperation();
        if($sure==0){
            $data['is_sure']=4;
            $data['sure']=4;
        }else{
            $data['is_sure']=$sure;
            $data['sure']=$sure;
        }
        $data['sure_time']=time();
        $data['state']=$state;
        $Db_Activitycooperation_num= $ActivitycooperationObj->edit($data,$activityapplication_id);
        if($Db_Activitycooperation_num){
            $jsondata['db_isok']=1;
            $jsondata['db_msg']='审核成功！';
        }else{
            $jsondata['isok']=0;
            $jsondata['db_msg']='审核失败！';
        }

        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] ='审核成功';
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['activityapplication_id'] = $activityapplication_id;



        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '个人中心：商家 发起人审核申请成为合作商家');

    }

    /**
     *   首页活动单家或多家：个人报名
     */
    public function userregistration()
    {
        header("Content-Type: text/html; charset=utf8 ");
        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('activity_id','tablename'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj=new User();
        $user_id='';
        if(strlen($usertoken)>2){
            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
            $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','tel');
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
            $user_id = $Db_User['id'];
        }

        $ActivityObj = new Activity();
        $CommonObj = new Common();
        $CustomObj = new Custom();
        $CustommessageObj = new Custommessage();
        $ActivityquestionnaireObj = new Activityquestionnaire();
        $ActivityapplicationObj = new Activityapplication();

        $activity_id = (int)Buddha_Http_Input::getParameter('activity_id');
        $user = Buddha_Http_Input::getParameter('user');
        $message = Buddha_Http_Input::getParameter('message');
        $phone = Buddha_Http_Input::getParameter('phone');
        $txt = Buddha_Http_Input::getParameter('txt');//单行
        /*判断 单行 数组是否是Json*/
        if(Buddha_Atom_String::isJson($txt)){
            $txt = json_decode($txt);
        }
        $text = Buddha_Http_Input::getParameter('text');//多行
        /*判断 多行 数组是否是Json*/
        if(Buddha_Atom_String::isJson($text)){
            $txt = json_decode($text);
        }
        $radioname = Buddha_Http_Input::getParameter('radioname');//单选
        /*判断 单选 数组是否是Json*/
        if(Buddha_Atom_String::isJson($radioname)){
            $radioname = json_decode($radioname);
        }
        $checkname = Buddha_Http_Input::getParameter('checkname');//多选
        /*判断 多选 数组是否是Json*/
        if(Buddha_Atom_String::isJson($checkname)){
            $checkname = json_decode($checkname);
        }

        $tablename = Buddha_Http_Input::getParameter('tablename');//是 哪一张表 的报名

        if(!$CommonObj->isIdByTablenameAndTableid($this->tablename,$activity_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000032, '活动类型内码ID无效！');
        }

///////////////////////////////////////////////////////////////////////////////////////////////////////////

        if (!$user && !$phone) {
            /***判断该用户是否存在(是否登录或)****/
            if (empty($user_id)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000061, '你未登录或未注册，请登录或填写姓名和手机号后再报名！');
            } else {
                /****判断用户是否已经报名了***/
                $Db_Activityapplication_num = $ActivityapplicationObj->countRecords("u_id={$user_id} and ac_id={$activity_id}");
                if (!$Db_Activityapplication_num) {
                    //判断用户姓名或联系方式是否为空
                    if($Db_User['mobile']=='' || $Db_User['realname']==''){
                        Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000062, '你的用户信息不完整，为了更好为你服务请完整后再提交或填写姓名和手机号后提交！');
                    }else{

                        if($Db_User['mobile']=='' || $Db_User['tel']==''){
                            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000062, '你的用户信息不完整，为了更好为你服务请完整后再提交或填写姓名和手机号后提交！');
                        }elseif(!$Db_User['mobile'])
                        {
                            $data['phone'] = $Db_User['tel'];
                        }elseif($Db_User['mobile'])
                        {
                            $data['phone'] = $Db_User['mobile'];
                        }
                        $data['username'] = $Db_User['realname'];
                        $data['u_id'] = $user_id;
                        $data_q['act_id'] = $activity_id;
                    }
                } else {
                    Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000063, '您已经报过名了,请不要重复报名！');
                }
            }
        } else {
//判断用户是否已经报名了
            $Db_Activityapplication_num = $ActivityapplicationObj->countRecords("phone={$phone} and ac_id={$activity_id}");
            if ($Db_Activityapplication_num == 0) {
                $data['u_id'] = 0;
                $data['username'] = $user;
                $data['phone'] = $phone;
            } else {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000063, '您已经报过名了,请不要重复报名！');
            }
        }


        $data['tablename'] = $tablename;
        $data['ac_id'] = $activity_id;
        $data['addtime'] = Buddha::$buddha_array['Buddha_timestamp'];
        $data['message'] = $message;
        $data['tablename'] = $tablename;
        $Activityapplication_id = $ActivityapplicationObj->add($data);

        if ($Activityapplication_id) {

            $Custommessage_data['t_name'] = 'custom';
            $Custommessage_data['activityapplication_id'] = $Activityapplication_id;

            if(Buddha_Atom_Array::isValidArray($txt))
            {
                foreach ($txt as $k=>$v){
                    if($k>0)
                    {
                        $Custommessage_data['t_id']=$k;
                        $Custommessage_data['message']=$v;
                    }else
                    {
                        /*这里为了兼容老版*/
                        $where = "t_id='{$activity_id}' AND t_name='activity'";
                        /*通过K获取ID*/
                        $Db_custom = $this->db->getSingleFiledValues(array('id'),'custom',$where." AND arrkey='{$v}'");
                        $Custommessage_data['t_id']=$Db_custom['id'];
                        $Custommessage_data['message']=$v;
                    }
                    $this->db->add( $Custommessage_data,'custommessage');
                }
            }

            if(Buddha_Atom_Array::isValidArray($text))
            {
                foreach ($text as $k=>$v)
                {

                    if($k>0)
                    {
                        $Custommessage_data['t_id'] = $k;
                        $Custommessage_data['message'] = $v;
                        $this->db->edit( $Custommessage_data,'custommessage');
                    }else
                    {
                        /*这里为了兼容老版*/
                        /*通过K获取ID*/
                        $where = "t_id='{$activity_id}' AND t_name='activity'";
                        $Db_custom = $this->db->getSingleFiledValues(array('id'),'custom',$where." AND arrkey='{$v}'");
                        $Custommessage_data['t_id'] = $Db_custom['id'];
                        $Custommessage_data['message'] = $v;
                        $this->db->add( $Custommessage_data,'custommessage');
                    }
                }
            }


            if(Buddha_Atom_Array::isValidArray($radioname))
            {
                foreach ($radioname as $k=>$v)
                {
                    if($k>0)
                    {
                        $Custommessage_data['t_id'] = $k;
                        $Db_Custom = $CustomObj->getSingleFiledValues(array('click_num')," id='{$k}'");
                        $Custommessage_data['click_num'] = $Db_Custom['click_num']+1;
                    }else
                    {
                        /*这里为了兼容老版*/
                        /*通过K获取ID*/
                        $where = "t_id='{$activity_id}' AND t_name='activity'";
                        $Db_custom = $this->db->getSingleFiledValues(array('id'),'custom',$where." AND arrkey='{$v}'");
                        $Custommessage_data['t_id'] = $Db_custom['id'];
                        $Custommessage_data['click_num'] = 1;
                    }
                    $this->db->edit( $Custommessage_data,'custom');
                }
            }



            if(Buddha_Atom_Array::isValidArray($checkname))
            {
                foreach ($checkname as $k=>$v)
                {

                    if($k>0)
                    {
                        $Custommessage_data['t_id'] = $k;
                        $Db_Custom = $CustomObj->getSingleFiledValues(array('click_num')," id='{$k}'");

                        $Custommessage_data['click_num'] = $Db_Custom['click_num']+1;
                    }else
                    {
                        /*这里为了兼容老版*/
                        /*通过K获取ID*/
                        $where = "t_id='{$activity_id}' AND t_name='activity'";
                        $Db_custom = $this->db->getSingleFiledValues(array('id'),'custom',$where." AND arrkey='{$v}'");
                        $Custommessage_data['t_id'] = $Db_custom['id'];
                        $Custommessage_data['click_num'] = 1;
                    }
                    $this->db->add( $Custommessage_data,'custommessage');
                }
            }


            $datas['db_isok'] = 0;
            $datas['db_msg'] = '报名成功！';
        }else {
            $datas['db_isok'] = 1;
            $datas['db_msg'] = '报名失败!';
        }


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        $datas['activity_id'] = $activity_id;
        $jsondata=$datas;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动： 单家活动个人报名');
    }



    /**
     *  个人中心：商家 活动添加之前必须请求的信息
     */
    public function beforeadd()
    {


        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $ActivityObj=new Activity();
        $UserObj=new User();
        $ShopObj=new Shop();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        if(!$ShopObj->IsUserHasShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000042, '你还未创建店铺，快去创建吧！');
        }

        if(!$ShopObj->IsUserHasNormalShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000043, '你名下的所有店铺异常，请处理后一个(要添加到哪个店铺下)或全部店铺后再添加！');
        }

        $jsondata = array();

        /*活动类型*/
        $jsondata['activitytype'] =$ActivityObj->activitytype();

        /*活动投票类型*/
        $jsondata['activityvodetype'] =$ActivityObj->activityvodetype();

        /*地区*/
        $jsondata['region']['Services'] = 'ajaxregion.getBelongFromFatherId';
        $jsondata['region']['param'] = array('father'=>1);

        /*正常店铺列表*/
        $jsondata['shoplist'] = $ShopObj->getShoparrByUserid($user_id);

        /*合作对象接口*/
        $jsondata['cooperative']['Services'] = 'activity.ajaxshop';
        $jsondata['cooperative']['param'] = array();

        /*冠名商家 接口*/
        $jsondata['merchant']['Services'] = 'activity.ajaxnamebusiness';
        $jsondata['merchant']['param'] = array();


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '店铺添加之前的操作接口');
    }


    /**
     * 个人中心：商家 活动添加
     */

    public function add()
    {
        header("Content-Type:text/html;charset=utf-8");

        if (Buddha_Http_Input::checkParameter(array('usertoken','typeid','name','start_date','end_date','coverphoto_arr','shop_id','is_remote','buddhastatus','brief','desc'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $CommonObj=new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $ActivityObj=new Activity();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $MoregalleryObj=new Moregallery();
        $CustomObj=new Custom();
        $JsonimageObj = new Jsonimage();


        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }
        if(!$ShopObj->IsUserHasShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000042, '你还未创建店铺，快去创建吧！');
        }

        if(!$ShopObj->IsUserHasNormalShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000043, '你名下的所有店铺异常，请处理后一个(要添加到哪个店铺下)或全部店铺后再添加！');
        }
        /*发布商家Id*/
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $shop_name=$ShopObj->getShopNameByShopid($shop_id,$user_id);
        /*是否上架*/
        $buddhastatus=(int)Buddha_Http_Input::getParameter('buddhastatus')?Buddha_Http_Input::getParameter('buddhastatus'):0;
        /*判断 $is_verify 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($buddhastatus)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        /*是否异地发布*/
        $is_remote=(int)Buddha_Http_Input::getParameter('is_remote')?Buddha_Http_Input::getParameter('is_remote'):1;
        /*判断 $is_verify 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($is_remote)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }


        /*类型*/
        $type=Buddha_Http_Input::getParameter('typeid');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');



        /*判断活动类型内码ID是否有效*/
        if($ActivityObj->isActivitytypeEffectiveByActivitytypeid($type)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000032	, '活动类型内码ID无效！');
        }



        /*判断店铺Id是否有效*/
        if($ShopObj->isShopByShopid($shop_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000009	, '店铺内码ID无效！');
        }



        /*封面照*/
        $coverphoto_arr =Buddha_Http_Input::getParameter('coverphoto_arr');

        /*判断图片数组是否是Json*/
        if(Buddha_Atom_String::isJson($coverphoto_arr)){
            $coverphoto_arr = json_decode($coverphoto_arr);
        }


        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($coverphoto_arr);


        if($type==3 || $type==4){
            /*冠名商家照*/
            $filebanner_arr =Buddha_Http_Input::getParameter('filebanner_arr');

            /*判断图片数组是否是Json*/
            if(Buddha_Atom_String::isJson($filebanner_arr)){
                $filebanner_arr = json_decode($filebanner_arr);
            }

            /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
            $JsonimageObj->errorDieImageFromUpload($filebanner_arr);
        }



        /*只有在选择了异地发布的时候才验证地区*/
        if($is_remote==1) {
            if (!$RegionObj->isProvince($level1)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
            }

            if (!$RegionObj->isCity($level2)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
            }

            if (!$RegionObj->isArea($level3)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
            }

        }


        /*活动名称*/
        $name=Buddha_Http_Input::getParameter('name');

        /*投票开始时间*/
        $start_date_str=Buddha_Http_Input::getParameter('start_date');

        $start_date=strtotime($start_date_str)?strtotime($start_date_str):0;
        if($start_date==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '时间格式错误！');
        }
        /*投票结束时间*/
        $end_date_str=Buddha_Http_Input::getParameter('end_date');
        $end_date=strtotime($end_date_str)?strtotime($end_date_str):0;
        if($end_date==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000050	, '时间格式错误！');
        }

        /*报名开始时间*/
        $sign_start_time_str=Buddha_Http_Input::getParameter('v_start_date');

        $sign_start_time=strtotime($sign_start_time_str)?strtotime($sign_start_time_str):0;
        if($sign_start_time==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000050	, '时间格式错误！');
        }


        /*报名结束时间*/
        $sign_end_time_str=Buddha_Http_Input::getParameter('v_end_date');
        $sign_end_time=strtotime($sign_end_time_str)?strtotime($sign_end_time_str):0;
        if($sign_end_time==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000050	, '时间格式错误！');
        }

        /*是否上架*/
        $buddhastatus=Buddha_Http_Input::getParameter('buddhastatus')?Buddha_Http_Input::getParameter('buddhastatus'):1;
        /*判断 $buddhastatus 的值是否是0,1*/
        if($CommonObj->isIdInDataEffectiveById($buddhastatus)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444	, '参数错误！');
        }

        /*简述*/
        $brief=Buddha_Http_Input::getParameter('brief');
        /*详情*/
        $desc=Buddha_Http_Input::getParameter('desc');

        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');    //是否异地发布
        /*判断 $is_remote 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($is_remote)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        if($type==1||$type==2){
            //表单
            $text=Buddha_Http_Input::getParameter('text');          //多行
            /*判断 多行 数组是否是Json*/
            if(Buddha_Atom_String::isJson($text)){
                $text = json_decode($text);
            }

            $txt=Buddha_Http_Input::getParameter('txt');            //单行
            /*判断 单行 数组是否是Json*/
            if(Buddha_Atom_String::isJson($txt)){
                $txt = json_decode($txt);
            }
            $radio=Buddha_Http_Input::getParameter('radio');        //单选的内容
            /*判断 单选的内容 数组是否是Json*/
            if(Buddha_Atom_String::isJson($radio)){
                $radio = json_decode($radio);
            }
            $radioname=Buddha_Http_Input::getParameter('radioname');//单选的标题
            /*判断 单选的标题 数组是否是Json*/
            if(Buddha_Atom_String::isJson($radioname)){
                $radioname = json_decode($radioname);
            }
            $checkname=Buddha_Http_Input::getParameter('checkname');//多选的标题
            /*判断 多选的标题 数组是否是Json*/
            if(Buddha_Atom_String::isJson($checkname)){
                $checkname = json_decode($checkname);
            }
            $checkbox=Buddha_Http_Input::getParameter('checkbox');  //多选的内容
            /*判断 多选的内容 数组是否是Json*/
            if(Buddha_Atom_String::isJson($checkbox)){
                $checkbox = json_decode($checkbox);
            }

            $address=Buddha_Http_Input::getParameter('address');    //详细地址
        }
        if($type==2||$type==3||$type==4){
            $coo_shopid=Buddha_Http_Input::getParameter('cooshopid');//合作对象Id
            /*判断 合作对象Id 数组是否是Json*/
            if(Buddha_Atom_String::isJson($coo_shopid)){
                $coo_shopid = json_decode($coo_shopid);
            }
            $coo_shopname=Buddha_Http_Input::getParameter('cooshopname');//合作对象名称
            /*判断 合作对象名称 数组是否是Json*/
            if(Buddha_Atom_String::isJson($coo_shopname)){
                $coo_shopname = json_decode($coo_shopname);
            }
        }

        if($type==3||$type==4){
            $cooshopname_title=Buddha_Http_Input::getParameter('cooshopname_title');//冠名商家名称数组
            /*判断 冠名商家名称 数组是否是Json*/
            if(Buddha_Atom_String::isJson($cooshopname_title)){
                $cooshopname_title = json_decode($cooshopname_title);
            }
            $cooshopid_title=Buddha_Http_Input::getParameter('cooshopid_title');//冠名商家ID数组
            /*判断 冠名商家ID数组 数组是否是Json*/
            if(Buddha_Atom_String::isJson($cooshopid_title)){
                $cooshopid_title = json_decode($cooshopid_title);
            }
            $prize=Buddha_Http_Input::getParameter('prize');         //奖品
            $v_type=Buddha_Http_Input::getParameter('v_type');              //投票或的合作对象类型
        }


        $data=array();
        if($type==1||$type==2){
            $data['address']=$address;
        }
        $CommonObj= new Common();
        $data['name']=$name;
        $data['type']=$type;
        $data['shop_id']=$shop_id;
        $data['shop_name'] = $shop_name;
        $data['add_time']=time();
        $data['user_id']=$user_id;
        $data['number']=$CommonObj->GeneratingNumber();
        $data['start_date']=($start_date);
        $data['end_date']=($end_date);
        $data['sign_start_time']=$sign_start_time;
        $data['sign_end_time']=$sign_end_time;

        $data['start_date_str']=$start_date_str;
        $data['end_date_str']=$end_date_str;
        $data['sign_start_time_str']=$sign_start_time_str;
        $data['sign_end_time_str']=$sign_end_time_str;

        $data['brief']=$brief;
        if($buddhastatus==''){//上架
            $data['buddhastatus']=1;
        }else{
            $data['buddhastatus']=$buddhastatus;
        }
//-----------
        if($type==3||$type==4){
            $data['vode_type']=$v_type;
        }
//-----------

        if($is_remote==0){//$activity_id
            $data['is_remote']=0;
            $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
            $data['level0']=$Db_level['level0'];
            $data['level1']=$Db_level['level1'];
            $data['level2']=$Db_level['level2'];
            $data['level3']=$Db_level['level3'];
        }elseif($is_remote==1){//1为异地

            $data['level1']=$level1;
            $data['level2']=$level2;
            $data['level3']=$level3;
        }
        $activity_id = $ActivityObj->add($data);

        if(!$activity_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000001, $this->tablenamestr.'添加失败！');
        }

        /*自定义表格添加：只有 个人商家1 和 联合商家2 能添加自定义表格*/
        if($type==1||$type==2){

            if(Buddha_Atom_Array::isValidArray($txt)){
                $CustomObj->customadd($txt,$this->tablename,$activity_id);
            }

            if(Buddha_Atom_Array::isValidArray($text)){
                $CustomObj->customadd($txt,$this->tablename,$activity_id);
            }

            if(Buddha_Atom_Array::isValidArray($radioname)){
                $CustomObj->customadd($radioname,$this->tablename,$activity_id);
            }

            if(Buddha_Atom_Array::isValidArray($radio)){
                $CustomObj->customadd($radio,$this->tablename,$activity_id);
            }

            if(Buddha_Atom_Array::isValidArray($checkname)){
                $CustomObj->customadd($checkname,$this->tablename,$activity_id);
            }
            if(Buddha_Atom_Array::isValidArray($checkbox)){
                $CustomObj->customadd($checkbox,$this->tablename,$activity_id);
            }

        }
        /* 冠名商家广告图片 ： 点赞4 和 投票3 能添加 冠名添加*/
        if($type==3||$type==4) {
            /*冠名商家广告图片*/
            if (Buddha_Atom_Array::isValidArray($filebanner_arr)) {
                $savePath = "storage/{$this->tablename}/{$activity_id}/";
                foreach ($filebanner_arr as $k => $v) {
                    $temp_img_arr = explode(',', $v);
                    $temp_base64_string = $temp_img_arr[1];
                    $output_file = date('Ymdhis', time()) . "-{$k}.jpg";
                    $filePath = PATH_ROOT . $savePath . $output_file;
                    Buddha_Atom_File::base64contentToImg($filePath, $temp_base64_string);
                    Buddha_Atom_File::resolveImageForRotate($filePath, NULL);
                    $result_img = $savePath . '' . $output_file;
                    $MoreImage[] = "{$result_img}";
                }
                if (Buddha_Atom_Array::isValidArray($MoreImage)) {
                    $uploadfield = 'filebanner_arr';
                    $moregallery_id = $MoregalleryObj->pcaddimage($uploadfield,$MoreImage,$activity_id,$this->tablename,$cooshopid_title,$user_id );
                }
            }

        }

        /*封面照相册*/
        if(Buddha_Atom_Array::isValidArray($coverphoto_arr)){
            $savePath="storage/{$this->tablename}/{$activity_id}/";
            foreach($coverphoto_arr as $k=>$v){
                $temp_img_arr = explode(',', $v);
                $temp_base64_string = $temp_img_arr[1];
                $output_file = date('Ymdhis',time()). "-{$k}.jpg";
                $filePath =PATH_ROOT.$savePath.$output_file;
                Buddha_Atom_File::base64contentToImg($filePath,$temp_base64_string);
                Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
                $result_img = $savePath.''.$output_file;
                $MoreImage[] = "{$result_img}";
            }
            if(Buddha_Atom_Array::isValidArray($MoreImage)){
                $uploadfield='coverphoto_arr';
                $MoregalleryObj->addImageArrToMoregallery($MoreImage,$activity_id,$savePath,$shop_id,$uploadfield);

                /*把封面照设为默认展示图片并把相应的图片路径更新到activity表中*/
                $ActivityObj->setFirstMoreImageImgToActivity($activity_id);
            }
        }

        /*富文本编辑器图片处理*/
        if($desc){
            $MoregalleryObj=new Moregallery();
            $field='desc';
            $saveData_desc = $MoregalleryObj->base_upload($desc,$activity_id,$this->tablename,$field);
            $saveData_desc = str_replace(PATH_ROOT,'/', $saveData_desc);
            if ($type==3||$type==4){
                /*富文本编辑器图片处理*/
                if($prize){
                    $prizefield='prize';
                    $saveData_prize = $MoregalleryObj->base_upload($prize,$activity_id,$this->tablename,$prizefield);
                    $saveData_prize = str_replace(PATH_ROOT,'/', $saveData_prize);
                    $details['prize'] = $saveData_prize;
                }
            }
            $details['desc'] = $saveData_desc;
            $ActivityObj->edit($details,$activity_id);
        }

        /*合作商家添加*/
        if(($type==2 ||$type==3||$type==4) && !empty($coo_shopid)){
            $ActivitycooperationObj=new Activitycooperation();
            if($type==2||$type==3||$type==4){
                if(($type==1||$type==2|| ($type==3&& $v_type==1) )){
                    array_push($coo_shopid,$shop_id);//先把自己的店铺添加到合作商家中、
                    array_push($coo_shopname,$shop_name);//先把自己的店铺添加到合作商家中、
                }
                $coonum[]=$ActivitycooperationObj->cooadd($coo_shopid,$activity_id,$coo_shopname);

            }
        }



        /*是否产生订单：0否；1是*/
        $is_needcreateorder = 0;
        $Services ='';
        $param = array();
//        $remote为1表示发布异地产品添加订单
        if($is_remote==1){
            $is_needcreateorder = 1;
            $Services = '';
            $param = array();
        }

        $jsondata = array();
        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] =$this->tablenamestr.'添加成功';
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['activity_id'] = $activity_id;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'添加');
    }


    /**
     * 个人中心：商家 活动删除
     */

    public function del()
    {
        header("Content-Type:text/html;charset=utf-8");

        if (Buddha_Http_Input::checkParameter(array('usertoken','activity_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $MoregalleryObj=new Moregallery();
        $CustomObj=new Custom();
        $ActivitycooperationObj = new Activitycooperation();
        $ActivityapplicationObj = new Activityapplication();
        $ActivityObj = new Activity();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $activity_id = Buddha_Http_Input::getParameter('activity_id')?Buddha_Http_Input::getParameter('activity_id'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }

        /*判断活动Id是否有效*/
        if(!$ActivityObj->isActivityidValid($activity_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033	, '活动内码ID无效！');
        }


///////
        /** $moregallery_id==0;表示删除全部冠名商家*/
        /*新的：coverphoto_arr 封面照相册、filebanner_arr 冠名商家 广告图片；
           旧的：file 添加封面照片、file_title 冠名商家*/

//        $where="tablename=activity";
        /*老版：user_id=0 and webfield=file_title*/
//        $where_userid0 = $where." AND webfield=file_title AND user_id=0";
        /*新版：user_id>0 and webfield=filebanner_arr*/
//        $where_userid1 = $where." AND webfield=filebanner_arr AND user_id='{$user_id}'}'";
///////
//            /**
//             * 删除相册（封面照片+冠名商家）
//             * 思路：先查询出该活动在相册中的所有图片的数据，先把图片文件夹的数据删除，然后再删除相册图片
//             *          新的：coverphoto_arr 封面照相册、    filebanner_arr  冠名商家 广告图片；
//                        旧的：file           封面照相册、    file_title      冠名商家
//             */
/////////////////////////////////////////////////////////////////////////////////////////////////////
//        $where="tablename=activity AND goods_id='{$activity_id}'";
//        /*老版：user_id=0*/
//        $where_userid0  = $where.' AND user_id=0';
//        /*新版：user_id>0 */
//        $where_userid1 = $where." AND user_id='{$user_id}'";
//        $Moregallery_where='';
//        if($MoregalleryObj->countRecords($where_userid0)){
//            $Moregallery_where.=$where_userid0;
//        }else{
//            $Moregallery_where=$where_userid1;
//        }
//
//        $Db_Moregallery = $MoregalleryObj->getFiledValues(array('id','goods_thumb','goods_img','goods_large','sourcepic'),$Moregallery_where);
//
//        if(Buddha_Atom_Array::isValidArray($Db_Moregallery)){
//            $idstr='';
//            foreach ($Db_Moregallery as $k=>$v){
//
//                if(Buddha_Atom_String::isValidString($v['goods_thumb'])){
//                    @unlink(PATH_ROOT.$v['goods_thumb']);
//                }
//                if(Buddha_Atom_String::isValidString($v['goods_img'])){
//                    @unlink(PATH_ROOT.$v['goods_img']);
//                }
//                if(Buddha_Atom_String::isValidString($v['goods_large'])){
//                    @unlink(PATH_ROOT.$v['goods_large']);
//                }
//                if(Buddha_Atom_String::isValidString($v['sourcepic'])){
//                    @unlink(PATH_ROOT.$v['sourcepic']);
//                }
//                $idstr.=$v['id'].',';
//            }
//        }
//        $idstr=rtrim($idstr,',');
//        $Moregallery_where.=" AND id IN ({$idstr})";
//        $Db_Moregallery_num = $this->db->delRecords ('moregallery',$Moregallery_where);
//
/////////////////////////////////////////////////////////////////////////////////////////////////////
/**自定义表单 删除***/
///////////////////////////////////////////////////////////////////////////////////////////////////
        $custom_where="t_name=activity AND t_id='{$activity_id}'";
        if($CustomObj->countRecords($custom_where)){
            $idstr='';
            $Db_Custom = $CustomObj->getFiledValues(array('id'),$custom_where);
            foreach ($Db_Custom as $k=>$v){
                $idstr.=$v['id'].',';
            }
            $idstr=rtrim($idstr,',');
            $custom_where.=" AND id IN ({$idstr}) ";
            $Db_Custom_num = $this->db->delRecords ('custom',$custom_where);
        }
///////////////////////////////////////////////////////////////////////////////////////////////////
        /**活动 合作对象 删除***/
///////////////////////////////////////////////////////////////////////////////////////////////////
        $Activitycooperation_where="t_name=activity AND act_id='{$activity_id}' AND u_id='{$user_id}'";
        if($ActivitycooperationObj->countRecords($custom_where)){
            $idstr='';
            $Db_Activitycooperation = $ActivitycooperationObj->getFiledValues(array('id'),$Activitycooperation_where);
            foreach ($Db_Activitycooperation as $k=>$v){
                $idstr.=$v['id'].',';
            }
            $idstr=rtrim($idstr,',');
            $custom_where.=" AND id IN ({$idstr}) ";
            $Db_Activitycooperation_num = $this->db->delRecords ('activitycooperation',$Activitycooperation_where);
        }
///////////////////////////////////////////////////////////////////////////////////////////////////
        /**活动 报名表 删除 ***/
///////////////////////////////////////////////////////////////////////////////////////////////////
        $activityapplication_where="tablename=activity AND ac_id='{$activity_id}' AND u_id='{$user_id}'";
        if($ActivityapplicationObj->countRecords($custom_where)){
            $idstr='';
            $Db_Activityapplication = $ActivityapplicationObj->getFiledValues(array('id'),$activityapplication_where);
            foreach ($Db_Activityapplication as $k=>$v){
                $idstr.=$v['id'].',';
            }
            $idstr=rtrim($idstr,',');
            $activityapplication_where.=" AND id IN ({$idstr}) ";
            $Db_Activityapplication_num = $this->db->delRecords ('activityapplication',$activityapplication_where);
        }
///////////////////////////////////////////////////////////////////////////////////////////////////
/**活动数据 删除***/
///////////////////////////////////////////////////////////////////////////////////////////////////

//        $Db_Activity_num = $this->db->delRecords ( 'activity', "id='{$activity_id}'" );
        $Db_Activity_num = $ActivityObj->toCleanTrash($activity_id,$user_id);
        $jsondata = array();
        if($Db_Activity_num){
            $jsondata['is_ok']=1;
            $jsondata['db_msg']=$this->tablenamestr.'删除成功!';
        }else{
            $jsondata['is_ok']=0;
            $jsondata['db_msg']=$this->tablenamestr.'删除失败!';
        }
///////////////////////////////////////////////////////////////////////////////////////////////////
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['activity_id'] = $activity_id;
//        /**相册 删除接口*/
//        $jsondata['album']=array(
//            'Server'=>'moregallery.deleteimage',
//            'param'=>array('moregallery_id'=>$activity_id,'table_name'=>$this->tablename),
//        );
//        /**冠名商家 删除接口*/
//        $jsondata['namebusiness']=array(
//            'Server'=>'activity.ajaxnamebusinessdel',
//            'param'=>array('activity_id'=>$activity_id,'moregallery_id'=>0),
//        );
//        /**自定义表单  删除接口*/
//        $jsondata['custom']=array(
//            'Server'=>'activity.customdel  ',
//            'param'=>array('moregallery_id'=>$activity_id,'table_name'=>$this->tablename),
//        );
//        /**合作对象 删除接口*/
//        $jsondata['activitycooperation']['Services']='activity.ajaxshopdel';
//        $jsondata['activitycooperation']['param']=array('activity_id'=>$activity_id,'activitycooperation_id'=>0);

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'删除');
    }


    /**
     *  个人中心：商家 活动更新（编辑）之前必须访问的接口
     */
    public function beforeupdate()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','activity_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $ShopObj=new Shop();
        $UserObj=new User();
        $RegionObj=new Region();
        $ActivityObj=new Activity();
        $CommonObj=new Common();
        $CustomObj=new Custom();
        $ActivitycooperationObj=new Activitycooperation();
        $ActivityapplicationObj=new Activityapplication();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $activity_id = (int)Buddha_Http_Input::getParameter('activity_id')?Buddha_Http_Input::getParameter('activity_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        if(!$ActivityObj->isActivityidValid($activity_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效！');
        }

        if(!$ActivityObj->isActivityidAndUidValid($activity_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000046, '该条记录不属于当前用户！');
        }
        /*活动报名表*/
        $app= $ActivityapplicationObj->countRecords("ac_id=$activity_id");
        /*活动合作商家(并且通过了商家的审核的)*/
        $coo= $ActivitycooperationObj->countRecords("act_id={$activity_id} and is_sure=1");
        if($app>0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000034, '对不起该活动已经有人报名了不能再更改了！');
        }

        if($coo>0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000035, '对不起该活动已经有商家报名了不能再更改了！');

        }
        if(!$ShopObj->IsUserHasShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000042, '你还未创建店铺，快去创建吧！');
        }

        if(!$ShopObj->IsUserHasNormalShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000043, '你名下的所有店铺异常，请处理后一个(要添加到哪个店铺下)或全部店铺后再添加！');
        }

        $fields = 'id AS activity_id, shop_id, shop_name,name AS activity_name, address, brief, prize,
                   type as typeid,vode_type as api_vodetypeid,start_date,end_date,sign_start_time,sign_end_time,add_time,click_count,
                   `desc`,is_remote, level1,level2,level3';

        if($b_display==1){
            $fields.=' ,activity_img AS img ';
        }elseif($b_display==2){
            $fields.=' , activity_thumb AS img ';
        }

        /*isdel=0 表示正常；isdel=5 表示选择了店铺认证单未支付的店铺*/
        $where=" id='{$activity_id}' AND user_id='$user_id' AND isdel=0 ";


        $sql ="select {$fields} FROM {$this->prefix}activity WHERE {$where} ";
        $Db_Activity_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();

        if(Buddha_Atom_Array::isValidArray($Db_Activity_arr))
        {
            $Db_Activity = $Db_Activity_arr[0];
            if(Buddha_Atom_Array::isValidArray($Db_Activity))
            {
                $Db_Activity['img']=$host.$Db_Activity['img'];
            }else{
                $Db_Activity['img'] = '';
            }

            $Db_Activity['mobile']=$ShopObj->isShowPhpone($Db_Activity['shop_id'],$usertoken);

            $Db_Activity['api_startdate']=$CommonObj->getDateStrOfTime($Db_Activity['start_date'],0,1);
            $Db_Activity['api_enddate']=$CommonObj->getDateStrOfTime($Db_Activity['end_date'],0,1);

            $signtime = $ActivityObj->getRegistrationtime($Db_Activity);

            $Db_Activity['api_signstarttime'] = $signtime['api_signstarttime'];

            $Db_Activity['api_signendtime'] = $signtime['api_signendtime'];

            $Db_Activity['api_add_time'] = $CommonObj->getDateStrOfTime($Db_Activity['add_time'],0,1,1);

            $Db_Activity['desc'] = Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['desc']);
            $Db_Activity['prize'] = Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['prize']);


            $Db_Activity['api_type'] = $ActivityObj->getactivitytypenameforactivitytyid((int)$Db_Activity['typeid']);


            /*活动类型*/
            $jsondata['activitytype'] = $ActivityObj->activitytypeselect($Db_Activity['typeid']);

            /*活动投票类型*/
            $jsondata['activityvodetype'] = $ActivityObj->activityvodetypeselect($Db_Activity['api_vodetypeid']);

            /*地区*/
            $Db_Activity['api_area'] = $RegionObj->getDetailOfAdrressByRegionIdStr($Db_Activity['level1'],$Db_Activity['level2'],$Db_Activity['level3'],$Spacer='>');

            /*正常店铺列表*/
            $jsondata['shoplist'] = $ShopObj->getShoparrByUserid($user_id,$Db_Activity['shop_id']);

            $jsondata['list'] = $Db_Activity;

                /*自定义表单*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
            $Custom_filed = array('id as custom_id','c_title','c_type','sub','c_type','sort','sub_1','arrkey');
            $Db_Custom = $CustomObj->getFiledValues($Custom_filed,"t_id='{$activity_id}' AND t_name='{$this->tablename}' ORDER BY `sort` ASC ");

            if(Buddha_Atom_Array::isValidArray($Db_Custom)){
                /**自定义表单 删除 接口*/
                foreach($Db_Custom as $k=>$v){
                    $Db_Custom[$k]['Services']='activity.customdel';
                    $Db_Custom[$k]['param']=array('activity_id'=>$activity_id,'custom_id'=>$v['custom_id'],'table_name'=>'activity');
                }
            }
            $jsondata['custom']=$Db_Custom;
/////////////////////////////////////// /////////////// /////////////// /////////////// /////////////// /////////////// /////////////// ///////////////
            /***合作对象***/
//////////////////////////////////////////////////////////////////
            $filed='m.id as moregallery_is,m.imgkey,m.shop_id,s.name ';
            if($b_display==1){
                $filed.=',m.goods_large as img';
            }else if($b_display==2){
                $filed.=',m.goods_img as img';
            }

            $sql ="select {$filed} 
               from {$this->prefix}moregallery as m 
               left join {$this->prefix}shop as s 
               on s.id = m.shop_id 
               where goods_id ={$activity_id} and tablename='{$this->tablename}' and webfield='file_title' 
               order by m.imgkey desc";//查询冠名商家
            $Db_Moregallery = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            if(Buddha_Atom_Array::isValidArray($Db_Moregallery)){
                /**冠名商家 删除 接口*/
                foreach($Db_Moregallery as $k=>$v){
                    $Db_Moregallery[$k]['Services']='activity.ajaxnamebusinessdel';
                    $Db_Moregallery[$k]['param']=array('activity_id'=>$activity_id,'moregallery_id'=>$v['moregallery_id']);
                }
            }
            $jsondata['merchant']=$Db_Moregallery;
//////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////
             /**合作对象列表***/
            $activitycooperationfiled=array('id as activitycooperation_id','shop_id','shop_name');

            $Db_Activitycooperation=  $ActivitycooperationObj->getFiledValues($activitycooperationfiled,"act_id ={$activity_id}");//查询合作商家
            if(Buddha_Atom_Array::isValidArray($Db_Activitycooperation)){
                foreach ($Db_Activitycooperation as $k=>$v)
                {
                    $Db_Activitycooperation[$k]['Services']='activity.ajaxshopdel';
                    $Db_Activitycooperation[$k]['param']=array('activity_id'=>$activity_id,'activitycooperation_id'=>$v['activitycooperation_id']);
                }
            }
            $jsondata['activitycooperation']=$Db_Activitycooperation;
/// ///////////////////////////////////////////////////////////

            /*活动类型*/
            $jsondata['activitytype'] =$ActivityObj->activitytype();

            /*活动投票类型*/
            $jsondata['activityvodetype'] =$ActivityObj->activityvodetype();

            /*地区*/
            $jsondata['region']['Services'] = 'ajaxregion.getBelongFromFatherId';
            $jsondata['region']['param'] = array('father '=>1);

            /*正常店铺列表*/
            $jsondata['shoplist'] = $ShopObj->getShoparrByUserid($user_id);

            /*合作对象接口*/
            $jsondata['cooperative']['Services'] = 'activity.ajaxshop';
            $jsondata['cooperative']['param'] = array();

            /*冠名商家 接口*/
            $jsondata['merchant']['Services'] = 'activity.ajaxnamebusiness';
            $jsondata['merchant']['param'] = array();
        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动编辑之前的操作接口');
    }



    /**
     *  个人中心：商家 活动更新
     */

    public function update()
    {

        if (Buddha_Http_Input::checkParameter(array('activity_id','usertoken','typeid','start_date','coverphoto_arr','end_date','name','shop_id','is_remote','realname','mobile','shop_id','buddhastatus','brief','desc'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $activity_id = Buddha_Http_Input::getParameter('activity_id')?Buddha_Http_Input::getParameter('activity_id'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $ActivityObj=new Activity();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $MoregalleryObj=new Moregallery();
        $CustomObj=new Custom();
        $JsonimageObj = new Jsonimage();
        $CommonObj = new Common();


        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }
        if(!$ShopObj->IsUserHasShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000042, '你还未创建店铺，快去创建吧！');
        }

        if(!$ShopObj->IsUserHasNormalShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000043, '你名下的所有店铺异常，请处理后一个(要添加到哪个店铺下)或全部店铺后再添加！');
        }
        if(!$ActivityObj->isActivityidValid($activity_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效！');
        }

        if(!$ActivityObj->isActivityidAndUidValid($activity_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000040, '该活动信息不属于当前用户！');
        }

        /*发布商家Id*/
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $shop_name=$ShopObj->getShopNameByShopid($shop_id,$user_id);
        /*是否异地发布*/
        $is_remote=(int)Buddha_Http_Input::getParameter('is_remote')?Buddha_Http_Input::getParameter('is_remote'):1;
        /*判断 $is_remote 的值是否是0,1*/
        if($CommonObj->isIdInDataEffectiveById($is_remote)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }
        /*类型*/
        $type=Buddha_Http_Input::getParameter('typeid');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');

        /*投票开始时间*/
        $start_date_str=Buddha_Http_Input::getParameter('start_date');

        $start_date=strtotime($start_date_str)?strtotime($start_date_str):0;
        if($start_date==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '时间格式错误！');
        }
        /*投票结束时间*/
        $end_date_str=Buddha_Http_Input::getParameter('end_date');
        $end_date=strtotime($end_date_str)?strtotime($end_date_str):0;
        if($end_date==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000050	, '时间格式错误！');
        }

        /*报名开始时间*/
        $sign_start_time_str=Buddha_Http_Input::getParameter('v_start_date');

        $sign_start_time=strtotime($sign_start_time_str)?strtotime($sign_start_time_str):0;
        if($sign_start_time==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000050	, '时间格式错误！');
        }

        /*报名结束时间*/
        $sign_end_time_str=Buddha_Http_Input::getParameter('v_end_date');
        $sign_end_time=strtotime($sign_end_time_str)?strtotime($sign_end_time_str):0;
        if($sign_end_time==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000050	, '时间格式错误！');
        }


        /*判断活动类型内码ID是否有效*/
        if($ActivityObj->isActivitytypeEffectiveByActivitytypeid($type)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000032	, '活动类型内码ID无效！');
        }

        /*判断店铺Id是否有效*/
        if($ShopObj->isShopByShopid($shop_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000009	, '店铺内码ID无效！');
        }




        /*封面照*/
        $coverphoto_arr =Buddha_Http_Input::getParameter('coverphoto_arr');
        /*判断图片数组是否是Json*/
        if(Buddha_Atom_String::isJson($coverphoto_arr)){
            $coverphoto_arr = json_decode($coverphoto_arr);
        }



        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($coverphoto_arr);


        if($type==3 || $type==4){
            /*冠名商家照*/
            $filebanner_arr =Buddha_Http_Input::getParameter('filebanner_arr');

            /*判断图片数组是否是Json*/
            if(Buddha_Atom_String::isJson($filebanner_arr)){
                $filebanner_arr = json_decode($filebanner_arr);
            }

            /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
            $JsonimageObj->errorDieImageFromUpload($filebanner_arr);
        }



        /*只有在选择了异地发布的时候才验证地区*/
        if($is_remote==1) {
            if (!$RegionObj->isProvince($level1)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
            }

            if (!$RegionObj->isCity($level2)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
            }

            if (!$RegionObj->isArea($level3)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
            }

        }


        /*活动名称*/
        $name=Buddha_Http_Input::getParameter('name');
        /*投票开始时间*/
        $start_date=Buddha_Http_Input::getParameter('start_date');
        /*投票结束时间*/
        $end_date=Buddha_Http_Input::getParameter('end_date');
        /*报名开始时间*/
        $sign_start_time=Buddha_Http_Input::getParameter('v_start_date');
        /*报名结束时间*/
        $sign_end_time=Buddha_Http_Input::getParameter('v_end_date');

        /*简述*/
        $brief=Buddha_Http_Input::getParameter('brief');
        /*详情*/
        $desc=Buddha_Http_Input::getParameter('desc');
        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');    //是否异地发布
        /*是否上架*/
        $buddhastatus=(int)Buddha_Http_Input::getParameter('buddhastatus')?Buddha_Http_Input::getParameter('buddhastatus'):0;
        /*判断 $is_verify 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($buddhastatus)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        if($type==1||$type==2){
            //表单
            $text=Buddha_Http_Input::getParameter('text');          //多行
            /*判断 多行 数组是否是Json*/
            if(Buddha_Atom_String::isJson($text)){
                $text = json_decode($text);
            }
            $txt=Buddha_Http_Input::getParameter('txt');            //单行
            /*判断 单行 数组是否是Json*/
            if(Buddha_Atom_String::isJson($txt)){
                $txt = json_decode($txt);
            }
            $radio=Buddha_Http_Input::getParameter('radio');        //单选的内容
            /*判断 单选的内容 数组是否是Json*/
            if(Buddha_Atom_String::isJson($radio)){
                $radio = json_decode($radio);
            }
            $radioname=Buddha_Http_Input::getParameter('radioname');//单选的标题
            /*判断 单选的标题 数组是否是Json*/
            if(Buddha_Atom_String::isJson($radioname)){
                $radioname = json_decode($radioname);
            }
            $checkname=Buddha_Http_Input::getParameter('checkname');//多选的标题
            /*判断 多选的标题 数组是否是Json*/
            if(Buddha_Atom_String::isJson($checkname)){
                $checkname = json_decode($checkname);
            }
            $checkbox=Buddha_Http_Input::getParameter('checkbox');  //多选的内容
            /*判断 多选的内容 数组是否是Json*/
            if(Buddha_Atom_String::isJson($checkbox)){
                $checkbox = json_decode($checkbox);
            }

            $address=Buddha_Http_Input::getParameter('address');    //详细地址
        }
        if($type==2||$type==3||$type==4){
            $coo_shopid=Buddha_Http_Input::getParameter('cooshopid');//合作商家Id
            /*判断 合作商家Id 数组是否是Json*/
            if(Buddha_Atom_String::isJson($coo_shopid)){
                $coo_shopid = json_decode($coo_shopid);
            }
            $coo_shopname=Buddha_Http_Input::getParameter('cooshopname');//合作商家名称
            /*判断 合作商家名称 数组是否是Json*/
            if(Buddha_Atom_String::isJson($coo_shopname)){
                $coo_shopname = json_decode($coo_shopname);
            }
        }

        if($type==3||$type==4){
            $cooshopname_title=Buddha_Http_Input::getParameter('cooshopname_title');//冠名商家名称数组
            /*判断 冠名商家名称 数组是否是Json*/
            if(Buddha_Atom_String::isJson($cooshopname_title)){
                $cooshopname_title = json_decode($cooshopname_title);
            }
            $cooshopid_title=Buddha_Http_Input::getParameter('cooshopid_title');//冠名商家ID数组
            /*判断 冠名商家ID数组 数组是否是Json*/
            if(Buddha_Atom_String::isJson($cooshopid_title)){
                $cooshopid_title = json_decode($cooshopid_title);
            }
            $prize=Buddha_Http_Input::getParameter('prize');         //奖品
            $v_type=Buddha_Http_Input::getParameter('v_type');              //投票或的合作对象类型
        }




        $data=array();
        if($type==1||$type==2){
            $data['address']=$address;
        }
        $CommonObj= new Common();
        $data['name']=$name;
        $data['type']=$type;
        $data['shop_id']=$shop_id;
        $data['shop_name'] = $shop_name;
        $data['user_id']=$user_id;
        $data['start_date']=strtotime($start_date);
        $data['end_date']=strtotime($end_date);
        $data['sign_start_time']=strtotime($sign_start_time);
        $data['sign_end_time']=strtotime($sign_end_time);

        $data['start_date_str']=$start_date_str;
        $data['end_date_str']=$end_date_str;
        $data['sign_start_time_str']=$sign_start_time_str;
        $data['sign_end_time_str']=$sign_end_time_str;


        $data['brief']=$brief;
        if($buddhastatus==''){//上架
            $data['buddhastatus']=1;
        }else{
            $data['buddhastatus']=$buddhastatus;
        }
//-----------
        if($type==3||$type==4){
            $data['vode_type']=$v_type;
        }
//-----------

        if($is_remote==0){//$activity_id
            $data['is_remote']=0;
            $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
            $data['level0']=$Db_level['level0'];
            $data['level1']=$Db_level['level1'];
            $data['level2']=$Db_level['level2'];
            $data['level3']=$Db_level['level3'];
        }elseif($is_remote==1){//1为异地

            $data['level1']=$level1;
            $data['level2']=$level2;
            $data['level3']=$level3;
        }
        $activity_id = $ActivityObj->edit($data,$activity_id);

        if(!$activity_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000001, '活动添加失败！');
        }

        $tablename='activity';


        /*自定义表格添加：只有 个人商家1 和 联合商家2 能添加自定义表格*/
        if($type==1||$type==2){

            if(Buddha_Atom_Array::isValidArray($txt)){
                $CustomObj->customadd($txt,$tablename,$activity_id);
            }

            if(Buddha_Atom_Array::isValidArray($text)){
                $CustomObj->customadd($txt,$tablename,$activity_id);
            }

            if(Buddha_Atom_Array::isValidArray($radioname)){
                $CustomObj->customadd($radioname,$tablename,$activity_id);
            }

            if(Buddha_Atom_Array::isValidArray($radio)){
                $CustomObj->customadd($radio,$tablename,$activity_id);
            }

            if(Buddha_Atom_Array::isValidArray($checkname)){
                $CustomObj->customadd($checkname,$tablename,$activity_id);
            }
            if(Buddha_Atom_Array::isValidArray($checkbox)){
                $CustomObj->customadd($checkbox,$tablename,$activity_id);
            }
        }
        /* 冠名商家广告图片 ： 点赞4 和 投票3 能添加 冠名添加*/
        if($type==3||$type==4) {
            /*冠名商家广告图片*/
            if (Buddha_Atom_Array::isValidArray($filebanner_arr)) {
                $savePath = "storage/{$tablename}/{$activity_id}/";
                foreach ($filebanner_arr as $k => $v) {
                    $temp_img_arr = explode(',', $v);
                    $temp_base64_string = $temp_img_arr[1];
                    $output_file = date('Ymdhis', time()) . "-{$k}.jpg";
                    $filePath = PATH_ROOT . $savePath . $output_file;
                    Buddha_Atom_File::base64contentToImg($filePath, $temp_base64_string);
                    Buddha_Atom_File::resolveImageForRotate($filePath, NULL);
                    $result_img = $savePath . '' . $output_file;
                    $MoreImage[] = "{$result_img}";
                }
                if (Buddha_Atom_Array::isValidArray($MoreImage)) {
                    $uploadfield = 'filebanner_arr';
                    $moregallery_id=$MoregalleryObj->pcaddimage($uploadfield,$MoreImage,$activity_id,$tablename,$cooshopid_title,$user_id );
                }
            }
        }

        /*封面照相册*/
        if(Buddha_Atom_Array::isValidArray($coverphoto_arr)){
            $savePath="storage/{$tablename}/{$activity_id}/";
            foreach($coverphoto_arr as $k=>$v){
                $temp_img_arr = explode(',', $v);
                $temp_base64_string = $temp_img_arr[1];
                $output_file = date('Ymdhis',time()). "-{$k}.jpg";
                $filePath =PATH_ROOT.$savePath.$output_file;
                Buddha_Atom_File::base64contentToImg($filePath,$temp_base64_string);
                Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
                $result_img = $savePath.''.$output_file;
                $MoreImage[] = "{$result_img}";
            }
            if(Buddha_Atom_Array::isValidArray($MoreImage)){
                $uploadfield='coverphoto_arr';
                $MoregalleryObj->addImageArrToMoregallery($MoreImage,$activity_id,$savePath,$shop_id,$uploadfield);

                /*把封面照设为默认展示图片并把相应的图片路径更新到activity表中*/
                $ActivityObj->setFirstMoreImageImgToActivity($activity_id);
            }
        }

        /*富文本编辑器图片处理*/
        if($desc){
            $MoregalleryObj=new Moregallery();
            $field='desc';
            $saveData_desc = $MoregalleryObj->base_upload($desc,$activity_id,$tablename,$field);
            $saveData_desc = str_replace(PATH_ROOT,'/', $saveData_desc);
            if ($type==3||$type==4){
                /*富文本编辑器图片处理*/
                if($prize){
                    $prizefield='prize';
                    $saveData_prize = $MoregalleryObj->base_upload($prize,$activity_id,$tablename,$prizefield);
                    $saveData_prize = str_replace(PATH_ROOT,'/', $saveData_prize);
                    $details['prize'] = $saveData_prize;
                }
            }
            $details['desc'] = $saveData_desc;
            $ActivityObj->edit($details,$activity_id);
        }

        /*合作商家添加*/
        if(($type==2 ||$type==3||$type==4) && !empty($coo_shopid)){
            $ActivitycooperationObj=new Activitycooperation();
            if($type==2||$type==3||$type==4){
                if(($type==1||$type==2|| ($type==3&& $v_type==1) )){
                    array_push($coo_shopid,$shop_id);//先把自己的店铺添加到合作商家中、
                    array_push($coo_shopname,$shop_name);//先把自己的店铺添加到合作商家中、
                }
                $coonum[]=$ActivitycooperationObj->cooadd($coo_shopid,$activity_id,$coo_shopname);

            }
        }


        /*是否产生订单：0否；1是*/
        $is_needcreateorder = 0;
        $Services ='';
        $param = array();
//        $remote为1表示发布异地产品添加订单
        if($is_remote==1){
            $is_needcreateorder = 1;
            $Services = '';
            $param = array();
        }


        $jsondata = array();
        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] =$this->tablenamestr.'添加成功';
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['activity_id'] = $activity_id;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '店铺更新/编辑');
    }


    /**
     *个人中心：商家  活动更新 自定义表单删除
     */
    public function customdel()
    {
        header("Content-Type:text/html;charset=utf-8");
        $tablenamestr='自定义表单';
        $tablename='custom';
        if (Buddha_Http_Input::checkParameter(array('usertoken','activity_id','custom_id','table_name'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $CommonObj=new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $activity_id = (int)Buddha_Http_Input::getParameter('table_id')?(int)Buddha_Http_Input::getParameter('table_id'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $table_nme = Buddha_Http_Input::getParameter('table_nme')?Buddha_Http_Input::getParameter('table_nme'):'';
        $custom_id = (int)Buddha_Http_Input::getParameter('custom_id')?(int)Buddha_Http_Input::getParameter('custom_id'):0;

        $ActivityObj=new Activity();
        $CommonObj = new Common();

        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }

        /*判断活动Id是否有效*/
        if(!$ActivityObj->isActivityidValid($activity_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033	, $this->tablenamestr.'内码ID无效！');
        }
        /*判断活动是否属于当前用户*/
        if(!$CommonObj->isActivityidAndUidValid($activity_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000046	, '该条记录不属于当前用户！');
        }



        /*判断自定义表单的内码ID 是否有效*/
        if(!$CommonObj->isToUserByTablenameAndTableid($table_nme,$custom_id,$user_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000040	, $tablenamestr.'内码ID无效！');
        }

        $Db_Activity_num=$this->db->delRecords ($tablename, "id='{$activity_id}' AND t_name='{$table_nme}' AND t_id='{$activity_id}'" );
        $jsondata = array();

        if($Db_Activity_num){
            $jsondata['is_ok']=1;
            $jsondata['db_msg']=$tablenamestr.'删除成功!';
        }else{
            $jsondata['is_ok']=0;
            $jsondata['db_msg']=$tablenamestr.'删除失败!';
        }


        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['activity_id'] = $activity_id;
        $jsondata['custom_id'] = $custom_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动更新'.$tablenamestr.'删除');
    }


    /**
     *  首页活动投票：投票
     * http://www.bendishangjia.com/index.php?a=vodelist&c=activity&id=166
     */
    public function vote()
    {

        header("Content-Type:text/html;charset=utf-8");
        $tablenamestr = $this->tablenamestr.'合作对象投票';
        $tablename = 'activitycooperation';

        if (Buddha_Http_Input::checkParameter(array('usertoken','table_id','table_name','activitycooperation_id','shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $CommonObj = new Common();
        $ActivitycooperationObj = new Activitycooperation();
        $VodetimeObj = new Vodetime();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $table_id = (int)Buddha_Http_Input::getParameter('table_id')?(int)Buddha_Http_Input::getParameter('table_id'):0;

        $table_name = Buddha_Http_Input::getParameter('table_name')?Buddha_Http_Input::getParameter('table_name'):'';
        /*检测活动是否存在*/
        if(!$CommonObj->isIdByTablenameAndTableid($table_name,$table_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效！！');
        }

        $newtime=time();
        /*判断活动是否能够投票：是否在投票期间*/
        if(!$this->db->countRecords($table_name," id ='$table_id' AND ( start_date<=$newtime AND $newtime<=end_date)")){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000048, '活动未开始或已结束，不能投票!');
        }


        /*合作对象内码ID */
        $activitycooperation_id = (int)Buddha_Http_Input::getParameter('activitycooperation_id')?(int)Buddha_Http_Input::getParameter('activitycooperation_id'):0;
        /*投票合作对象为：商家、个人、产品；这里合作店铺ID或者产品或者个人的内码ID  */
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;
        if(!$CommonObj->isIdByTablenameAndTableid('activitycooperation',$activitycooperation_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000047, '活动合作对象内码ID无效！');
        }

        $jsondata=array();
        if(empty($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000043, '请登录后再投票(如果没有帐号请注册！)');
        }else{
            $CommonObj = new Common();
            $time = $CommonObj->time_handle();

            $where = $time['where'];//今天的0点< 当前时间 < 明天的0点时间
            $vodewhere = $where." and act_id ={$table_id} and whichtable='{$tablename}' and u_id={$user_id} and shop_id={$shop_id}";

            /*查询用户是否在一天当中是否针对同一家已经投过票了*/
            $count = $VodetimeObj->getSingleFiledValues(array('id','shop_id','v_time'),$vodewhere);

            if($count){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000042, '你对该商家今天已经投过票了，请选择其它的吧！');
            }else{
                /*查询合作商家表中的ID//查询该商家的投票次数*/
                $Db_Activitycooperation = $ActivitycooperationObj->getSingleFiledValues(array('id','praise_num')," act_id={$table_id} and id={$shop_id}");
                $data['u_id'] = $user_id;
                $data['whichtable'] = $tablename;
                $data['table_id'] = $Db_Activitycooperation['id'];
                $data['act_id'] = $table_id;
                $data['v_time'] = time();
                $data['shop_id'] = $shop_id;
                $Db_Vodetime = $VodetimeObj->add($data);

                /*该商家的投票次数加一*/
                $data_coo['praise_num'] = $Db_Activitycooperation['praise_num']+1;
                $ActivitycooperationObj->edit($data_coo,$Db_Activitycooperation['id']);

                if($Db_Vodetime){
                    $jsondata['db_msg'] = '投票成功';
                    $jsondata['db_isok'] = 1;
                    $jsondata['vodenum'] = $data_coo['praise_num'];
                }else{
                    $jsondata['db_msg'] = '投票失败';
                    $jsondata['db_isok'] = 0;
                }
            }
            $tableid = $table_name.'_id';
            $jsondata['usertoken'] = $usertoken;
            $jsondata['activitycooperation_id'] = $activitycooperation_id;
            $jsondata[$tableid] = $table_id;
        }



        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '合作对象投票'.$tablenamestr);
    }


    /**
     * 首页：活动合作对象列表（商家、产品、个人）
    */
    public function activitycooperationmore()
    {
        $host = Buddha::$buddha_array['host'];
        header("Content-Type:text/html;charset=utf-8");

        if (Buddha_Http_Input::checkParameter(array('table_id','table_name','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj = new Common();
        $ActivityObj= new Activity();


        $table_id = (int)Buddha_Http_Input::getParameter('table_id')?(int)Buddha_Http_Input::getParameter('table_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;

        $table_name = Buddha_Http_Input::getParameter('table_name')?Buddha_Http_Input::getParameter('table_name'):'';

        /*检测活动是否存在*/
        if(!$CommonObj->isIdByTablenameAndTableid($table_name,$table_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033, '活动内码ID无效！！');
        }

        $view = Buddha_Http_Input::getParameter('title')?Buddha_Http_Input::getParameter('view'):2;//2人气、3最新

        $api_keyword = Buddha_Http_Input::getParameter('api_keyword')?Buddha_Http_Input::getParameter('api_keyword'):'';
        $page = Buddha_Http_Input::getParameter('page') ? Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);


        $Db_Activity= $ActivityObj->getSingleFiledValues(array('id','type','vode_type'),"id={$table_id}");//查询当前活动的活动类型（如果是投票也要查询投票类型）
        $limit = Buddha_Tool_Page::sqlLimit ($page, $pagesize);
        $where = ' a.act_id='.$table_id;
        if(!empty($api_keyword)){
            $where.=" and (s.name like '%{$api_keyword}%' or s.number like '%{$api_keyword}%')";
        }

        /*统计该活动下有没有合作对象: 这里不加合作对象是否属于前期不加*/

        $Db_Activitycooperation_num=$this->db->countRecords('activitycooperation','act_id='.$table_id);

        if(!$Db_Activitycooperation_num){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000044, '该活动还没有合作对象，赶快去添加吧！');

        }

        /*对应商品（supply:goods_thumb 照片）、个人（user：logo照片）、店铺（shop：small）的（cooID、票数 、名称、 在activitycooperation表中）和活动ID*/

        /*在 activitycooperation 表中要显示的字段有： 商品、个人、店铺*/
        $filed = "a.id AS activitycooperation_id,a.shop_id,a.shop_name,a.praise_num,a.sure,a.is_sure,a.sore";
        /*在 activitycooperation 表中要显示的字段有：在activitycooperation中要显示当前 商品、个人、店铺 的所在行的ID、票数、名称*/
        if($view==2){//2人气、3最新
            $orderby = ' ORDER BY a.praise_num DESC';
        }elseif($view==3){//2人气、3最新
            $orderby = ' ORDER BY a.add_time DESC';
        }
        if(($Db_Activity['type']==3 || $Db_Activity['type']==4) && $Db_Activity['vode_type']==2) {//
            $filed.=',u.logo AS api_img';
            $jointable = 'user';
            $as_f = 'u';
        }else if(($Db_Activity['type']==3 || $Db_Activity['type']==4) && $Db_Activity['vode_type']==3) {
            $filed.=',s.goods_thumb AS api_img';
            $jointable = 'supply';
            $as_f = 's';
        }else{
            $filed.=',s.small AS api_img';
            $jointable = 'shop';
            $as_f = 's';
        }
        $sql = "select {$filed}
               from {$this->prefix}activitycooperation as a 
               INNER join {$this->prefix}{$jointable} as {$as_f} 
               on {$as_f}.id = a.shop_id  
               where {$where} {$orderby} {$limit}";
        $Db_Activitycooperation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        foreach($Db_Activitycooperation  AS $k=>$v){
            if(($Db_Activity['type']==3 || $Db_Activity['type']==4) AND $Db_Activity['vode_type']==2 ) {//个人
                if(!Buddha_Atom_String::isValidString($v['api_img'])){
                    $Db_Activitycooperation[$k]['api_img']=$host.$v['api_img'];
                }else{
                    $Db_Activitycooperation[$k]['api_img']=$host.'style/images/im.png';
                }
            }else{//产品
                if(Buddha_Atom_String::isValidString($v['api_img'])){
                    $Db_Activitycooperation[$k]['api_img']=$host.$v['api_img'];
                }else{
                    $Db_Activitycooperation[$k]['api_img']='';
                }
            }
        }

        if(Buddha_Atom_Array::isValidArray($Db_Activitycooperation)){
            /*个人*/
            if(($Db_Activity['type']==3 || $Db_Activity['type']==4) && $Db_Activity['vode_type']==2) {

                $services='';
                $tablename='';
            /*产品*/
            }else if(($Db_Activity['type']==3 || $Db_Activity['type']==4) && $Db_Activity['vode_type']==3) {

                $services = 'multisingle.supplysingle';
                $tablename = 'supply';
            /*店铺*/
            }else{
                $services='shop.view';
                $tablename='shop';
            }

            $tablenid = $tablename.'_id';

            foreach ($Db_Activitycooperation as $k=>$v){
                $Db_Activitycooperation[$k]['table_name']=$tablename;

                if($services!=''){
                    $Db_Activitycooperation[$k]['view']['services'] = $services;
                    $Db_Activitycooperation[$k]['view']['param'] = array($tablenid=>$v['shop_id']);
                }else{
                    $Db_Activitycooperation[$k]['view']['services'] = '';
                    $Db_Activitycooperation[$k]['view']['param'] = array();
                }

                $Db_Activitycooperation[$k]['vote']=array(
                    'services'=>'activity.vote',
                    'services'=>array('activitycooperation_id'=>$v['activitycooperation_id'],'shop_id'=>$v['shop_id'],'activity_id'=>$table_id,'table_name'=>$this->tablename),
                );

            }

            $jsondata['list'] = $Db_Activitycooperation;
            $tablewhere = "{$this->prefix}activitycooperation as a 
                           INNER JOIN {$this->prefix}{$jointable} as {$as_f} 
                           on {$as_f}.id = a.shop_id ";
            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];
        }else{
            $datas['isok'] = 'false';
        }


        $tableid = $table_name.'_id';
        $jsondata[$tableid] = $table_id;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '首页活动投票：合作对象列表');
    }


    /***
     * 添加：ajax合作对象列表
     * AJAX操作-活动添加 获取合作对象列表
    **/
    public function ajaxshop()
    {

        header("Content-Type:text/html;charset=utf-8");
        if (Buddha_Http_Input::checkParameter(array('usertoken','b_display','linenumber','api_number','typeid','votypeid'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $CommonObj = new Common();
        $RegionObj = new Region();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");

        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):0;


        /*城市编号*/
        $api_number = (int)Buddha_Http_Input::getParameter('api_number')?Buddha_Http_Input::getParameter('api_number'):0;
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }

        $locdata = $RegionObj->getApiLocationByNumberArr($api_number);

        $api_keyword = Buddha_Http_Input::getParameter('api_keyword')?Buddha_Http_Input::getParameter('api_keyword'):''; //搜索关键字
        $linenumber = (int)Buddha_Http_Input::getParameter('linenumber')?(int)Buddha_Http_Input::getParameter('linenumber'):1;//行号（代表第几个合作的）
        /*当为产品时，为必填项*/
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;//发布店铺ID


        $typeid = (int)Buddha_Http_Input::getParameter('typeid')?(int)Buddha_Http_Input::getParameter('typeid'):0;//活动类型
        if(!Buddha_Atom_String::isValidString($typeid)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }
        if($typeid==1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000055, '活动商家个人，没有合作对象,请重新选择活动类型吧！');
        }

        /***只有活动类型为： 商家联合（typeid=2）和 投票（typeid=3）有合作对象***/
        if(!$CommonObj->isIdInDataEffectiveById($typeid,array(2,3))){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        $votypeid = (int)Buddha_Http_Input::getParameter('votypeid')?(int)Buddha_Http_Input::getParameter('votypeid'):0;//投票类型

        if($typeid == 3 ){
            if(!$CommonObj->isIdInDataEffectiveById($votypeid,$data=array(1,2,3))){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
            }

            if(!Buddha_Atom_String::isValidString($shop_id) AND $votypeid==3){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000057, '请选择发布店铺后,再选择合作对象吧！');
            }
        }

        /**活动类型为：商家联合(合作对象为商家)***/
        $limit=' LIMIT 100 ';
        $order=' ORDER BY id DESC  ';
        $where=" isdel=0 {$locdata['sql']} ";

        /**活动类型为：商家联合(合作对象为商家)***/
        if (($typeid == 2 AND $votypeid==0) OR ($typeid == 3 AND $votypeid==1)){
            $table_name='shop';
            $filed=array('id','name','realname','mobile','number','roadfullname');
            $where.=' AND state=0';
            /**合作对象为商家搜索关键字为： 店铺名称 + 店铺编号*/
            if(Buddha_Atom_String::isValidString($api_keyword)){
                $where.=" AND name LIKE '%{$api_keyword}%' AND number LIKE '%{$api_keyword}%' ";
            }

        /**活动类型为：投票(合作对象为个人)***/
        }elseif ($typeid == 3 AND $votypeid == 2){
            $table_name='user';
            $filed=array('id','realname AS name','mobile');
            $where.=' AND  state=0';
            /**合作对象为商家搜索关键字为： 姓名 + 手机*/
            if(Buddha_Atom_String::isValidString($api_keyword)){
                $where.=" AND name LIKE '%{$api_keyword}%' AND mobile LIKE '%{$api_keyword}%' ";
            }
        /**活动类型为：投票(合作对象为产品)***/
        }elseif ($typeid == 3 AND $votypeid == 3){
            $table_name='shop';
            $filed=array('id','goods_name as name','goods_sn AS number');
            $where.=' AND buddhastatus=0 AND is_sure=1 ';
            /**合作对象为商家搜索关键字为： 商品名称 + 商品编号*/
            if(Buddha_Atom_String::isValidString($api_keyword)){
                $where.=" AND goods_name LIKE '%{$api_keyword}%' AND goods_sn LIKE '%{$api_keyword}%' ";
            }
        }

        $Db_Table=$this->db->getFiledValues($filed,$this->prefix.$table_name,$where.$order.$limit);

        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Table)){
            if($b_display==1){
                $maxfileds=20;
            }elseif($b_display==2){
                $maxfileds=10;
            }

            if (($typeid == 2 AND $votypeid==0) OR ($typeid == 3 AND $votypeid==1)){
                foreach($Db_Table as $k=>$v){
                    $Db_Table[$k]['name'] = array('namestr'=>'店铺名称','name'=>mb_substr($v['name'],0,$maxfileds).'**');
                    $Db_Table[$k]['number'] = array('namestr'=>'店铺编号','name'=>$v['number']);
                    $Db_Table[$k]['realname'] = array('namestr'=>'店铺联系人','name'=>mb_substr($v['realname'],0,2).'**');
                    if(Buddha_Atom_String::isValidString($v['roadfullname'])){
                        $Db_Table[$k]['roadfullname'] = array('namestr'=>'店铺地址','name'=>mb_substr($v['roadfullname'],0,$maxfileds).'**');
                    }else{
                        $Db_Table[$k]['roadfullname'] = array('namestr'=>'店铺地址','name'=>'');
                    }
                    $qs = substr($v['mobile'],0,3);
                    $hs = substr($v['mobile'],-3);
                    if(Buddha_Atom_String::isValidString($v['mobile'])){
                        $Db_Table[$k]['mobile'] = array('namestr'=>'店铺联系电话','name'=>$qs.'*****'.$hs);
                    }else{
                        $Db_Table[$k]['mobile'] = array('namestr'=>'店铺联系电话','name'=>'');
                    }
                }
            }elseif ($typeid == 3 AND $votypeid == 2){
                foreach($Db_Table as $k=>$v){
                    $qs = substr($v['mobile'],0,3);
                    $hs = substr($v['mobile'],-3);
                    $Db_Table[$k]['name'] = array('namestr'=>'用户姓名','name'=>mb_substr($v['name'],0,2).'**');
                    $Db_Table[$k]['mobile'] = array('namestr'=>'用户电话','name'=>$qs.'*****'.$hs);
                }
            }elseif ($typeid == 3 AND $votypeid == 3) {
                foreach($Db_Table as $k=>$v){
                    $Db_Table[$k]['name'] = array('namestr'=>'店铺名称','name'=>mb_substr($v['name'],0,$maxfileds).'**');
                    $Db_Table[$k]['number'] = array('namestr'=>'商品编号','name'=>$v['number']);
                }
            }
            $jsondata['list'] = $Db_Table;
            $jsondata['linenumber'] = $linenumber;

        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, 'AJAX操作-活动添加 获取合作对象列表');
    }



    /**
     * 个人中心：商家 活动更新 活动合作对象删除
     */

    public function ajaxshopdel()
    {
        header("Content-Type:text/html;charset=utf-8");

        if (Buddha_Http_Input::checkParameter(array('usertoken','activity_id','activitycooperation_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $CommonObj=new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $activity_id = Buddha_Http_Input::getParameter('activity_id')?Buddha_Http_Input::getParameter('activity_id'):0;
        $activitycooperation_id = Buddha_Http_Input::getParameter('activitycooperation_id')?Buddha_Http_Input::getParameter('activitycooperation_id'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $ActivityObj = new Activity();

        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        /*判断活动Id是否有效*/
        if(!$ActivityObj->isActivityidValid($activity_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033	, '活动内码ID无效！');
        }

        /*判断活动合作对象Id是否有效*/
        if(!$CommonObj->isIdByTablenameAndTableid('activitycooperation',$activitycooperation_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000058	, '合作对象内码ID无效！');
        }

        $Db_Activity_num=$this->db->delRecords ('activitycooperation', "id='{$activitycooperation_id}'" );
        $jsondata = array();
        if($Db_Activity_num){
            $jsondata['db_isok']=1;
            $jsondata['db_msg']='合作对象删除成功!';
        }else{
            $jsondata['db_isok']=0;
            $jsondata['db_msg']='合作对象删除失败!';
        }

        $jsondata['usertoken'] = $usertoken;
        $jsondata['activity_id'] = $activity_id;
        $jsondata['activitycooperation_id'] = $activitycooperation_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动更新：活动合作对象删除');
    }





    /**
     * 添加：ajax 冠名商家列表
    */
    public function ajaxnamebusiness()
    {
        header("Content-Type:text/html;charset=utf-8");
        if (Buddha_Http_Input::checkParameter(array('usertoken','b_display','linenumber','api_number','typeid','votypeid'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $CommonObj = new Common();
        $RegionObj = new Region();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");

        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):0;


        /*城市编号*/
        $api_number = (int)Buddha_Http_Input::getParameter('api_number')?Buddha_Http_Input::getParameter('api_number'):0;
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }

        $locdata = $RegionObj->getApiLocationByNumberArr($api_number);

        $api_keyword = Buddha_Http_Input::getParameter('api_keyword')?Buddha_Http_Input::getParameter('api_keyword'):''; //搜索关键字
        $linenumber = (int)Buddha_Http_Input::getParameter('linenumber')?(int)Buddha_Http_Input::getParameter('linenumber'):1;//行号（代表第几个合作的）

        $typeid = (int)Buddha_Http_Input::getParameter('typeid')?(int)Buddha_Http_Input::getParameter('typeid'):0;//活动类型
        if(!Buddha_Atom_String::isValidString($typeid)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }
        if($typeid==1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000055, '活动商家个人，没有合作对象,请重新选择活动类型吧！');
        }

        if(!$CommonObj->isIdInDataEffectiveById($typeid,array(1,2,3))){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        $votypeid = (int)Buddha_Http_Input::getParameter('votypeid')?(int)Buddha_Http_Input::getParameter('votypeid'):0;//投票类型

        /***只有活动类型为： 投票（typeid=3 并且$votypeid=1,2,3）有冠名商家***/

        if($typeid == 3 AND $votypeid>0){
            if(!$CommonObj->isIdInDataEffectiveById($votypeid,$data=array(1,2,3))){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
            }
        }




        $limit=' LIMIT 100 ';
        $order=' ORDER BY id DESC  ';
        $where=" isdel=0 {$locdata['sql']} ";


        $table_name='shop';
        $filed=array('id','name','realname','mobile','number','roadfullname');
        $where.=' AND state=0';
        /**合作对象为商家搜索关键字为： 店铺名称 + 店铺编号*/
        if(Buddha_Atom_String::isValidString($api_keyword)){
            $where.=" AND name LIKE '%{$api_keyword}%' AND number LIKE '%{$api_keyword}%' ";
        }

        $Db_Table=$this->db->getFiledValues($filed,$this->prefix.$table_name,$where.$order.$limit);

        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Table)){
            if($b_display==1){
                $maxfileds=20;
            }elseif($b_display==2){
                $maxfileds=10;
            }

            foreach($Db_Table as $k=>$v){
                $Db_Table[$k]['name'] = array('namestr'=>'店铺名称','name'=>mb_substr($v['name'],0,$maxfileds).'**');
                $Db_Table[$k]['number'] = array('namestr'=>'店铺编号','name'=>$v['number']);
                $Db_Table[$k]['realname'] = array('namestr'=>'店铺联系人','name'=>mb_substr($v['realname'],0,2).'**');
                if(Buddha_Atom_String::isValidString($v['roadfullname'])){
                    $Db_Table[$k]['roadfullname'] = array('namestr'=>'店铺地址','name'=>mb_substr($v['roadfullname'],0,$maxfileds).'**');
                }else{
                    $Db_Table[$k]['roadfullname'] = array('namestr'=>'店铺地址','name'=>'');
                }
                $qs = substr($v['mobile'],0,3);
                $hs = substr($v['mobile'],-3);
                if(Buddha_Atom_String::isValidString($v['mobile'])){
                    $Db_Table[$k]['mobile'] = array('namestr'=>'店铺联系电话','name'=>$qs.'*****'.$hs);
                }else{
                    $Db_Table[$k]['mobile'] = array('namestr'=>'店铺联系电话','name'=>'');
                }
            }

            $jsondata['list'] = $Db_Table;
            $jsondata['linenumber'] = $linenumber;

        }










        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, 'aAJAX操作-活动添加 冠名商家列表');
    }


    /**
     * 个人中心：商家 活动更新 冠名商家 删除
     */

    public function ajaxnamebusinessdel()
    {
        header("Content-Type:text/html;charset=utf-8");

        if (Buddha_Http_Input::checkParameter(array('usertoken','activity_id','moregallery_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $CommonObj=new Common();
        $MoregalleryObj=new Moregallery();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $activity_id = Buddha_Http_Input::getParameter('activity_id')?Buddha_Http_Input::getParameter('activity_id'):0;
        $moregallery_id = Buddha_Http_Input::getParameter('moregallery_id')?Buddha_Http_Input::getParameter('moregallery_id'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $ActivityObj = new Activity();

        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        /*判断活动Id是否有效*/
        if(!$ActivityObj->isActivityidValid($activity_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033	, '活动内码ID无效！');
        }
        if($moregallery_id){
            /*判断活动 冠名商家 Id是否有效*/
            if(!$CommonObj->isIdByTablenameAndTableid('moregallery',$moregallery_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000059	, '冠名商家ID无效！');
            }
        }

        if($moregallery_id>0){
            /*新的：coverphoto_arr 封面照相册、filebanner_arr 冠名商家广告图片；
               旧的：file 添加封面照片、file_title 冠名商家*/

            $where="tablename = activity AND id ='{$moregallery_id}'";
            /*老版：user_id=0 and webfield=file_title*/
            $where_userid0=$where." AND webfield=file_title AND user_id=0'";
            /*新版：user_id>0 and webfield=filebanner_arr*/
            $where_userid1=$where." AND webfield=filebanner_arr AND user_id='{$user_id}'}'";

            if($MoregalleryObj->countRecords($where_userid0)){
                $Db_Moregallery_num = $this->db->delRecords ('moregallery', $where_userid0);
            }else{
                $Db_Moregallery_num = $this->db->delRecords ('moregallery', $where_userid1);
            }
        }

//        $Db_Moregallery_num = $this->db->delRecords ('moregallery', $where_userid0);

        $jsondata = array();
        if($Db_Moregallery_num){
            $jsondata['db_isok']=1;
            $jsondata['db_msg']='冠名商家删除成功!';
        }else{
            $jsondata['db_isok']=0;
            $jsondata['db_msg']='冠名商家删除失败!';
        }

        $jsondata['usertoken'] = $usertoken;
        $jsondata['activity_id'] = $activity_id;
        $jsondata['moregallery_id'] = $moregallery_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '活动更新：活动 冠名商家 删除');
    }




    /**
     * 个人中心：商家 查询自定义表单 反馈数据
    */
    public function amountofclicks()
    {
        header("Content-Type:text/html;charset=utf-8");

        if (Buddha_Http_Input::checkParameter(array('usertoken','activity_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $CommonObj = new Common();
        $CustomObj = new Custom();
        $ActivityObj = new Activity();
        $CustommessageObj = new Custommessage();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $activity_id = Buddha_Http_Input::getParameter('activity_id')?Buddha_Http_Input::getParameter('activity_id'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        /*判断活动Id是否有效*/
        if(!$CommonObj->isToUserByTablenameAndTableid($this->tablename,$activity_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000033	, '活动内码ID无效！');
        }
        $Customfiled=array('id as custom_id','c_type','c_title','click_num','sort');
        /***sub_1=0 先查询出单行、多行、单选标题、多选标题 ***/
        $where=" t_name='custom' AND t_id='{$activity_id}'";
        $Customwhere=$where." AND sub_1=0";
        $Customorder=' ORDER BY sort ASC';
        $limit=Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $jsondata = array();
        $jsondata['page'] = 0;
        $jsondata['pagesize'] =0;
        $jsondata['totalrecord'] = 0;
        $jsondata['totalpage'] = 0;
        $jsondata['list'] = array();
        $Db_Custom= $CustomObj->getFiledValues($Customfiled,$Customwhere.$Customorder.$limit);

        if(Buddha_Atom_Array::isValidArray($Db_Custom)){
            /**
             * c_type = 1 单行
             * c_type = 2 多行
             * c_type = 3 单选  sub_1 =0/''   表示标题；大于0表示内容
             * c_type = 4 多选  sub_1  =0/''  表示标题；大于0表示内容
             */
            foreach ($Db_Custom as $k=>$v){
                if($v['c_type']==1 OR $v['c_type']==2){
                   $Db_Custommessage= $CustommessageObj->getSingleFiledValues(array('id AS custommessage_id','message'),"t_id='{$v['t_id']}' AND t_name='{$this->tablename}' ");
                   if(Buddha_Atom_Array::isValidArray($Db_Custommessage)){
                       $Db_Custom[$k]['message'] = $Db_Custommessage;
                   }else{
                       $Db_Custom[$k]['message'] = array();
                   }
                   $Db_Custom[$k]['son'] = array();
                }elseif($v['c_type']==3 OR $v['c_type'] == 2){
                    $Db_Custom[$k]['message'] = array();
                    $Custom_where = $where." AND sort={$v['sort']} AND sub_1!=0";
                    $Db_Custom_1 = $CustomObj->getFiledValues($Customfiled,$Custom_where.$Customorder.$limit);
                    $Db_Custom[$k]['son'] = $Db_Custom_1;
                }
            }


            $jsondata['list'] = $Db_Custom;
            $tablewhere = $this->prefix.'custom';
            $temp_Common = $CommonObj->pagination($tablewhere, $Customwhere, $pagesize, $page);
            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '商家:查询自定义表单 反馈数据');
    }


}
