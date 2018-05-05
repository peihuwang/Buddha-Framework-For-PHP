<?php

/**
 * Class SingleinformationController
 */
class SingleinformationController extends Buddha_App_Action
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
       $this->tablenamestr='单页信息';
       $this->tablename='singleinformation';
    }




    /**
     * 个人中心：单页信息详情
     */
    public function managementview()
    {
        header("Content-Type: text/html; charset=utf8 ");
        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken','singleinformation_id','groupid'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $ShopObj= new Shop();
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


        $api_singleinformationid = (int)Buddha_Http_Input::getParameter('singleinformation_id')?Buddha_Http_Input::getParameter('singleinformation_id'):0;

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $titel='';
        if(($groupid==1 || $typeid==1) || ($groupid==4 || $typeid==4)){

            /*商家和个人：对于单页信息的管理具有相同的功能*/

            $where = " isdel=0 AND user_id='{$user_id}' AND id='{$api_singleinformationid}' ";
            if($groupid==1 || $typeid==1){
                $titel='商家';
            }elseif($groupid==4 || $typeid==4){
                $titel='普通会员';
            }
        }elseif($groupid==2 || $typeid==2){

            /*代理商只能查看未审核的详情*/
            $where=" id='{$api_singleinformationid}' AND is_sure=0 ";
            $titel='代理商';
        }elseif($groupid==3 || $typeid==3){

            /*合伙人：合伙人角色不具备该功能*/
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000023, '合伙人角色不具备该功能！');

        }



        $sql =" SELECT  * 
                FROM {$this->prefix}singleinformation WHERE {$where} ";
        $Db_Singleinformation_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata =array();
        $jsondata['list'] =array();

        if(Buddha_Atom_Array::isValidArray($Db_Singleinformation_arr)){

            $Db_Singleinformation=  $Db_Singleinformation_arr[0];

            $Db_Singleinformation['singleinformation_id']=$Db_Singleinformation['id'];
            if($Db_Singleinformation['shop_id']!=0){
                $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$Db_Singleinformation['shop_id']}' and user_id='{$Db_Singleinformation['user_id']}'");
                $name='商家：'.$shop_name['name'];
            }else{
                $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$Db_Singleinformation['user_id']}'");
                $name='个人：'.$shop_name['name'];
            }


            if($Db_Singleinformation['is_sure']==0){

                $Db_Singleinformation['api_auditstateimg']=$host.'apistate/menuplus/weishenhe.png';

            }elseif($Db_Singleinformation['is_sure']==4){

                $Db_Singleinformation['api_auditstateimg']=$host.'apistate/menuplus/weitonguo.png';

            }elseif($Db_Singleinformation['is_sure']==1){

                $Db_Singleinformation['api_auditstateimg']=$host.'apistate/menuplus/yitonguo.png';

            }

            if($Db_Singleinformation['buddhastatus']==1){

                $Db_Singleinformation['api_buddhastatus']='上 架';

            }else if($Db_Singleinformation['buddhastatus']==0){

                $Db_Singleinformation['api_buddhastatus']='下 架';

            }

            if($Db_Singleinformation['state']==1){

                $Db_Singleinformation['api_buddhastatus']='停 用';

            }else if($Db_Singleinformation['state']==0){

                $Db_Singleinformation['api_buddhastatus']='启 用';

            }


            if(!Buddha_Atom_String::isValidString($Db_Singleinformation['shop_name'])){

                $Db_Singleinformation['shop_name']=$ShopObj->getShopnameFromShopid($Db_Singleinformation['shop_id']);

            }

            if($b_display==2){

                $Db_Singleinformation['img']=$host.$Db_Singleinformation['singleinformation_thumb'];

            }elseif($b_display==1){


                $Db_Singleinformation['img']=$host.$Db_Singleinformation['singleinformation_thumb'];

            }

            $Db_Singleinformation['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Singleinformation['desc']);
            unset( $Db_Singleinformation['singleinformation_img']);
            unset( $Db_Singleinformation['singleinformation_large']);
            unset( $Db_Singleinformation['singleinformation_thumb']);
            unset( $Db_Singleinformation['user_id']);
            unset( $Db_Singleinformation['id']);
            unset( $Db_Singleinformation['level0']);
            unset( $Db_Singleinformation['level1']);
            unset( $Db_Singleinformation['level2']);
            unset( $Db_Singleinformation['level3']);
            unset( $Db_Singleinformation['level3']);
            unset( $Db_Singleinformation['sourcepic']);

            $jsondata=$Db_Singleinformation;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $titel."{$this->tablenamestr}审核详情");
    }




    /**
     *    个人中心： 信息管理列表
     */
    public function managementmore()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display', 'usertoken','groupid'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj = new Common();
        $UserObj = new User();
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

        $titel='';
        if(($groupid==1 || $typeid==1) || ($groupid==4 || $typeid==4)){

            /*商家和个人：对于单页信息的管理具有相同的功能*/

            $where = " isdel=0 and user_id='{$user_id}'";
            if($groupid==1 || $typeid==1){
                $titel='商家';
            }elseif($groupid==4 || $typeid==4){
                $titel='普通会员';
            }
        }elseif($groupid==2 || $typeid==2){

            /*代理商：只能查看自己区域内的单页信息*/
            $where = "  level3='{$Db_User['level3']}' ";
            $titel='代理商';
        }elseif($groupid==3 || $typeid==3){

            /*合伙人：合伙人角色不具备该功能*/
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000023, '合伙人角色不具备该功能！');

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


        $fileds = ' id AS singleinformation_id, name, buddhastatus, is_sure, number, brief, shop_id, state ';
        if ($b_display == 1) {

            $fileds .= ' , singleinformation_img AS img ';
        } elseif ($b_display == 2) {

            $fileds .= ' , singleinformation_thumb AS  img ';
        }

        $orderby = " ORDER BY add_time DESC ";


        $sql = " SELECT  {$fileds}  
                FROM {$this->prefix}singleinformation WHERE {$where} 
                {$orderby}  " . Buddha_Tool_Page::sqlLimit($page, $pagesize);


        $Db_Singleinformation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $jsondata = array();

        if (Buddha_Atom_Array::isValidArray($Db_Singleinformation)) {

            $jsondata['nav'] = $ShopObj->getPersonalCenterHeaderMenu('singleinformation.singleinformationmenmanagemore',$isShowStop);
            $jsondata['ser'] = array(
                'Services'=>'singleinformation.singleinformationmenmanageview',
                'param'=>array(
                    'groupid'=>$groupid,
                    'typeid'=>$typeid,
                ),
            );


            foreach ($Db_Singleinformation as $k => $v) {


                if ($v['shop_id'] != 0) {
                    $shop_name = $ShopObj->getSingleFiledValues(array('name'), "id='{$v['shop_id']}' AND user_id='{$v['user_id']}'");
                    $Db_Singleinformation[$k]['shop_name']  =  $shop_name['name'];
                } else {
                    $shop_name = $UserObj->getSingleFiledValues(array('name'), "id='{$v['user_id']}'");
                    $Db_Singleinformation[$k]['shop_name']   =  $shop_name['name'];
                }

                if ($v['is_verify'] == 1) {
                    $Db_Singleinformation[$k]['api_authenticatestateimg'] = $host . 'apistate/menuplus/yirenzheng.png';
                } else {
                    $Db_Singleinformation[$k]['api_authenticatestateimg'] = $host . 'apistate/menuplus/weirenzheng.png';
                }

                if ($v['is_sure'] == 0) {

                    $Db_Singleinformation[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/weishenhe.png';

                } elseif ($v['is_sure'] == 4) {

                    $Db_Singleinformation[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/weitonguo.png';

                } elseif ($v['is_sure'] == 1) {

                    $Db_Singleinformation[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/yitonguo.png';

                }


                if (Buddha_Atom_String::isValidString($v['img'])) {

                    $Db_Singleinformation[$k]['api_img'] = $host . $v['img'];

                } else {

                    $Db_Singleinformation[$k]['api_img'] = '';
                }

                if ($v['buddhastatus'] == 1) {

                    $Db_Singleinformation[$k]['api_buddhastatus'] = '上 架';

                } else if ($v['buddhastatus'] == 0) {

                    $Db_Singleinformation[$k]['api_buddhastatus'] = '下 架';

                }

                if ($v['state'] == 1) {

                    $Db_Singleinformation[$k]['api_buddhastatus'] = '停 用';

                } else if ($v['state'] == 0) {

                    $Db_Singleinformation[$k]['api_buddhastatus'] = '启 用';

                }


                if (!Buddha_Atom_String::isValidString($v['shop_name'])) {

                    $Db_Singleinformation[$k]['shop_name'] = $ShopObj->getShopnameFromShopid($v['shop_id']);

                }
                unset($Db_Singleinformation[$k]['img']);
                unset($Db_Singleinformation[$k]['level3']);
            }


            $tablewhere = $this->prefix . 'singleinformation';

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);


            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];
            $jsondata['list'] = $Db_Singleinformation;


        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $titel."{$this->tablenamestr}管理列表");



    }


    /**
     *  个人中心: 商家 单页信息列表
     */

     public function businesmenmanagesingleinformationmore()
     {

         $host = Buddha::$buddha_array['host'];

         if (Buddha_Http_Input::checkParameter(array('b_display', 'usertoken'))) {
             Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
         }
         $CommonObj = new Common();
         $UserObj = new User();
         $ShopObj = new Shop();
         $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;

         $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

         $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3');
         $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
         $user_id = $Db_User['id'];

         if (!$UserObj->isHasMerchantPrivilege($user_id)) {
             Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请该角色！');
         }

         $api_keyword = Buddha_Http_Input::getParameter('api_keyword') ? Buddha_Http_Input::getParameter('api_keyword') : '';


         $b_display = (int)Buddha_Http_Input::getParameter('b_display') ? Buddha_Http_Input::getParameter('b_display') : 2;

         $page = Buddha_Http_Input::getParameter('page') ? Buddha_Http_Input::getParameter('page') : 1;
         $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
         $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

         $view = Buddha_Http_Input::getParameter('view') ? Buddha_Http_Input::getParameter('view') : 0;

         $shop_id = Buddha_Http_Input::getParameter('shop_id') ? Buddha_Http_Input::getParameter('shop_id') : 0;


         $where = " isdel=0 and user_id='{$user_id}'";

         if (Buddha_Atom_String::isValidString($api_keyword)) {

             $where .= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword, array('name', 'number'));

         }

        if($shop_id>0){
            $where .=" AND  shop_id='{$shop_id}'";
        }


         if ($view) {
             switch ($view) {
                 case 2;
                     $where .= ' and is_sure=0 ';
                     break;
                 case 3;
                     $where .= ' and is_sure=1 ';
                     break;
                 case 4;
                     $where .= ' and is_sure=4 ';
                     break;
             }
         }

         $fileds = ' id AS singleinformation_id, name, buddhastatus,is_sure, number, brief ,shop_id ';

         if ($b_display == 1) {
             $fileds .= ' , singleinformation_img AS img ';
         } elseif ($b_display == 2) {
             $fileds .= ' , singleinformation_thumb AS img ';
         }

         $orderby = " ORDER BY add_time DESC ";


         $sql = " SELECT  {$fileds}  
                FROM {$this->prefix}singleinformation WHERE {$where} 
                {$orderby}  " . Buddha_Tool_Page::sqlLimit($page, $pagesize);


         $Db_Singleinformation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


         $jsondata = array();

         if (Buddha_Atom_Array::isValidArray($Db_Singleinformation)) {


             $jsondata['nav'] = $ShopObj->getPersonalCenterHeaderMenu('singleinformation.businesmenmanagesingleinformationmore',0);
             foreach ($Db_Singleinformation as $k => $v) {


                 if ($v['shop_id'] != 0) {
                     $shop_name = $ShopObj->getSingleFiledValues(array('name'), "id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                     $Db_Singleinformation[$k]['shop_name']  =  $shop_name['name'];
                 } else {
                     $shop_name = $UserObj->getSingleFiledValues(array('name'), "id='{$v['user_id']}'");
                     $Db_Singleinformation[$k]['shop_name']   =  $shop_name['name'];
                 }
                 if ($v['is_sure'] == 0) {

                     $Db_Singleinformation[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/weishenhe.png';

                 } elseif ($v['is_sure'] == 4) {

                     $Db_Singleinformation[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/weitonguo.png';

                 } elseif ($v['is_sure'] == 1) {

                     $Db_Singleinformation[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/yitonguo.png';

                 }


                 if (Buddha_Atom_String::isValidString($v['img'])) {

                     $Db_Singleinformation[$k]['api_img'] = $host . $v['img'];

                 } else {

                     $Db_Singleinformation[$k]['api_img'] = '';
                 }

                 if ($v['buddhastatus'] == 1) {

                     $Db_Singleinformation[$k]['api_buddhastatus'] = '上 架';

                 } else if ($v['buddhastatus'] == 0) {

                     $Db_Singleinformation[$k]['api_buddhastatus'] = '下 架';

                 }

                 if ($v['state'] == 1) {

                     $Db_Singleinformation[$k]['api_buddhastatus'] = '停 用';

                 } else if ($v['state'] == 0) {

                     $Db_Singleinformation[$k]['api_buddhastatus'] = '启 用';

                 }


                 if (!Buddha_Atom_String::isValidString($v['shop_name'])) {

                     $Db_Singleinformation[$k]['shop_name'] = $ShopObj->getShopnameFromShopid($v['shop_id']);

                 }

                 $Db_Singleinformation[$k]['top']=array(
                     'Services'=>'payment.infotop',
                     'param'=>array(
                         'singleinformation_id'=>$v['singleinformation_id'],
                         'good_table'=>'singleinformation',
                         ),
                 );

                 $Db_Singleinformation[$k]['update']=array(
                     'Services'=>'singleinformation.beforeupdate',
                     'param'=>array('singleinformation_id'=>$v['singleinformation_id']),
                 );
                 $Db_Singleinformation[$k]['del']=array(
                     'Services'=>'singleinformation.del',
                     'param'=>array('singleinformation_id'=>$v['singleinformation_id']),
                 );


                 unset($Db_Singleinformation[$k]['img']);
                 unset($Db_Singleinformation[$k]['level3']);
             }


             $tablewhere = $this->prefix . 'singleinformation';

             $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);


             $jsondata['page'] = $temp_Common['page'];
             $jsondata['pagesize'] = $temp_Common['pagesize'];
             $jsondata['totalrecord'] = $temp_Common['totalrecord'];
             $jsondata['totalpage'] = $temp_Common['totalpage'];
             $jsondata['list'] = $Db_Singleinformation;

             $jsondata['add']=array(
                 'Services'=>'singleinformation.beforeadd',
                 'param'=>array(),
             );

         }

         Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "商家{$this->tablenamestr}列表");

     }




    /**
     * 代理商：单页信息详情（）
     */
    public function agentmanagesingleinformationview()
    {
        header("Content-Type: text/html; charset=utf8 ");
        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken','singleinformation_id'))) {
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
        $api_singleinformationid = (int)Buddha_Http_Input::getParameter('singleinformation_id')?Buddha_Http_Input::getParameter('singleinformation_id'):0;

        if(!$UserObj->isHasAgentPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商角色！');
        }

        if(!$CommonObj->isOwnerBelongToAgentByLeve3($this->tablename,$api_singleinformationid,$Db_User['level3'])){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;


        /*代理商只能查看未审核的详情*/
        $where=" id='{$api_singleinformationid}' ";

        $filed='id singleinformation_id,shop_id,shop_name,name,click_count,number,keywords,brief,`desc`,state,is_remote,add_time,buddhastatus,is_sure,level1,level2,level3';

        if($b_display==2){
            $filed.=',singleinformation_img as api_img';

        }elseif($b_display==1){
            $filed.=',singleinformation_thumb as api_img';

        }

        $sql =" SELECT  {$filed}
                FROM {$this->prefix}singleinformation WHERE {$where} ";
        $Db_Singleinformation_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata['list'] =array();

        if(Buddha_Atom_Array::isValidArray($Db_Singleinformation_arr)){

            $Db_Singleinformation=  $Db_Singleinformation_arr[0];

            if($Db_Singleinformation['shop_id']!=0){
                $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$Db_Singleinformation['shop_id']}' and user_id='{$Db_Singleinformation['user_id']}'");
                $name='商家：'.$shop_name['name'];
            }else{
                $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$Db_Singleinformation['user_id']}'");
                $name='个人：'.$shop_name['name'];
            }


            if(!Buddha_Atom_String::isValidString($Db_Singleinformation['keywords'])){
                $Db_Singleinformation['keywords']='';
            }

            if($Db_Singleinformation['is_verify']==1){
                $Db_Singleinformation['api_authenticatestateimg']=$host.'apistate/menuplus/yirenzheng.png';

            }else{
                $Db_Singleinformation['api_authenticatestateimg']=$host.'apistate/menuplus/weirenzheng.png';

            }

            if($Db_Singleinformation['is_sure']==0){

                $Db_Singleinformation['api_auditstateimg']=$host.'apistate/menuplus/weishenhe.png';
                /*是否显示审核模块：0否；1是*/
                $Db_Singleinformation['api_isshowaudit']=1;

            }elseif($Db_Singleinformation['is_sure']==4){

                $Db_Singleinformation['api_auditstateimg']=$host.'apistate/menuplus/weitonguo.png';
                $Db_Singleinformation['api_isshowaudit']=0;
            }elseif($Db_Singleinformation['is_sure']==1){

                $Db_Singleinformation['api_auditstateimg']=$host.'apistate/menuplus/yitonguo.png';
                $Db_Singleinformation['api_isshowaudit']=0;
            }


            if($Db_Singleinformation['buddhastatus']==1){

                $Db_Singleinformation['api_buddhastatus']='上 架';

            }else if($Db_Singleinformation['buddhastatus']==0){

                $Db_Singleinformation['api_buddhastatus']='下 架';

            }

            if($Db_Singleinformation['state']==1){

                $Db_Singleinformation['api_buddhastatus']='停 用';

            }else if($Db_Singleinformation['state']==0){

                $Db_Singleinformation['api_buddhastatus']='启 用';

            }
            if(Buddha_Atom_String::isValidString($Db_Singleinformation['api_img'])){
                $Db_Singleinformation['api_img']=$host.$Db_Singleinformation['api_img'];
            }else{
                $Db_Singleinformation['api_img']='';
            }

            if(!Buddha_Atom_String::isValidString($Db_Singleinformation['shop_name'])){

                $Db_Singleinformation['shop_name']=$ShopObj->getShopnameFromShopid($Db_Singleinformation['shop_id']);

            }
            $Db_Singleinformation['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Singleinformation['desc']);

            $jsondata=$Db_Singleinformation;

        }
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商{$this->tablenamestr}审核详情");
    }



    /**
     *
     * @author csh
     *  代理商：单页信息管理列表
     */

    public function agentmanagesingleinformationviewmore()
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
        $fileds = ' id AS singleinformation_id, is_sure,name, shop_id,shop_name, buddhastatus, brief, state ,level3 ';

        if($b_display==1){
            $fileds.=' , singleinformation_img AS img ';
        }elseif($b_display==2){
            $fileds.=' , singleinformation_thumb AS  img ';
        }

        $orderby = " ORDER BY add_time DESC ";


        $sql =" SELECT  {$fileds}  
                FROM {$this->prefix}singleinformation WHERE {$where} 
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $Db_Singleinformation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();

        $jsondata['nav'] = $ShopObj->getPersonalCenterHeaderMenu('singleinformation.agentmanagesingleinformationmore');
        $jsondata['page'] =  0;
        $jsondata['pagesize'] =  0;
        $jsondata['totalrecord'] =  0;
        $jsondata['totalpage'] =  0;
        $jsondata['list'] = array();

        if(Buddha_Atom_Array::isValidArray($Db_Singleinformation)){

            foreach($Db_Singleinformation as $k=>$v){

                if($v['shop_id']!=0){
                    $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                    $name='商家：'.$shop_name['name'];
                }else{
                    $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                    $name='个人：'.$shop_name['name'];
                }

                if($v['is_verify']==1){
                    $Db_Singleinformation[$k]['api_authenticatestateimg']=$host.'apistate/menuplus/yirenzheng.png';
                }else{
                    $Db_Singleinformation[$k]['api_authenticatestateimg']=$host.'apistate/menuplus/weirenzheng.png';
                }

                if($v['is_sure']==0){

                    $Db_Singleinformation[$k]['api_auditstateimg']=$host.'apistate/menuplus/weishenhe.png';

                    /*单页信息：审核状态（只有未审核的单页信息才显示）*/
                    $Db_Singleinformation[$k]['issureServices']=array(
                        'Services' => 'singleinformation.beforeverify',
                        'param'=> array('singleinformation_id'=>$v['singleinformation_id'])
                    );

                }elseif($v['is_sure']==4){

                    $Db_Singleinformation[$k]['api_auditstateimg']=$host.'apistate/menuplus/weitonguo.png';

                }elseif($v['is_sure']==1){

                    $Db_Singleinformation[$k]['api_auditstateimg']=$host.'apistate/menuplus/yitonguo.png';


                    /*单页信息：上下架（只有正常的单页信息才显示）*/
                    $Db_Singleinformation[$k]['buddhastatusServices']=array(
                        'Services' => 'singleinformation.offshelf',
                        'param'=> array('shelf'=>$v['buddhastatus'],'singleinformation_id'=>$v['singleinformation_id'])
                    );

                    if($v['buddhastatus']==1){

                        $Db_Singleinformation[$k]['api_buddhastatus']='上 架';

                    }else if($v['buddhastatus']==0){

                        $Db_Singleinformation[$k]['api_buddhastatus']='下 架';
                    }
                }

                if(Buddha_Atom_String::isValidString($v['img'])){

                    $Db_Singleinformation[$k]['api_img']=$host.$v['img'];

                }else{

                    $Db_Singleinformation[$k]['api_img']='';
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

                    $Db_Singleinformation[$k]['shop_name']=$ShopObj->getShopnameFromShopid($v['shop_id']);

                }
                unset( $Db_Singleinformation[$k]['img']);
                unset( $Db_Singleinformation[$k]['level3']);

                $Db_Singleinformation[$k]['view']=array(
                    "Services"=> "singleinformation.verify",
                    "param"=>array('singleinformation_id'=>$v['singleinformation_id']),
                );
            }


            $tablewhere = $this->prefix.'singleinformation';

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $jsondata['page'] =  $temp_Common['page'];
            $jsondata['pagesize'] =  $temp_Common['pagesize'];
            $jsondata['totalrecord'] =  $temp_Common['totalrecord'];
            $jsondata['totalpage'] =  $temp_Common['totalpage'];
            $jsondata['list'] = $Db_Singleinformation;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商{$this->tablenamestr}管理列表");

    }


    /**
     *
     * @author csh
     * 单页信息列表
     */
    public function more()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('api_number','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        /*说明： 如果存在usertoken 表示是从个人中心请求数据：即只请求自己名下的所有单页信息*/

        $usertoken =  Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';


        if(Buddha_Atom_String::isValidString($usertoken)){
            $UserObj=new User();
            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
            $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
            $user_id = $Db_User['id'];
        }

        /*如果存在usertoken表示是从个人中心请求数据(要加入对应的条件和显示相应的字段)  则不需要*/

        $RegionObj = new Region();
        $CommonObj = new Common();

        /*城市编号*/
        $api_number = (int)Buddha_Http_Input::getParameter('api_number');
        $b_display = (int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $shop_id = (int) Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;

        $api_keyword = Buddha_Http_Input::getParameter('api_keyword');
        $page = (int)Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page'):1;


        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):15;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

//        /*是否按照附近显示*/
//        $api_isnearby = Buddha_Http_Input::getParameter('api_isnearby')?Buddha_Http_Input::getParameter('api_isnearby'):0;
//
//        /* 按照附近显示的距离默认为1km(如果api_isnearby==1时)*/
//        $api_nearbydistance = Buddha_Http_Input::getParameter('api_nearbydistance')?Buddha_Http_Input::getParameter('api_nearbydistance'):1;
//        $lats = (int)Buddha_Http_Input::getParameter('lat')?Buddha_Http_Input::getParameter('lat'):0;
//        $lngs = (int)Buddha_Http_Input::getParameter('lng')?Buddha_Http_Input::getParameter('lng'):0;


        $fields = 'id AS singleinformation_id, shop_id, name, brief,number,add_time,click_count ';


        if($b_display == 1){
            $fields.=' , singleinformation_img AS img ';
        }elseif($b_display == 2){
            $fields.=' , singleinformation_thumb AS img ';
        }

        $where=' isdel=0 AND state=0 AND buddhastatus=0 AND is_sure=1';

        if($shop_id){
            $where.=" AND shop_id='{$shop_id}' ";
        }

            /*在条件中加入地区*/
        $where .= $RegionObj->whereJoinRegion($api_number);
// 屏蔽原因：单页信息列表中没有经纬度 无法加入附近显示
//        if($api_isnearby==1){
//            /*在条件中加入附近显示*/
//            $where.= $RegionObj->whereJoinNearby($api_nearbydistance,$lats,$lngs,$api_number);
//        }

        $orderby = " ORDER BY toptime,add_time DESC ";

        $where .= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword,'name');

        if(Buddha_Atom_String::isValidString($usertoken)){
            $where.=" AND user_id='{$user_id}' ";
        }


        $sql = " SELECT {$fields} FROM {$this->prefix}singleinformation  WHERE {$where} {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );


        $Db_Singleinformation = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach($Db_Singleinformation as $k=>$v){
            $Db_Singleinformation[$k]['api_addtime'] =$CommonObj->getDateStrOfTime($v['add_time'],0,0,0);;
            $Db_Singleinformation[$k]['img'] = $host.$v['img'];
            $Db_Singleinformation[$k]['icon_shop'] = $host."style/images/shopgray.png";
            $Db_Singleinformation[$k]['services'] = 'singleinformation.view';
            $Db_Singleinformation[$k]['param'] = array('singleinformation_id'=>$v['singleinformation_id']);
        }

        $jsondata = array();
        $jsondata['list']= array();
        $jsondata['page'] = 0;
        $jsondata['pagesize'] = 0;
        $jsondata['totalrecord'] = 0;
        $jsondata['totalpage'] = 0;

//        $jsondata = $CommonObj->ListFinalReturnValueArray($Db_Shop,$tablewhere, $where, $pagesize, $page);

    if(Buddha_Atom_Array::isValidArray($Db_Singleinformation))
    {
        $jsondata['list'] = $Db_Singleinformation;

        $tablewhere = $this->prefix . 'singleinformation';
        $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);
        $jsondata['page'] = $temp_Common['page'];
        $jsondata['pagesize'] = $temp_Common['pagesize'];
        $jsondata['totalrecord'] = $temp_Common['totalrecord'];
        $jsondata['totalpage'] = $temp_Common['totalpage'];
    }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'列表');
    }


    /**
     * 代理商：单页信息审核之前必须请求详情页面
     */

    public function beforeverify()
    {
        $host= Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('singleinformation_id','usertoken','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $SingleinformationObj = new Singleinformation();
        $ShopObj = new Shop();
        $CommonObj = new Common();

        $singleinformation_id =  Buddha_Http_Input::getParameter('singleinformation_id');
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

        if($SingleinformationObj->isOwnerBelongToAgentByLeve3($singleinformation_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000021, '此单页信息不属于当前的代理商管理');
        }

        if($CommonObj->isIssureByTableid($singleinformation_id,'singleinformation')){
                    Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 50000002	, '已经审核过了，请不要重复审核！');
        }

        $singleinformation_id = Buddha_Http_Input::getParameter('singleinformation_id');

        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $shop_id =(int) Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;


        $fields = ' id AS singleinformation_id,add_time,click_count, shop_id, name, brief,`desc` ';

        if($b_display==1){

            $fields.=' , singleinformation_img AS img ';

        }elseif($b_display==2){

            $fields.=' , singleinformation_thumb AS img ';
        }

        $where=" id ='{$singleinformation_id}' ";

        if($shop_id>0){

            $where.=" AND shop_id='{$shop_id}' ";
        }

        $sql =" SELECT {$fields} FROM {$this->prefix}singleinformation  WHERE {$where} ";



        $Db_Singleinformation_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_Singleinformation_arr)){

            $Db_Singleinformation=$Db_Singleinformation_arr[0];
            if($Db_Singleinformation['img']){
                $Db_Singleinformation['img']=$host.$Db_Singleinformation['img'];
            }else{
                $Db_Singleinformation['img'] = '';
            }

            $Db_Singleinformation['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Singleinformation['desc']);
            $Db_Singleinformation['shop_name']=$ShopObj->getShopnameFromShopid($Db_Singleinformation['shop_id']);
            $Db_Singleinformation['shop_img']=$host.$ShopObj->getShopImgFromShopid($Db_Singleinformation['shop_id'],$b_display);
            /*单页信息：审核*/
            $Db_Singleinformation['issureServices']=array(
                'Services' => 'singleinformation.verify',
                'param'=> array('is_sure'=>$Db_Singleinformation['is_sure'],'singleinformation_id'=>$Db_Singleinformation['singleinformation_id'])
            );


            $jsondata['list'] = $Db_Singleinformation;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商进行{$this->tablenamestr}之前必须请求的详情页面");

    }


    /**
     * 代理商：单页信息审核
     */
    public function verify(){

        if (Buddha_Http_Input::checkParameter(array('singleinformation_id','usertoken','is_sure'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $SingleinformationObj = new Singleinformation();
        $CommonObj = new Common();

        $singleinformation_id =  (int)Buddha_Http_Input::getParameter('singleinformation_id');

        /*审核状态：1通过审核  ；4未通过审核*/
        $is_sure = (int) Buddha_Http_Input::getParameter('is_sure')?(int) Buddha_Http_Input::getParameter('is_sure'):0;
        /*判断$is_sure审核状态码 是否属于 1,4*/
        if(!$CommonObj->isIdInDataEffectiveById($is_sure,array(1,4))){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }
        $remarks = Buddha_Http_Input::getParameter('remarks')? Buddha_Http_Input::getParameter('remarks'):'';
        /*4未通过审核 必须填写备注*/
        if($is_sure==4 AND !Buddha_Atom_String::isValidString($remarks))
        {
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

        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$SingleinformationObj->isOwnerBelongToAgentByLeve3($singleinformation_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $data = array();
        $data['is_sure'] =$is_sure ;
        $data['remarks'] =$remarks ;
        $Db_Singleinformation_num= $SingleinformationObj->edit($data,$singleinformation_id);
//        $tablenamestr='单页信息';
        if($Db_Singleinformation_num){
            $datas['is_ok']=1;
            $datas['is_msg']=$this->tablenamestr.'审核成功！';
        }else{
            $datas['is_ok']=0;
            $datas['is_msg']=$this->tablenamestr.'审核失败！';
        }
        $jsondata = array();
        $jsondata['singleinformation_id'] = $singleinformation_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'审核');


    }

    /**
     * 代理商：单页信息上下架状态
     */

    public function offshelf(){

        if (Buddha_Http_Input::checkParameter(array('singleinformation_id','usertoken','shelf'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj=new User();
        $SingleinformationObj = new Singleinformation();
        $CommonObj = new Common();
        $UserommonObj = new Userommon();

        $singleinformation_id =  Buddha_Http_Input::getParameter('singleinformation_id');

        /*默认下架  0下架 1=上架*/
        $shelf = (int)Buddha_Http_Input::getParameter('shelf') ? (int)Buddha_Http_Input::getParameter('shelf') : 0;
        if(!$CommonObj->isIdInDataEffectiveById($shelf)){
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

        if($UserObj->isHasAgentPrivilege($user_id)==0){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$SingleinformationObj->isOwnerBelongToAgentByLeve3($singleinformation_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $data = array();
        $msg = "";

        if($shelf==0)
        {
            $data['buddhastatus'] =1 ;
            $msg="下架";

        }else{

            $data['buddhastatus'] =0 ;
            $msg="上架";
        }

        $SingleinformationObj->edit($data,$singleinformation_id);


        $jsondata = array();
        $jsondata['singleinformation_id'] = $singleinformation_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['msg'] = $msg;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.$msg);


    }


    /**
     * @view 单页信息详情
     */
    public function view()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('singleinformation_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

//        $UserObj=new User();
        $ShopObj=new Shop();
        $SingleinformationObj=new Singleinformation();
        $CommonObj=new Common();

        /*说明： 如果存在usertoken并正确;如果存在usertoken 表示是从个人中心请求数据：即只请求自己名下的所有单页信息(包括可以查看自己当前单页信息下所有的报名者留言和电话信息)*/
        $usertoken =  Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';


        $soure =  Buddha_Http_Input::getParameter('soure')?Buddha_Http_Input::getParameter('soure'):0;

//
//        if($soure==1){
//
//            if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
//                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
//            }
//            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
//
//            $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
//            $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
//            $user_id = $Db_User['id'];
//
//            if(!$UserObj->isHasMerchantPrivilege($user_id) ){
//                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色！');
//            }else{
//                $isDisplayRegistrationDetails=1;
//            }
//        }




        $singleinformation_id = Buddha_Http_Input::getParameter('singleinformation_id');

        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $shop_id =(int) Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;


        $fields = ' id AS singleinformation_id,add_time,click_count, shop_id, name, brief,`desc` ';

        if($b_display==1){

            $fields.=' , singleinformation_large AS img ';

        }elseif($b_display==2){

            $fields.=' , singleinformation_img AS img ';
        }

        $where=" id ='{$singleinformation_id}' ";

        if($shop_id>0){

            $where.=" AND shop_id='{$shop_id}' ";
        }

        $sql =" SELECT {$fields} FROM {$this->prefix}singleinformation WHERE {$where} ";


        $Db_Singleinformation_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_Singleinformation_arr)){

            $Db_Singleinformation = $Db_Singleinformation_arr[0];

            /*更新点击量*/
            $Singleinformation_data['click_count']=$Db_Singleinformation_arr['click_count']+1;
            $SingleinformationObj->edit($Singleinformation_data,$singleinformation_id);

            if($Db_Singleinformation['img']){
                $Db_Singleinformation['img']=$host.$Db_Singleinformation['img'];
            }else{
                $Db_Singleinformation['img'] = '';
            }
            $Db_Singleinformation['api_addtime'] =$CommonObj->getDateStrOfTime($Db_Singleinformation['add_time'],0,0,0);;

            $Db_Singleinformation['desc'] = Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Singleinformation['desc']);
            $Db_Singleinformation['shop_name'] = $ShopObj->getShopnameFromShopid($Db_Singleinformation['shop_id']);
            $Db_Singleinformation['shop_img'] = $host.$ShopObj->getShopImgFromShopid($Db_Singleinformation['shop_id'],$b_display);
            $Db_Singleinformation['shop'] = array(
                'Services'=>'shop.view',
                'param'=>array('shop_id'=>$Db_Singleinformation['shop_id']),
            );

            $jsondata['isshowcellphone'] = array(
                'services' =>'shop.isshowcellphone',
                'param' => array('shop_id'=>$Db_Singleinformation['shop_id']),
            );
            $jsondata=$Db_Singleinformation;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'详情');
    }


    /**
     *   商家：单页信息 添加之前
     */

    public function beforeadd()
    {

        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

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
        /*正常店铺列表*/
        $jsondata['shoplist'] = $ShopObj->getShoparrByUserid($user_id);

        $jsondata['region']['Services'] = 'ajaxregion.getBelongFromFatherId';
        $jsondata['region']['param'] = array('father '=>1);

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'添加之前的操作接口');
    }



    /**
     *   商家：单页信息 添加
     */

    public function add()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','name','image_arr','shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];



        $SingleinformationObj=new Singleinformation();
        $ShopObj=new Shop();
        $CommonObj=new Common();
        $RegionObj=new Region();
        $MoregalleryObj=new Moregallery();
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



        $shop_id=Buddha_Http_Input::getParameter('shop_id');

        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');

        /*异地发布:0是 1否*/
        $is_remote=(int)Buddha_Http_Input::getParameter('is_remote');


        /*判断 $is_remote 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($is_remote)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }


        $image_arr=Buddha_Http_Input::getParameter('image_arr');



        /*判断图片数组是否是Json*/
        if(Buddha_Atom_String::isJson($image_arr)){
            $image_arr = json_decode($image_arr);
        }




        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);



        if($image_arr== 0 or $image_arr=='' or count($image_arr)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000047, '相册不能为空！');

        }


        if(!$ShopObj->isShopByShopid($shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000006, '店铺内码ID无效！');
        }




        /*只有在选择了异地发布的时候才验证地区*/
        if($is_remote==1){
            if(!$RegionObj->isProvince($level1)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
            }

            if(!$RegionObj->isCity($level2)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
            }

            if(!$RegionObj->isArea($level3)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
            }

        }


        /*信息的名称*/
        $name=Buddha_Http_Input::getParameter('name');


        /*是否上架:0为是  1为否*/
        $buddhastatus=(int)Buddha_Http_Input::getParameter('buddhastatus');
        /*判断 $buddhastatus 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($buddhastatus)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }


        /*简述*/
        $brief=Buddha_Http_Input::getParameter('brief');

        /*详情*/
        $desc=Buddha_Http_Input::getParameter('desc');


        $datas = array();
        $datas['user_id'] = $user_id;
        $datas['name'] = $name;
        $datas['shop_id'] = $shop_id;
        $datas['shop_name'] = $ShopObj->getShopNameByShopid($shop_id,$user_id);
        $datas['buddhastatus'] = $buddhastatus;
        $datas['is_remote'] = $is_remote;
        $datas['number']=$CommonObj->GeneratingNumber();//单页编号




        /*只有在选择了异地发布的时候才验证地区*/
        if($is_remote==1) {
            $datas['level0'] = 1;
            $datas['level1'] = $level1;
            $datas['level2'] = $level2;
            $datas['level3'] = $level3;
        }else{
           $Db_shop= $ShopObj->getShopareaByShopid($shop_id,$user_id);
            $datas['level0'] = $Db_shop['level0'];
            $datas['level1'] = $Db_shop['level1'];
            $datas['level2'] = $Db_shop['level2'];
            $datas['level3'] = $Db_shop['level3'];
        }
        $datas['number']=$CommonObj->GeneratingNumber();//单页信息编号;
        $datas['brief'] = $brief;
        $datas['add_time'] = Buddha::$buddha_array['buddha_timestamp'];


        $singleinformation_id=$SingleinformationObj->add($datas);
        if(!$singleinformation_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000020, '单页信息添加失败！');
        }



        if(Buddha_Atom_Array::isValidArray($image_arr)){
            $savePath="storage/{$this->tablename}/{$singleinformation_id}/";
            if(!file_exists(PATH_ROOT.$savePath)){
                mkdir(PATH_ROOT.$savePath, 0777);
            }


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

            if(Buddha_Atom_Array::isValidArray($MoreImage)){
                $uploadfield='image_arr';

                $MoregalleryObj->addImageArrToMoregallery($MoreImage,$singleinformation_id,$savePath,$shop_id,$uploadfield,$this->tablename,$user_id);

                $SingleinformationObj->setFirstGalleryImgToShop($singleinformation_id);

            }

        }

        /*富文本编辑器图片处理*/
        if($desc){
            $MoregalleryObj=new Moregallery();
            $field='desc';
            $saveData_desc = $MoregalleryObj->base_upload($desc,$singleinformation_id,$this->tablename,$field);
            $saveData_desc = str_replace(PATH_ROOT,'/', $saveData_desc);
            $details['desc'] = $saveData_desc;
            $SingleinformationObj->edit($details,$singleinformation_id);
        }


        $param = array();


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
        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] =$this->tablenamestr.'添加成功';
        $jsondata['usertoken'] = $usertoken;
        $jsondata['singleinformation_id'] = $singleinformation_id;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'添加');
    }



    /**
     * 商家：单页信息更新之前必须请求的信息
     */
    public function beforeupdate()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','singleinformation_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $SingleinformationObj=new Singleinformation();
        $UserObj=new User();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $CommonObj=new Common();
        $AlbumObj=new Album();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $singleinformation_id = (int)Buddha_Http_Input::getParameter('singleinformation_id')?Buddha_Http_Input::getParameter('singleinformation_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        if(!$SingleinformationObj->getShopidIsVerify($singleinformation_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000022, $this->tablenamestr.'内码ID无效！');
        }
        if(!$ShopObj->IsUserHasShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000042, '你还未创建店铺，快去创建吧！');
        }

        if(!$ShopObj->IsUserHasNormalShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000043, '你名下的所有店铺异常，请处理后一个(要添加到哪个店铺下)或全部店铺后再添加！');
        }
        $fields = 'id AS singleinformation_id,shop_id,name,brief,
                    `desc`, state, is_remote, buddhastatus, 
                    level0, level1, level2, level3 ';

        if($b_display==1)
        {
            $fields.=' ,singleinformation_img AS img ';
        }elseif($b_display==2){
            $fields.=' , singleinformation_thumb AS img ';
        }

        /*isdel=0 表示正常；isdel=5 表示选择了店铺认证单未支付的店铺*/

        $where=" id='{$singleinformation_id}' AND user_id='$user_id' AND (isdel=0 or isdel=5)";


        $sql ="select {$fields} FROM {$this->prefix}singleinformation WHERE {$where} ";
        $Db_Singleinformation_array = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);





        $jsondata = array();

        if(Buddha_Atom_Array::isValidArray($Db_Singleinformation_array)){

            $Db_Singleinformation = $Db_Singleinformation_array[0];

            /**↓↓↓↓↓↓↓↓↓↓↓ 相册 ↓↓↓↓↓↓↓↓↓↓↓**/
            $Db_Singleinformation['img'] = $host.$Db_Singleinformation['img'];
            $Db_Singleinformation['imgmore'] = array();

            $MoregalleryFiled = array('id as moregallery_id');
            if($b_display == 1)
            {
                array_push($MoregalleryFiled,'goods_img as img');
            }else if($b_display == 2){
                array_push($MoregalleryFiled,'goods_thumb as img');
            }
            $MoregalleryObj = new Moregallery();
            $Db_Moregallery = $MoregalleryObj->getFiledValues($MoregalleryFiled,
                "(user_id='{$user_id}' or user_id=0) AND goods_id='{$singleinformation_id}' AND tablename='{$this->tablename}' AND webfield='file'");

            if(Buddha_Atom_Array::isValidArray($Db_Moregallery))
            {
                foreach ($Db_Moregallery as $k=>$v)
                {
                    $Db_Moregallery[$k]['img'] =$host.$v['img'];
                    $Db_Moregallery[$k]['Services'] = 'moregallery.deleteimage';
                    $Db_Moregallery[$k]['param'] = array('moregallery_id'=>$v['moregallery_id'],'table_name'=>'moregallery');
                }
            }
            $Db_Singleinformation['imgmore'] = $Db_Moregallery;
            /**↑↑↑↑↑↑↑↑↑↑ 相册 ↑↑↑↑↑↑↑↑↑↑**/

            $Db_Singleinformation['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Singleinformation['desc']);
            /*地区*/
            $Db_Singleinformation['api_area']=$RegionObj->getDetailOfAdrressByRegionIdStr($Db_Singleinformation['level1'],$Db_Singleinformation['level2'],$Db_Singleinformation['level3'],$Spacer='>');
            $jsondata=$Db_Singleinformation;
            /*正常店铺列表*/
            $jsondata['shoplist'] = $ShopObj->getShoparrByUserid($user_id,$Db_Singleinformation['shop_id']);

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'编辑之前的操作接口');
    }




    /**
     *  商家： 单页信息编辑
     */

    public function update()
    {

        if (Buddha_Http_Input::checkParameter(array('usertoken','singleinformation_id','shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj=new User();
        $SingleinformationObj=new Singleinformation();
        $ShopObj=new Shop();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $singleinformation_id=Buddha_Http_Input::getParameter('singleinformation_id');

        if(!$SingleinformationObj->getShopidIsVerify($singleinformation_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000022, '单页信息内码ID无效！');
        }

        if(!$ShopObj->IsUserHasShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000042, '你还未创建店铺，快去创建吧！');
        }

        if(!$ShopObj->IsUserHasNormalShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000043, '你名下的所有店铺异常，请处理后一个(要添加到哪个店铺下)或全部店铺后再添加！');
        }
        $SingleinformationObj=new Singleinformation();
        $ShopObj=new Shop();
        $CommonObj=new Common();
        $RegionObj=new Region();
        $MoregalleryObj=new Moregallery();
        $JsonimageObj = new Jsonimage();


        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }

        $shop_id=Buddha_Http_Input::getParameter('shop_id');

        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');

        /*异地发布:0是 1否*/
        $is_remote=(int)Buddha_Http_Input::getParameter('is_remote');

        /*判断 $is_remote 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($is_remote)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }


        $image_arr=Buddha_Http_Input::getParameter('image_arr');
        /*判断图片数组是否是Json*/
        if(Buddha_Atom_String::isJson($image_arr)){
            $image_arr = json_decode($image_arr);
        }
        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);

        if(!$ShopObj->isShopByShopid($shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000009, '店铺内码ID无效！');
        }

        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);



        /*只有在选择了异地发布的时候才验证地区*/
        if($is_remote==1){
            if(!$RegionObj->isProvince($level1)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
            }

            if(!$RegionObj->isCity($level2)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
            }

            if(!$RegionObj->isArea($level3)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
            }
        }


        /*信息的名称*/
        $name=Buddha_Http_Input::getParameter('name');


        /*是否上架:0为是  1为否*/
        $buddhastatus=(int)Buddha_Http_Input::getParameter('buddhastatus');
        /*判断 $buddhastatus 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($buddhastatus)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }


        /*简述*/
        $brief=Buddha_Http_Input::getParameter('brief');

        /*详情*/
        $desc=Buddha_Http_Input::getParameter('desc');


        $datas = array();
        $datas['user_id'] = $user_id;
        $datas['name'] = $name;
        $datas['shop_id'] = $shop_id;
        $datas['shop_name'] = $ShopObj->getShopNameByShopid($shop_id,$user_id);
        $datas['buddhastatus'] = $buddhastatus;
        $datas['is_remote'] = $is_remote;
        /*只有在选择了异地发布的时候才验证地区*/
        if($is_remote==1) {
            $datas['level0'] = 1;
            $datas['level1'] = $level1;
            $datas['level2'] = $level2;
            $datas['level3'] = $level3;
        }else{
            $Db_shop= $ShopObj->getShopareaByShopid($shop_id,$user_id);
            $datas['level0'] = $Db_shop['level0'];
            $datas['level1'] = $Db_shop['level1'];
            $datas['level2'] = $Db_shop['level2'];
            $datas['level3'] = $Db_shop['level3'];
        }
        $datas['brief'] = $brief;
        $datas['desc'] = $desc;
        $singleinformation_num=$SingleinformationObj->edit($datas,$singleinformation_id);

        if(!$singleinformation_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000020, $this->tablenamestr.'编辑失败！');
        }

        /*相册*/

        if(Buddha_Atom_Array::isValidArray($image_arr)){
            $savePath="storage/{$this->tablename}/{$singleinformation_id}/";
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
            if(Buddha_Atom_Array::isValidArray($MoreImage)){
                $uploadfield='image_arr';
                $MoregalleryObj->addImageArrToMoregallery($MoreImage,$singleinformation_id,$savePath,$shop_id,$uploadfield,$this->tablename,$user_id);
                $SingleinformationObj->setFirstGalleryImgToShop($singleinformation_id);
            }
        }

        /*富文本编辑器图片处理*/
        if($desc){
            $MoregalleryObj=new Moregallery();
            $field='desc';
            $saveData_desc = $MoregalleryObj->base_upload($desc,$singleinformation_id,$this->tablename,$field);
            $saveData_desc = str_replace(PATH_ROOT,'/', $saveData_desc);
            $details['desc'] = $saveData_desc;
            $SingleinformationObj->edit($details,$singleinformation_id);
        }


        $param = array();

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
        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] =$this->tablenamestr.'更新成功';
        $jsondata['usertoken'] = $usertoken;
        $jsondata['singleinformation_id'] = $singleinformation_id;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'更新（或编辑）');
    }
    /**
     *  商家： 单页信息删除
     */

    public function del()
    {

        if (Buddha_Http_Input::checkParameter(array('usertoken','singleinformation_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $SingleinformationObj = new Singleinformation();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $singleinformation_id=(int)Buddha_Http_Input::getParameter('singleinformation_id')?(int)Buddha_Http_Input::getParameter('singleinformation_id'):0;

        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }

        if(!$SingleinformationObj->getShopidIsVerify($singleinformation_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000022, '单页信息内码ID无效！');
        }

//        $Db_Singleinformation_num = $SingleinformationObj->delRecords("id='{$singleinformation_id}' AND user_id='{$user_id}'");
        $Db_Singleinformation_num = $SingleinformationObj->toCleanTrash($singleinformation_id,$user_id);

        $jsondata = array();
        if($Db_Singleinformation_num)
        {
            $jsondata['db_isok'] = 1;
            $jsondata['db_msg'] = $this->tablenamestr.'删除成功！';
        }else{
            $jsondata['db_isok'] = 0;
            $jsondata['db_msg'] = $this->tablenamestr.'删除失败！';
         }
        $jsondata['singleinformation_id'] = $singleinformation_id;

        $jsondata['usertoken'] = $usertoken;
        $jsondata['singleinformation_id'] = $singleinformation_id;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'删除');
    }

}