<?php

/**
 * Class SupershopglobController
 */
class SupershopglobController extends Buddha_App_Action{


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
                Buddha_Http_Head::redirect('没有选中',"index.php?a=more&c=supershopglob");
            }
            $ids = implode ( ',', Buddha_Http_Input::getParameter ( 'ids' ));
            switch($job){

                case 'stop';
                    $this->db->updateRecords(array('buddhastatus' =>1 ),'supershopglob',"id IN ($ids)");
                    Buddha_Http_Head::redirect('删除成功',"index.php?a=more&c=supershopglob");
                    break;
                case 'del';
                    $this->db->updateRecords(array('isdel' =>1 ),'supershopglob',"id IN ($ids)");
                    Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=supershopglob");
                    break;


            }
        }


        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 0;
        $p=(int)Buddha_Http_Input::getParameter('p');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $searchType = array (1=> '名称',2 => '姓名',3 => '手机号');

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
            $where.=" and (main.name like '%$keyword%' or  u.mobile like '%$keyword%' or  u.realname like '%$keyword%') ";
            $params['keyword'] = $keyword;


        }

       $sql ="SELECT count(*) AS total
       FROM {$this->prefix}supershopglob as main
       LEFT JOIN {$this->prefix}supershopconf as ssc
       ON main.supershopconf_id = ssc.id
       LEFT JOIN {$this->prefix}user as u
       ON ssc.user_id = u.id
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

         $sql ="SELECT  main.*,shop.name as shop_name,u.realname as user_realname,u.mobile as user_mobile,
                        ssc.appKey,ssc.appSecret
     FROM {$this->prefix}supershopglob as main
     LEFT JOIN {$this->prefix}supershopconf as ssc
     ON main.supershopconf_id = ssc.id
     LEFT JOIN {$this->prefix}shop as shop
     ON ssc.shop_id = shop.id
     LEFT JOIN {$this->prefix}user as u
     ON ssc.user_id = u.id
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
        $SupershopglobObj = new Supershopglob();


        //获取数据
        $name = Buddha_Http_Input::getParameter('name');
        $supershopconf_id = Buddha_Http_Input::getParameter('supershopconf_id');

        $path = PATH_ROOT . "storage/supershop/";
        if (!is_dir($path)){
            @mkdir($path,0777,true);
        }

        $path = PATH_ROOT . "storage/supershop/glob/";
        if (!is_dir($path)){
            @mkdir($path,0777,true);
        }

      if(Buddha_Http_Input::isPost()){

          $Image= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/supershop/glob/",
              array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('Image')
              ->getOneReturnArray();


          $sourcepic = str_replace("storage/supershop/glob/",'',$Image);


            $data=array();
            $data['name']=$name;
            $data['supershopconf_id']=$supershopconf_id;
            $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
          if($Image) {

              $data['logo'] = "storage/supershop/glob/" . $sourcepic;
          }



            $add=$SupershopglobObj->add($data);
            if($add){
                Buddha_Http_Head::redirect('添加成功',"index.php?a=more&c=supershopglob");
            }else{
                Buddha_Http_Head::redirect('添加失败',"index.php?a=more&c=supershopglob");
            }

        }

        $optionList = $SupershopconfObj->getOption();

        $this->smarty->assign('optionList',$optionList);
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
        $SupershopglobObj = new Supershopglob();

        $id=(int)Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $Db_Supershopglob=$SupershopglobObj->fetch($id);

        if(!count($Db_Supershopglob)){
            Buddha_Http_Head::redirect('信息不存在',"index.php?a=more&c=supershopglob&p={$page}");
        }

        //获取数据
        $name = Buddha_Http_Input::getParameter('name');
        $supershopconf_id = Buddha_Http_Input::getParameter('supershopconf_id');

        $view = Buddha_Http_Input::getParameter('view');
        $id = Buddha_Http_Input::getParameter('id');
        $buddhastatus = Buddha_Http_Input::getParameter('buddhastatus')? 0:1;



        if(Buddha_Http_Input::isPost()){

            $Image= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/supershop/glob/",
                array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                ->getOneReturnArray();


            $sourcepic = str_replace("storage/supershop/glob/",'',$Image);



            $data=array();
            $data['name']=$name;
            $data['supershopconf_id']=$supershopconf_id;
            $data['buddhastatus']=$buddhastatus;


            if($Image) {
                //删除图片
                $SupershopglobObj->deleteFIleOfPicture($id);
                $data['sourcepic'] = "storage/supershop/glob/" . $sourcepic;
            }

            $SupershopglobObj->edit($data,$id);

            if($SupershopglobObj){
                Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=supershopglob&p={$page}");
            }else{
                Buddha_Http_Head::redirect('编辑失败',"index.php?a=more&c=supershopglob&p={$page}");
            }


        }

        $Db_Supershopglob=$SupershopglobObj->fetch($id);
        $this->smarty->assign('Db_Supershopglob', $Db_Supershopglob);
        $this->smarty->assign('page',$page);

        $optionList = $SupershopconfObj->getOption($Db_Supershopglob['supershopconf_id']);

        $this->smarty->assign('optionList',$optionList);

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