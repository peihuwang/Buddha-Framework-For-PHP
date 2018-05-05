<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/11
 * Time: 10:39
 * author sys
 */
class AgentsController extends Buddha_App_Action
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
     * 会员管理列表
     */

    public function membermore(){
        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有代理商权限');
        }
        $where = " isdel=0 and level3='{$Db_User['level3']}' and (groupid='1' or groupid='4') ";
        if($keyword){
            $where.=" and (username like '%$keyword%' or realname like '%$keyword%') ";
        }
        $rcount = $this->db->countRecords( $this->prefix.'user', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
          if($page > $pcount){
              $page=$pcount;
          }

        $orderby = " order by onlineregtime DESC ";
        $list = $this->db->getFiledValues (array('id','username','realname','mobile','groupid'),  $this->prefix.'user', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        foreach($list as $k=>$v){
            if($v['realname']){
                $name=$v['realname'];
            }else{
                $name=$v['username'];
            }
            if($v['groupid']==1){
                $groupid='商家';
            }elseif($v['groupid']==4){
                $groupid='个人';
            }
            $memberlist[]=array(
                'user_id'=>$v['id'],
                'name'=>$name,
                'mobile'=>$v['mobile'],
                'groupid'=>$groupid,
            );
        }
        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }
        if(Buddha_Atom_Array::isValidArray($memberlist)){
            $jsondata['list'] = $memberlist;
        }else{
            $jsondata['list'] = array();
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'会员管理列表');

    }

    /**
     * 会员详情
     */
    public function membersingle(){
        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken','user_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $ShopObj = new Shop();
        $RegionObj = new Region();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $uid=Buddha_Http_Input::getParameter('user_id');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有代理商权限');
        }
        $field = array('id as user_id','logo','username','nickname','mobile','realname','level1','level2','level3','address','gender');
        $userifon=$UserObj->getSingleFiledValues($field,"id='{$uid}'");
        $agent_area=$RegionObj->getAllArrayAddressByLever($userifon['level3']);
        if($agent_area){
            $areadder='';
            foreach($agent_area as $k=> $v) {
                if ($k != 0)
                    $areadder.=$v['name'].' > ';
            }
            $userifon['areadder']=Buddha_Atom_String::toDeleteTailCharacter($areadder);
        }
        $userifon['logo'] = $host . $userifon['logo'];
        $jsondata['list'] = $userifon;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'代理商会员管理详情');


    }

    /**
     * 供应管理
     */
    public function supplymore(){
        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有代理商权限');
        }
        $ShopObj = new Shop();
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $where =" level3='{$Db_User['level3']}' ";
        if($view) {
            switch ($view) {
                case 2;
                    $where .= ' and isdel=0 and is_sure=0';
                    break;
                case 3;
                    $where .= " and isdel=0 and is_sure=1";
                    break;
                case 4;
                    $where .= " and isdel=0 and is_sure=4 ";
                    break;
                case 5;
//////////////↓↓↓↓/////////////////
                    $where .= " and isdel=0 and is_sure=1 and buddhastatus=1";
///////////↑↑↑↑↑↑↑////////////////////
                    break;
            }
        }
        if($keyword){
            $where.=" and goods_name like '%$keyword%'";
        }

        $sql = "select count(*) as total from {$this->prefix}supply  where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $orderby = " order by add_time DESC ";
        $list = $this->db->getFiledValues (array('id','user_id','shop_id','goods_sn','goods_name','is_promote','market_price','promote_price','promote_start_date','promote_end_date','goods_thumb','buddhastatus','is_sure'),  $this->prefix.'supply', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        foreach($list as $k=>$v){
            $nwstiem=Buddha::$buddha_array['buddha_timestamp'];
            if($v['shop_id']!=0){
                $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                $name='商家：'.$shop_name['name'];
            }else{
                $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                $name='个人：'.$shop_name['name'];
            }
            if($v['is_promote']==0){
                $price=$v['market_price'];
            }else{
                if($nwstiem<$v['promote_start_date']){
                    $price=$v['market_price'];
                }elseif($nwstiem>$v['promote_start_date'] and  $nwstiem< $v['promote_end_date']){
                    $price=$v['promote_price']." 原价:".$v['market_price'];
                }else{
                    $ShopObj->edit(array('promote_price'=>0,'is_promote'=>0,'promote_start_date'=>0,'promote_end_date'=>0),$v['id']);
                }
            }
            if($v['is_sure']==0){
                $is_sure='0';
            }elseif($v['is_sure']==4){
                $is_sure='4';
            }else{
                $is_sure='1';
            }
            if($v['buddhastatus']==1){
                $state='上 架';
            }else if($v['buddhastatus']==0){
                $state='下 架 ';
            }
            if($v['is_sure'] == 0){
                $icon_audit = $host . "apiuser/menuplus/weishenhe.png";
            }elseif($v['is_sure'] == 1){
                $icon_audit = $host . "apiuser/menuplus/yishenhe.png";
            }elseif($v['is_sure'] == 4){
                $icon_audit = $host . "apiuser/menuplus/weitongguo.png";
            }
            $jsondatas[]=array(
                'id'=>$v['id'],
                'title'=>$v['goods_name'],
                'images'=>$host . $v['goods_thumb'],
                'goods_sn'=>$v['goods_sn'],
                'user_id'=>$v['user_id'],
                'icon_money' => $host . "apiuser/menuplus/icon_money.png",
                'icon_shop' => $host . "apiuser/menuplus/icon_shop.png",
                'icon_audit' => $icon_audit,
                'is_sure'=>$is_sure,
                'buddhastatus' => $v['buddhastatus'],
                'state'=>$state,
                'name'=>$name,
                'price'=>"￥".$price,
            );
        }

        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }
        if($list){
            $jsondata['list']=$jsondatas;
        }else{
            $jsondata['list']= array();
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商管理供应列表');

    }

    /**
     * 代理商供应审核
     */
    public function supplyaudit(){
        if (Buddha_Http_Input::checkParameter(array('supply_id','usertoken','is_sure'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $SupplyObj = new Supply();
        $supply_id =  Buddha_Http_Input::getParameter('supply_id');
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

        if(!$SupplyObj->isOwnerBelongToAgentByLeve3($supply_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $data = array();
        $data['is_sure'] = $is_sure;
        $SupplyObj->edit($data,$supply_id);
        $jsondata = array();
        $jsondata['supply_id'] = $supply_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商供应审核');
    }

    /**
     * 代理商供应上下架
     */
    public function supplyoffshelf(){
        if (Buddha_Http_Input::checkParameter(array('supply_id','usertoken','buddhastatus'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $SupplyObj = new Supply();

        $supply_id =  Buddha_Http_Input::getParameter('supply_id');
        /*默认下架  0下架 1=上架*/
        $buddhastatus = (int)Buddha_Http_Input::getParameter('buddhastatus') ? (int)Buddha_Http_Input::getParameter('buddhastatus') : 0;

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];
        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }
        if(!$SupplyObj->isOwnerBelongToAgentByLeve3($supply_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }
        $data = array();
        $msg="";
        if($buddhastatus==0){
            $data['buddhastatus'] =1 ;
            $msg="下架";
        }else{
            $data['buddhastatus'] =0 ;
            $msg="上架";
        }
        $SupplyObj->edit($data,$supply_id);
        $jsondata = array();
        $jsondata['supply_id'] = $supply_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['msg'] = $msg;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商供应管理'.$msg);
    }


    /**
     * 租赁管理列表
     */

    public function leasemore(){
        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $ShopObj = new Shop();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有代理商权限');
        }
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):1;
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $where = " level3='{$Db_User['level3']}' ";
        if ($view) {
            switch ($view) {
                case 2;
                    $where .= ' and isdel=0 and is_sure=0';
                    break;
                case 3;
                    $where .= " and isdel=0 and is_sure=1";
                    break;
                case 4;
                    $where .= " and isdel=0 and is_sure=4 ";
                    break;
                case 5;
//                        $where.=" and isdel=4 and buddhastatus=1";
//////////////↓↓↓↓/////////////////
                    $where .= " and isdel=0 and is_sure=1 and buddhastatus=1";
///////////↑↑↑↑↑↑↑////////////////////                        break;
            }
        }
        if ($keyword) {
            $where .= " and lease_name like '%$keyword%'";
        }
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $sql = "select count(*) as total from {$this->prefix}lease  where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $orderby = " order by add_time DESC ";
        $list = $this->db->getFiledValues('', $this->prefix . 'lease', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));

        foreach ($list as $k => $v) {
            if ($v['shop_id'] != 0) {
                $shop_name = $ShopObj->getSingleFiledValues(array('name'), "id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                $name = '商家：' . $shop_name['name'];
            } else {
                $shop_name = $UserObj->getSingleFiledValues(array('name'), "id='{$v['user_id']}'");
                $name = '个人：' . $shop_name['name'];
            }
            $price = "￥". $v['rent'] ;
            if ($v['is_sure'] == 0) {
                $is_sure = '0';
            } elseif ($v['is_sure'] == 4) {
                $is_sure = '4';
            } else {
                $is_sure = '1';
            }
            if ($v['buddhastatus'] == 1) {
                $state = '上 架';
            } else if ($v['buddhastatus'] == 0) {
                $state = '下 架 ';
            }
            if ($v['is_sure'] == 0) {
                $icon_audit = $host . "apiuser/menuplus/weishenhe.png";
            } elseif ($v['is_sure'] == 1) {
                $icon_audit = $host . "apiuser/menuplus/yishenhe.png";
            } elseif ($v['is_sure'] == 4) {
                $icon_audit = $host . "apiuser/menuplus/weitongguo.png";
            }

            $jsondatas[] = array(
                'lease_id' => $v['id'],
                'title' => $v['lease_name'],
                'images' => $host . $v['lease_thumb'],
                'user_id' => $v['user_id'],
                'icon_money' => $host . "apiuser/menuplus/icon_money.png",
                'icon_shop' => $host . "apiuser/menuplus/icon_shop.png",
                'icon_audit' => $icon_audit,
                'is_sure'=>$is_sure,
                'buddhastatus' => $v['buddhastatus'],
                'name' => $name,
                'price' => $price,
            );
        }
        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }
        if($list){
            $jsondata['list']=$jsondatas;
        }else{
            $jsondata['list']= array();
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商管理租赁列表');
    }


    /**
     * 租赁审核
     */
    public function leaseaudit(){
        if (Buddha_Http_Input::checkParameter(array('lease_id','usertoken','is_sure'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $LeaseObj = new Lease();
        $lease_id =  Buddha_Http_Input::getParameter('lease_id');
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

        if(!$LeaseObj->isOwnerBelongToAgentByLeve3($lease_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $data = array();
        $data['is_sure'] = $is_sure;
        $LeaseObj->edit($data,$lease_id);
        $jsondata = array();
        $jsondata['lease_id'] = $lease_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商租赁审核');
    }

    /**
     * 代理商租赁上下架
     */
    public function leaseoffshelf(){
        if (Buddha_Http_Input::checkParameter(array('lease_id','usertoken','buddhastatus'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $LeaseObj = new Lease();

        $lease_id =  Buddha_Http_Input::getParameter('lease_id');
        /*默认下架  0下架 1=上架*/
        $buddhastatus = (int)Buddha_Http_Input::getParameter('buddhastatus') ? (int)Buddha_Http_Input::getParameter('buddhastatus') : 0;

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];
        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }
        if(!$LeaseObj->isOwnerBelongToAgentByLeve3($lease_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }
        $data = array();
        $msg="";
        if($buddhastatus==0){
            $data['buddhastatus'] =1 ;
            $msg="下架";
        }else{
            $data['buddhastatus'] =0 ;
            $msg="上架";
        }
        $LeaseObj->edit($data,$lease_id);
        $jsondata = array();
        $jsondata['lease_id'] = $lease_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['msg'] = $msg;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商租赁管理'.$msg);
    }
    /**
     * 需求管理
     */
    public function demandmore(){
        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有代理商权限');
        }
        $ShopObj = new Shop();
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $where =" level3='{$Db_User['level3']}' ";
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
                case 5;
//////////////↓↓↓↓/////////////////
                    $where.=" and isdel=0 and is_sure=1 and buddhastatus=1";
///////////↑↑↑↑↑↑↑////////////////////
                    break;
            }
        }
        if($keyword){
            $where.=" and name like '%{$keyword}%'";
        }

        $sql = "select count(*) as total from {$this->prefix}demand  where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $orderby = " order by add_time DESC ";
        $list = $this->db->getFiledValues ('',  $this->prefix.'demand', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        foreach($list as $k=>$v){
            $nwstiem=Buddha::$buddha_array['buddha_timestamp'];
            if($v['shop_id']!=0){
                $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                $name='商家：'.$shop_name['name'];
            }else{
                $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                $name='个人：'.$shop_name['name'];
            }
            if($v['is_promote']==0){
                $price=$v['market_price'];
            }else{
                if($nwstiem<$v['promote_start_date']){
                    $price=$v['market_price'];
                }elseif($nwstiem>$v['promote_start_date'] and  $nwstiem< $v['promote_end_date']){
                    $price=$v['promote_price']." 原价:".$v['market_price'];
                }else{
                    $ShopObj->edit(array('promote_price'=>0,'is_promote'=>0,'promote_start_date'=>0,'promote_end_date'=>0),$v['id']);
                }
            }
            if($v['is_sure']==0){
                $is_sure='0';
            }elseif($v['is_sure']==4){
                $is_sure='4';
            }else{
                $is_sure='1';
            }
            if($v['buddhastatus']==1){
                $state='上 架';
            }else if($v['buddhastatus']==0){
                $state='下 架 ';
            }
            if($v['is_sure'] == 0){
                $icon_audit = $host . "apiuser/menuplus/weishenhe.png";
            }elseif($v['is_sure'] == 1){
                $icon_audit = $host . "apiuser/menuplus/yishenhe.png";
            }elseif($v['is_sure'] == 4){
                $icon_audit = $host . "apiuser/menuplus/weitongguo.png";
            }
            $jsondatas[]=array(
                'demand_id'=>$v['id'],
                'title'=>$v['name'],
                'images'=> $host . $v['demand_thumb'],
                'user_id'=>$v['user_id'],
                'icon_money' => $host . "apiuser/menuplus/icon_money.png",
                'icon_shop' => $host . "apiuser/menuplus/icon_shop.png",
                'icon_audit' => $icon_audit,
                'is_sure'=>$is_sure,
                'buddhastatus' => $v['buddhastatus'],
                'state'=>$state,
                'name'=>$name,
                'price'=>"￥".$v['budget'],
            );
        }

        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }
        if($list){
            $jsondata['list']=$jsondatas;
        }else{
            $jsondata['list']= array();
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商管理需求列表');
    }

    /**
     * 需求审核
     */
    public function demandaudit(){
        if (Buddha_Http_Input::checkParameter(array('demand_id','usertoken','is_sure'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $DemandObj = new Demand();
        $demand_id =  Buddha_Http_Input::getParameter('demand_id');
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

        if(!$DemandObj->isOwnerBelongToAgentByLeve3($demand_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $data = array();
        $data['is_sure'] = $is_sure;
        $DemandObj->edit($data,$demand_id);
        $jsondata = array();
        $jsondata['demand_id'] = $demand_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商需求审核');
    }

    /**
     * 需求上下架
     */
    public function demandoffshelf(){
        if (Buddha_Http_Input::checkParameter(array('demand_id','usertoken','buddhastatus'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $DemandObj = new Demand();

        $demand_id =  Buddha_Http_Input::getParameter('demand_id');
        /*默认下架  0下架 1=上架*/
        $buddhastatus = (int)Buddha_Http_Input::getParameter('buddhastatus') ? (int)Buddha_Http_Input::getParameter('buddhastatus') : 0;

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];
        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }
        if(!$DemandObj->isOwnerBelongToAgentByLeve3($demand_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }
        $data = array();
        $msg="";
        if($buddhastatus==0){
            $data['buddhastatus'] =1 ;
            $msg="下架";
        }else{
            $data['buddhastatus'] =0 ;
            $msg="上架";
        }
        $DemandObj->edit($data,$demand_id);
        $jsondata = array();
        $jsondata['demand_id'] = $demand_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['msg'] = $msg;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, ' 代理商管理需求'.$msg);
    }

    /**
     * 招聘管理
     */
    public function recruitmore(){
        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有代理商权限');
        }
        $ShopObj = new Shop();
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $where =" level3='{$Db_User['level3']}' ";
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
                case 5;
//////////////↓↓↓↓/////////////////
                    $where.=" and isdel=0 and is_sure=1 and buddhastatus=1";
///////////↑↑↑↑↑↑↑////////////////////
                    break;
            }
        }
        if($keyword){
            $where.=" and recruit_name like '%$keyword%'";
        }

        $sql = "select count(*) as total from {$this->prefix}recruit  where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $orderby = " order by add_time DESC ";
        $list = $this->db->getFiledValues ('',  $this->prefix.'recruit', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        foreach($list as $k=>$v){
            $nwstiem=Buddha::$buddha_array['buddha_timestamp'];
            if($v['shop_id']!=0){
                $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                $name='商家：'.$shop_name['name'];
            }else{
                $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                $name='个人：'.$shop_name['name'];
            }
            if($v['pay']>0){
                $price="￥".$v['pay'];
            }else{
                    $price="面议";
            }
            if($v['is_sure']==0){
                $is_sure='0';
            }elseif($v['is_sure']==4){
                $is_sure='4';
            }else{
                $is_sure='1';
            }
            if($v['buddhastatus']==1){
                $state='上 架';
            }else if($v['buddhastatus']==0){
                $state='下 架 ';
            }
            if($v['is_sure'] == 0){
                $icon_audit = $host . "apiuser/menuplus/weishenhe.png";
            }elseif($v['is_sure'] == 1){
                $icon_audit = $host . "apiuser/menuplus/yishenhe.png";
            }elseif($v['is_sure'] == 4){
                $icon_audit = $host . "apiuser/menuplus/weitongguo.png";
            }
            $jsondatas[]=array(
                'recruit_id'=>$v['id'],
                'title'=>$v['recruit_name'],
                'user_id'=>$v['user_id'],
                'icon_money' => $host . "apiuser/menuplus/icon_money.png",
                'icon_shop' => $host . "apiuser/menuplus/icon_shop.png",
                'icon_audit' => $icon_audit,
                'is_sure'=>$is_sure,
                'buddhastatus' => $v['buddhastatus'],
                'state'=>$state,
                'name'=>$name,
                'price'=>$price,
            );
        }

        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }
        if($list){
            $jsondata['list']=$jsondatas;
        }else{
            $jsondata['list']= array();
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商管理招聘列表');
    }


}