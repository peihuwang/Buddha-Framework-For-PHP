<?php

/**
 * Class ArticleController
 */
class ArticleController extends Buddha_App_Action
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
     * 系统公告列表
     */
    public function usersystemnoticemore()
    {
        $ArticlecatalogObj = new Articlecatalog();
        $page = (int)Buddha_Http_Input::getParameter('page') ? (int)Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $cat_id = $ArticlecatalogObj->getSystemNoticeCatid();
        $where = " cat_id={$cat_id} and buddhastatus='0 ' and isdel='0' ";
        $sql = "select count(*) as total from {$this->prefix}article where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $orderby = " order by id DESC ";

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $sql = "select id as article_id,cat_id,name,brief,createtimestr from {$this->prefix}article WHERE  {$where}{$orderby} {$limit}";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;
        $jsondata['list'] = $list;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '系统公告');
    }

    /**
     *系统公告详情
     */
    public function usernoticesingle()
    {
        $UserObj = new User();
        if (Buddha_Http_Input::checkParameter(array('usertoken', 'article_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }
        $article_id = (int)Buddha_Http_Input::getParameter('article_id');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $sql = "select id as article_id,cat_id,name,brief,createtimestr,content from {$this->prefix}article WHERE id='{$article_id}'";
        $Db_newsInfo = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = $Db_newsInfo[0];
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '系统公告详情');


    }

    /**
     * 平台简介列表
     */
    public function platformbrief(){
        $UserObj = new User();
        $ArticleObj = new Article();
        $ArticlecatalogObj = new Articlecatalog();
        /*if (Buddha_Http_Input::checkParameter(array('b_display'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }*/
        $page = (int)Buddha_Http_Input::getParameter('page') ? (int)Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $cat_id = $ArticlecatalogObj->getplatformbrief();
        $where = " cat_id={$cat_id} and buddhastatus='0 ' and isdel='0' ";
        $sql = "select count(*) as total from {$this->prefix}article where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $rcount = $ArticleObj->countRecords($where);
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $orderby = " order by id DESC ";

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $sql = "select id as profile_id,cat_id,name,brief,createtimestr from {$this->prefix}article WHERE  {$where}{$orderby} {$limit}";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;
        $jsondata['list'] = $list;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '平台简介');
    }

    /**
     * 平台简介详情
     */
    public function platformbriefsingle(){
        //$UserObj = new User();
        //$ArticleObj = new News();
        if (Buddha_Http_Input::checkParameter(array( 'profile_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }
        $profile_id = (int)Buddha_Http_Input::getParameter('profile_id');
        /*$usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];*/
        $sql = "select id as profile_id,cat_id,name,brief,createtimestr,content from {$this->prefix}article WHERE id='{$profile_id}'";
        $Db_newsInfo = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = $Db_newsInfo[0];

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '平台简介详情');
    }

    /**
     * 代理商入门
     */
    public function agentknowledgemore(){
        $UserObj = new User();
        $ArticleObj = new Article();
        $ArticlecatalogObj = new Articlecatalog();
        /*if (Buddha_Http_Input::checkParameter(array('b_display'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }*/
        $page = (int)Buddha_Http_Input::getParameter('page') ? (int)Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $cat_id = $ArticlecatalogObj->agentknowledge();
        $where = " cat_id={$cat_id} and buddhastatus='0 ' and isdel='0' ";
        $sql = "select count(*) as total from {$this->prefix}article where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $rcount = $ArticleObj->countRecords($where);
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $orderby = " order by id DESC ";

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $sql = "select id as article_id,cat_id,name,brief,createtimestr from {$this->prefix}article WHERE  {$where}{$orderby} {$limit}";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;
        $jsondata['list'] = $list;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商入门');
    }

    /**
     * 代理商详情
     */
    public function agentknowledgesingle(){
        if (Buddha_Http_Input::checkParameter(array( 'article_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }
        $article_id = (int)Buddha_Http_Input::getParameter('article_id');
        $sql = "select id as merchant_id,cat_id,name,brief,createtimestr,content from {$this->prefix}article WHERE id='{$article_id}'";
        $Db_newsInfo = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = $Db_newsInfo[0];

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, ' 代理商入门详情');
    }

    /**
     * 商家入门列表
     */
    public function merchantnowledgemore(){
        $UserObj = new User();
        $ArticleObj = new Article();
        $ArticlecatalogObj = new Articlecatalog();
        /*if (Buddha_Http_Input::checkParameter(array('b_display'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }*/
        $page = (int)Buddha_Http_Input::getParameter('page') ? (int)Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $cat_id = $ArticlecatalogObj->merchantnowledge();
        $where = " cat_id={$cat_id} and buddhastatus='0 ' and isdel='0' ";
        $sql = "select count(*) as total from {$this->prefix}article where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $rcount = $ArticleObj->countRecords($where);
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $orderby = " order by id DESC ";

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $sql = "select id as merchant_id,cat_id,name,brief,createtimestr from {$this->prefix}article WHERE  {$where}{$orderby} {$limit}";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;
        $jsondata['list'] = $list;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '商家入门');
    }

    /**
     * 商家入门详情
     */
    public function merchantnowledgesingle(){
        //$UserObj = new User();
        //$ArticleObj = new News();
        if (Buddha_Http_Input::checkParameter(array( 'merchant_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }
        $merchant_id = (int)Buddha_Http_Input::getParameter('merchant_id');
        /*$usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];*/
        $sql = "select id as merchant_id,cat_id,name,brief,content,createtimestr from {$this->prefix}article WHERE id='{$merchant_id}'";
        $Db_newsInfo = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = $Db_newsInfo[0];

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '商家入门详情');
    }
    /**
     * 新手入门列表
     */
    public function gettingStarted(){
        $ArticleObj = new Article();
        $ArticlecatalogObj = new Articlecatalog();
        $page = (int)Buddha_Http_Input::getParameter('page') ? (int)Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $cat_id = $ArticlecatalogObj->novice();
        $where = " cat_id={$cat_id} and buddhastatus='0 ' and isdel='0' ";
        $sql = "select count(*) as total from {$this->prefix}article where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $rcount = $ArticleObj->countRecords($where);
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $orderby = " order by id DESC ";

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $sql = "select id as merchant_id,cat_id,name,brief,createtimestr from {$this->prefix}article WHERE  {$where}{$orderby} {$limit}";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;
        $jsondata['list'] = $list;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '新手入门');
    }
    /**
     * 新手入门详情
     */
    public function gettingStartedSingle(){
        if (Buddha_Http_Input::checkParameter(array( 'merchant_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }
        $merchant_id = (int)Buddha_Http_Input::getParameter('merchant_id');
        $sql = "select id as merchant_id,cat_id,name,brief,content,createtimestr from {$this->prefix}article WHERE id='{$merchant_id}'";
        $Db_newsInfo = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = $Db_newsInfo[0];

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '新手入门详情');
    }

    /**
     * 合伙人入门列表
     */
    public function partnersMore(){
        $ArticleObj = new Article();
        $ArticlecatalogObj = new Articlecatalog();
        $page = (int)Buddha_Http_Input::getParameter('page') ? (int)Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $cat_id = $ArticlecatalogObj->partners();
        $where = " cat_id={$cat_id} and buddhastatus='0 ' and isdel='0' ";
        $sql = "select count(*) as total from {$this->prefix}article where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $rcount = $ArticleObj->countRecords($where);
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $orderby = " order by id DESC ";

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $sql = "select id as merchant_id,cat_id,name,brief,createtimestr from {$this->prefix}article WHERE  {$where}{$orderby} {$limit}";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;
        $jsondata['list'] = $list;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商入门列表');
    }

    /**
     * 合伙人入门详情
     */
    public function partnersSingle(){
        if (Buddha_Http_Input::checkParameter(array( 'merchant_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }
        $merchant_id = (int)Buddha_Http_Input::getParameter('merchant_id');
        $sql = "select id as merchant_id,cat_id,name,brief,content,createtimestr from {$this->prefix}article WHERE id='{$merchant_id}'";
        $Db_newsInfo = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = $Db_newsInfo[0];

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '合伙人入门详情');
    }


}