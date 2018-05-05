<?php

/**我的消息
 * Class NewsController
 */
class NewsController extends Buddha_App_Action{
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
     * 获取用户消息列表
     * sys
     */
    public function usernoticemore(){
        $UserObj = new User();
        $NewsObj = new News();
        if(Buddha_Http_Input::checkParameter(array('usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],444444,'必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo', 'realname','mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $page = (int)Buddha_Http_Input::getParameter('page');
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize');
       // $keyword = trim(Buddha_Http_Input::getParameter('keyword'));
        //$starttime = trim(Buddha_Http_Input::getParameter('starttime'));
        //$endtime = trim(Buddha_Http_Input::getParameter('endtime'));
        $page = (int)Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize')?(int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $where = " u_id={$user_id} ";
        /*if(!empty($keyword)){
            $where .= "  and name like %{$keyword}% ";
        }
        if($endtime){
            $starttime = strtotime($starttime);
            $where .= " and add_time >=  {$starttime} ";
        }
        if($endtime  ){
            $endtime = strtotime($endtime);
            $where .= " and add_time <=  {$endtime} ";
        }
        if($starttime && $endtime && $starttime>$endtime){
            $endtime = $starttime + 86400;
            $where .= " and add_time >=  {$starttime} and add_time <=  {$endtime} ";
        }*/
        $sql ="select count(*) as total from {$this->prefix}news where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount =$count_arr[0]['total'];
        $rcount = $NewsObj->countRecords($where);
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by sure desc , add_time DESC ";

        $limit =Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

        $sql ="select id as news_id,u_id,soure_id,shop_id,shop_name,name,content,sure,is_act,add_time,remarks from {$this->prefix}news WHERE  {$where}{$orderby} {$limit}";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;
        $jsondata['list'] = $list;

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'我的消息');

    }

    /**
     * 我的消息详情
     * sys
     */
    public function usernoticesingle(){
        $UserObj = new User();
        $NewsObj = new News();
        if(Buddha_Http_Input::checkParameter(array('usertoken','news_id'))){//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $news_id = (int)Buddha_Http_Input::getParameter('news_id');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo', 'realname','mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $data = array();
        $data['sure'] = 0;
        $NewsObj->edit($data,$news_id);//改变阅读状态
        $sql ="select id as news_id,u_id,soure_id,shop_id,shop_name,name,content,sure,is_act,add_time,remarks from {$this->prefix}news WHERE id='{$news_id}'";
        $Db_newsInfo = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = $Db_newsInfo[0];

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'我的消息详情');

    }

    /**
     * 批量改变消息的阅读状态
     * sys
     */
    public function usernoticebatchtoread(){
        $UserObj = new User();
        $NewsObj = new News();
        if(Buddha_Http_Input::checkParameter(array('usertoken','news_id'))){//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $news_id_arr = Buddha_Http_Input::getParameter('news_id');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo', 'realname','mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!Buddha_Atom_Array::isValidArray($news_id_arr)  or count($news_id_arr)>30){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'news_id参数不正确');
        }
        $ids = implode ( ',', $news_id_arr);
        $NewsObj->updateRecords(array('sure' =>0),"u_id='{$user_id}' AND id IN ($ids)");

        Buddha_Http_Output::makeWebfaceJson($news_id_arr,'/webface/?Services='.$_REQUEST['Services'],0,'操作成功');
    }

    /**
     *我的消息未读个数
     * sys
     */
    public function usernoticeunread(){
        $UserObj = new User();
        $NewsObj = new News();
        if(Buddha_Http_Input::checkParameter(array('usertoken'))){//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo', 'realname','mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $unreadNewNum = $NewsObj->countRecords("u_id='{$user_id}' AND sure='1'");
        $jsondata = $unreadNewNum;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'我的消息(未读)');


    }

    /**
     * 删除我的消息
     * sys
     */
    public function usernoticedeleting(){
        $UserObj = new User();
        $NewsObj = new News();
        if(Buddha_Http_Input::checkParameter(array('usertoken','news_id'))){//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $news_id = (int)Buddha_Http_Input::getParameter('news_id');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$NewsObj->isUserHasDeletePrivilege($user_id,$news_id)){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000019,'没有删除信息的权限');
        }

        $re = $NewsObj->del($news_id);
        if($re){
            $jsondata['info'] = '操作成功！';
        }else{
            $jsondata['info'] = '服务器忙！';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'我的消息详情');
    }


}