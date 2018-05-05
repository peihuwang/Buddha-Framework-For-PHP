<?php

/**
 * Class DemandController
 */
class DemandController extends Buddha_App_Action{

    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='需求';
        $this->tablename='demand';
    }

    public function more(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $DemandObj=new Demand();
        $ShopObj=new Shop();
        $params = array ();
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $p=(int)Buddha_Http_Input::getParameter('p');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $searchType = array (1 => '全部需求', 2 => '新增需求', 3 => '审核通过',4 => '审核未通过', 5=> '下架需求', 6=> '推荐', 7=> '热门');

        if(Buddha_Http_Input::getParameter('job')){
            $job=Buddha_Http_Input::getParameter('job');
            if(!Buddha_Http_Input::getParameter('ids')){
                Buddha_Http_Head::redirect('您没有选择参数','index.php?a=more&c=demand&view='.$view.'&p='.$p);
            }
            $ids = implode ( ',',Buddha_Http_Input::getParameter('ids'));

            switch($job){
                case 'is_sure':
                    $DemandObj->updateRecords(array('is_sure'=>1,'buddhastatus'=>0),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=demand&view='.$view.'&p='.$p);
                    break;
                case 'stop':
                    $DemandObj->updateRecords(array('is_sure'=>0,'buddhastatus'=>1,'isdel'=>4),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=demand&view='.$view.'&p='.$p);
                    break;
                case 'sure':
                    $DemandObj->updateRecords(array('is_sure'=>1,'buddhastatus'=>0,),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=demand&view='.$view.'&p='.$p);
                    break;
                case 'enable':
                    $DemandObj->updateRecords(array('is_sure'=>1,'buddhastatus'=>0,'isdel'=>0),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=demand&view='.$view.'&p='.$p);
                    break;
                case 'is_hot':
                    $DemandObj->updateRecords(array('is_hot'=>1),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=demand&view='.$view.'&p='.$p);
                    break;
                case 'is_rec':
                    $DemandObj->updateRecords(array('is_rec'=>1),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=demand&view='.$view.'&p='.$p);
                    break;
            }
        }

        $where = " (isdel=0 or isdel=4 )";
        if($view) {
            $params['view'] = $view;
            switch ($view) {
                case 2;
                    $where .= ' and is_sure=0';
                    break;
                case 3;
                    $where .= " and is_sure=1";
                    break;
                case 4;
                    $where .= " and is_sure=4 ";
                    break;
                case 5;
                    $where .= " and  buddhastatus=1";
                    break;
                case 6;
                    $where .= " and  is_sure=1 and is_rec=1";
                    break;

                case 7;
                    $where .= " and  is_sure=1 and   is_hot=1";
                    break;

            }
        }

        if($keyword){
            $where.=" and name like '%$keyword%'";
            $params['keyword'] = $keyword;
        }
        $rcount= $DemandObj->countRecords($where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by id DESC ";

        $list = $this->db->getFiledValues('', $this->prefix . 'demand', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));

        $RegionObj = new Region();

        foreach($list as $k=>$v)
        {
            $shop_name=$ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}'");
            $list[$k]['shop_name']=  $shop_name['name'];
            $list[$k]['regionale']= $RegionObj->getDetailOfAdrressByRegionIdStr($v['level1'],$v['level2'],$v['level3']);
        }
        $strPages =  Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=lease&' .http_build_query($params).'&', $pagesize);

        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);
        $this->smarty->assign( 'params', $params );


        $this->smarty->assign('searchType',$searchType);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign( 'keyword', $keyword );

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $DemandObj=new Demand();
        $DemandcatObj=new Demandcat();
        $ShopObj=new Shop();
        $p=(int)Buddha_Http_Input::getParameter('p');
        $view=(int)Buddha_Http_Input::getParameter('view');
        $id=(int)Buddha_Http_Input::getParameter('id');

        if(!$id){
            Buddha_Http_Head::redirect('参数错误！',"index.php?a=more&c=demand&p={$p}&view={$view}");
        }
        $demand=$DemandObj->fetch($id);
        if(!$demand){
            Buddha_Http_Head::redirect('没有找到您要的信息！',"index.php?a=more&c=demand&p={$p}&view={$view}");
        }

        $buddhastatus=Buddha_Http_Input::getParameter('buddhastatus');
        $is_hot=Buddha_Http_Input::getParameter('is_hot');
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        $remarks=Buddha_Http_Input::getParameter('remarks');

        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['buddhastatus']=$buddhastatus;
            $data['is_hot']=$is_hot;
            $data['is_sure']=$is_sure;
            $data['remarks']=$remarks;
            $DemandObj->edit($data,$id);
            if($demand){
                Buddha_Http_Head::redirect('编辑成功！',"index.php?a=more&c=demand&p={$p}&view={$view}");
            }else{
                Buddha_Http_Head::redirect('编辑失败！',"index.php?a=more&c=demand&p={$p}&view={$view}");
            }
        }
        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>$demand['demand_desc'], 'width' => '100', 'height' => 500 )
            ));

        $demandcat=$DemandcatObj->goods_thumbgoods_thumb($demand['demandcat_id']);
        if($demandcat){
            $cat='';
            foreach($demandcat as $k=>$v){
                $cat.=$v['cat_name'].' > ';
            }
            $demand['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat);
        }
        $shop_name=$ShopObj->getSingleFiledValues(array('name'),"id='{$demand['shop_id']}'");
        if($shop_name){
            $demand['shop_name']=  $shop_name['name'];
        }

        $this->smarty->assign('demand',$demand);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

//        $DemandObj=new Demand();
        $id = Buddha_Http_Input::getParameter('id');
        $p = Buddha_Http_Input::getParameter('p');
        $view = Buddha_Http_Input::getParameter('view');

        list($hsk_adminsid, $hsk_adminuser, $hsk_adminpw, $hsk_admintime) = explode("\t",  Buddha_Tool_Password::cookieDecode(Buddha_Http_Cookie::getCookie('buddha_adminsid') , Buddha::$buddha_array['cookie_hash']));
        $uid = $hsk_adminsid;

        $UsercommonObj=new Usercommon();
        $Db_Usercommon = $UsercommonObj->photoalbumDel('moregallery',$this->tablename,$id,$uid,1);

        if($Db_Usercommon){
//            $DemandObj->deleteFIleOfPicture($id);
            Buddha_Http_Head::redirect('删除成功！',"index.php?a=more&c={$this->tablename}&p={$p}&view={$view}");
        }else{
            Buddha_Http_Head::redirect('删除失败！',"index.php?a=more&c={$this->tablename}&p={$p}&view={$view}");
        }
    }

}