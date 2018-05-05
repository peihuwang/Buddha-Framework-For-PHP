<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/6
 * Time: 10:24
 */
class BillController extends Buddha_App_Action{
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
     * 我的账单
     */
    public function mybillmore(){
        $UserObj = new User();
        $BillObj = new Bill();
        if(Buddha_Http_Input::checkParameter(array('usertoken'))){//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],444444,'必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $page = (int)Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize')?(int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $fieldsarray= array('id','usertoken','logo', 'realname','mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $where = " user_id='{$user_id}' and isdel=0 ";
        $sql ="select count(*) as total from {$this->prefix}bill where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount =$count_arr[0]['total'];
        $rcount = $BillObj->countRecords($where);
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by createtime DESC ";

        $limit =Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

        $sql ="select id as bill_id,user_id,order_type,order_desc,createtimestr,orient,billamt from {$this->prefix}bill WHERE  {$where}{$orderby} {$limit}";

        $Db_billInfo = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = $Db_billInfo;

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'我的账单');

    }

}