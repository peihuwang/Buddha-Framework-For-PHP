<?php

/**
 * Class SupershopconfController
 */
class SupershopconfController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function more(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        if(Buddha_Http_Input::getParameter('job')){
            $job = Buddha_Http_Input::getParameter ( 'job' );
            if (! Buddha_Http_Input::getParameter ( 'ids' )) {
                Buddha_Http_Head::redirect('没有选中',"index.php?a=more&c=supershopconf");
            }
            $ids = implode ( ',', Buddha_Http_Input::getParameter ( 'ids' ));
            switch($job){

                case 'stop';
                    $this->db->updateRecords(array('buddhastatus' =>1 ),'supershopconf',"id IN ($ids)");
                    Buddha_Http_Head::redirect('删除成功',"index.php?a=more&c=supershopconf");
                    break;
                case 'del';
                    $this->db->updateRecords(array('isdel' =>1 ),'supershopconf',"id IN ($ids)");
                    Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=supershopconf");
                    break;


            }
        }


        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 0;
        $p=(int)Buddha_Http_Input::getParameter('p');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $searchType = array (1=> '应用名称',2 => '姓名',3 => '手机号');

        $where = " main.isdel=0 ";
        /*view=0 上架 1=下架*/
        if($view) {
            $params['view'] = $view;
            switch ($view) {
                case 0:
                   $where .= " and main.buddhastatus = 0 "  ;
                    break;

                case 1:

                    $where .= " and main.buddhastatus = 1 "  ;
                    break;

            }
        }

        if($keyword){
            $where.=" and (main.appname like '%$keyword%' or  u.mobile like '%$keyword%' or  u.realname like '%$keyword%') ";
            $params['keyword'] = $keyword;
        }

       $sql ="SELECT count(*) AS total
       FROM {$this->prefix}supershopconf as main
       LEFT JOIN {$this->prefix}shop as shop
       ON main.shop_id = shop.id
       LEFT JOIN {$this->prefix}user as u
       ON main.user_id = u.id
       WHERE {$where} ";


        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

         $rcount =$count_arr[0]['total'];

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by main.id DESC ";
        $limit =Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

         $sql ="SELECT  main.*,shop.name as shop_name,u.realname as user_realname,u.mobile as user_mobile
     FROM {$this->prefix}supershopconf as main
     LEFT JOIN {$this->prefix}shop as shop
     ON main.shop_id = shop.id
     LEFT JOIN {$this->prefix}user as u
     ON main.user_id = u.id
     WHERE {$where} {$orderby}  {$limit}";





        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $strPages =  Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=supershopconf&' .http_build_query($params).'&', $pagesize);

        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);
        $this->smarty->assign('params', $params );
        $this->smarty->assign('view',$view);

        $this->smarty->assign('searchType',$searchType);
        $this->smarty->assign('params', $params );
        $this->smarty->assign('keyword', $keyword );

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    public function add(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $SupershopconfObj = new Supershopconf();


        //获取数据
        $appname=Buddha_Http_Input::getParameter('appname');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $user_id=Buddha_Http_Input::getParameter('user_id');

        $appKey=Buddha_Http_Input::getParameter('appKey');
        $appSecret=Buddha_Http_Input::getParameter('appSecret');

        $starttimestr=Buddha_Http_Input::getParameter('starttime');
        $endtimestr=Buddha_Http_Input::getParameter('endtime');


        $starttime = strtotime($starttimestr);
        $endtime = strtotime($endtimestr);


        if(Buddha_Atom_String::isValidString($appKey) AND Buddha_Atom_String::isValidString($appSecret) ){

            $opentoken = md5($appKey.'|'.$appSecret);
        }else{
            $opentoken = md5(time().'|'.rand(1,100));
        }

        $password=Buddha_Http_Input::getParameter('password');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');
        $agentrate=Buddha_Http_Input::getParameter('agentrate');
        $partnerrate=Buddha_Http_Input::getParameter('partnerrate');

      if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['appname']=$appname;
            $data['shop_id']=$shop_id;
            $data['user_id']=$user_id;

            $data['appKey']=$appKey;
            $data['appSecret']=$appSecret;
            $data['opentoken']=$opentoken;
            $data['starttime']=$starttime;
            $data['starttimestr']=$starttimestr;
            $data['endtime']=$endtime;
            $data['endtimestr']=$endtimestr;

            $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];



            $add=$SupershopconfObj->add($data);
            if($add){
                Buddha_Http_Head::redirect('添加成功',"index.php?a=more&c=supershopconf");
            }else{
                Buddha_Http_Head::redirect('添加失败',"index.php?a=more&c=supershopconf");
            }

        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }
    public function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/
        $SupershopconfObj = new Supershopconf();

        $view = Buddha_Http_Input::getParameter('view');
        $id = Buddha_Http_Input::getParameter('id');
        //获取数据
        $appname=Buddha_Http_Input::getParameter('appname');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $user_id=Buddha_Http_Input::getParameter('user_id');

        $appKey=Buddha_Http_Input::getParameter('appKey');
        $appSecret=Buddha_Http_Input::getParameter('appSecret');

        $starttimestr=Buddha_Http_Input::getParameter('starttime');
        $endtimestr=Buddha_Http_Input::getParameter('endtime');


        $starttime = strtotime($starttimestr);
        $endtime = strtotime($endtimestr);


        if(Buddha_Atom_String::isValidString($appKey) AND Buddha_Atom_String::isValidString($appSecret) ){

            $opentoken = md5($appKey.'|'.$appSecret);
        }else{
            $opentoken = md5(time().'|'.rand(1,100));
        }

        $password=Buddha_Http_Input::getParameter('password');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');
        $agentrate=Buddha_Http_Input::getParameter('agentrate');
        $partnerrate=Buddha_Http_Input::getParameter('partnerrate');

        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['appname']=$appname;
            $data['shop_id']=$shop_id;
            $data['user_id']=$user_id;

            $data['appKey']=$appKey;
            $data['appSecret']=$appSecret;
            $data['opentoken']=$opentoken;
            $data['starttime']=$starttime;
            $data['starttimestr']=$starttimestr;
            $data['endtime']=$endtime;
            $data['endtimestr']=$endtimestr;

            $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];



            $add=$SupershopconfObj->edit($data,$id);
            if($add){
                Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=supershopconf");
            }else{
                Buddha_Http_Head::redirect('编辑失败',"index.php?a=more&c=supershopconf");
            }

        }

        $Db_Supershopconf=$SupershopconfObj->fetch($id);
        $this->smarty->assign('Db_Supershopconf', $Db_Supershopconf);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $SupershopconfObj = new Supershopconf();
        $id = Buddha_Http_Input::getParameter('id');
        $view= Buddha_Http_Input::getParameter('view');
        $page = Buddha_Http_Input::getParameter('p');
        $userInfo = $SupershopconfObj->del($id);
        if($userInfo){
            Buddha_Http_Head::redirect('删除成功',"index.php?a=more&c=supershopconf&p={$page}&view={$view}");
        }
        Buddha_Http_Head::redirect('删除失败',"index.php?a=more&c=supershopconf&p={$page}&view={$view}");
    }

}