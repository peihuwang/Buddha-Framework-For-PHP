<?php
/**
 * Class RechargeController
 *商家充值列表
 */
class RechargeController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }
    function more(){
    	/******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/
        $UserObj = new User();
        $Recharge = new Recharge();
        $p=(int)Buddha_Http_Input::getParameter('p');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $where = " 1=1 ";
        //充值总额
        $Db_recharge = $this->db->query("select sum(total_amount) as total  from {$this->prefix}recharge where 1=1 ")->fetchAll(PDO::FETCH_ASSOC);
        $this->smarty->assign('moneyin', $Db_recharge[0]['total']);

        if($keyword){
            $where.=" realname like '%$keyword%' ";
            $params['keyword'] = $keyword;
        }
        $sql ="select count(*) as total  from {$this->prefix}recharge where {$where} ";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount =$count_arr[0]['total'];
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by id DESC ";
        $limit =Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

        $sql ="select * from {$this->prefix}recharge where {$where} {$orderby}  {$limit}";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);;

        foreach($list as $k=>$v){
            $user_id  = $v['uid'];
            if($user_id){
                $Db_userinfo = $UserObj->getSingleFiledValues(array('username','realname','mobile')," id='{$user_id}' ");
                if($Db_userinfo['realname']){
                	$list[$k]['realname']=$Db_userinfo['realname'];
                }else{
                	$list[$k]['realname']=$Db_userinfo['username'];
                }
                $list[$k]['mobile']=$Db_userinfo['mobile'];
            }
        }
        @$strPages =  Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=recharge&' .http_build_query($params).'&', $pagesize);
        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign( 'keyword', $keyword );
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
}