<?php

/**
 * Class BillController
 */
class BillController extends Buddha_App_Action{


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

        $OrderObj = new Order();
        $UserObj = new User();
        $BillObj = new Bill();
        $RegionObj = new Region();


        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 100;
        $p=(int)Buddha_Http_Input::getParameter('p');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        /*$searchType = array (100 => '总账单',2 => '代理商账单',3 => '合伙人账单',4 => '普通会员账单',99 => '平台账单',1 => '商家账单');
        if($view == 2 || $view == 5 || $view == 6 || $view == 7){*/
            $searchType = array (100 => '总账单',2 => '代理商账单',3 => '合伙人账单',4 => '普通会员账单','5'=>'省代理','6'=>'市代理','7'=>'区县代理',99 => '平台账单',1 => '商家账单');
        //}
        $where = " bill.isdel=0 ";
        if($view) {
            $params['view'] = $view;
            switch ($view) {

                case 1:
                    $where .= " and u.groupid=1 "  ;
                    break;

                case 2:
                    $where .= " and u.groupid=2 "  ;
                    break;

                case 3:
                    $where .= " and u.groupid=3 "  ;
                    break;

                case 4:
                    $where .= " and u.groupid=4 "  ;
                    break;
                case 5:
                    $regions = $RegionObj->getSingleFiledValues(array('id'),"fullname  LIKE '%{$keyword}%'");
                    $where .= " and u.groupid=2 and u.level1={$regions['id']} ";
                    break;
                case 6:
                    $regions = $RegionObj->getSingleFiledValues(array('id'),"fullname  LIKE '%{$keyword}%'");
                    $where .= " and u.groupid=2 and u.level2={$regions['id']} ";
                    break;
                case 7:
                    $regions = $RegionObj->getSingleFiledValues(array('id'),"fullname  LIKE '%{$keyword}%'");
                    $where .= " and u.groupid=2 and u.level3={$regions['id']} ";
                    break;

                case 99:
                   $where .= " and bill.user_id=0 " ;
                    break;
            }
        }

        //平台收入
        $Db_Bill = $this->db->query("select sum(bill.billamt) as total  from {$this->prefix}bill as bill left join {$this->prefix}user as u
    on bill.user_id = u.id
    where {$where} and bill.orient='+' ")->fetchAll(PDO::FETCH_ASSOC);
        $this->smarty->assign('moneyin', $Db_Bill[0]['total']);

        $Db_Bill = $this->db->query("select sum(bill.billamt) as total  from {$this->prefix}bill as bill left join {$this->prefix}user as u
    on bill.user_id = u.id
    where {$where} and bill.orient='-' ")->fetchAll(PDO::FETCH_ASSOC);
        $this->smarty->assign('moneyout', $Db_Bill[0]['total']);

        if($keyword && $view !=5 && $view !=6 && $view !=7){
            $where.=" and (bill.order_sn like '%$keyword%' or  u.mobile like '%$keyword%' or  u.realname like '%$keyword%' )";
            $params['keyword'] = $keyword;
        }else{
            $params['keyword'] = $keyword;
        }


        $level = Buddha_Http_Input::getParameter('level');
        if(strlen($level)){
            $where.=" and  ( u.level1='{$level}' or  u.level2='{$level}' or  u.level3='{$level}')  ";
        }




          $sql ="select count(*) as total  from {$this->prefix}bill as bill left join {$this->prefix}user as u
    on bill.user_id = u.id
    where {$where} ";


        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

         $rcount =$count_arr[0]['total'];

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by bill.id DESC ";
        $limit =Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

         $sql ="select bill.billamt,bill.order_type,bill.order_sn,bill.createtime,bill.user_id,
               u.mobile,u.username,u.realname,u.level1,u.level2,u.level3

	 from {$this->prefix}bill as bill left join {$this->prefix}user as u
    on bill.user_id = u.id


    where {$where} {$orderby}  {$limit}
";



        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);;

        foreach($list as $k=>$v){
            $user_id  = $v['user_id'];
            $level1  = $v['level1'];
            $level2  = $v['level2'];
            $level3  = $v['level3'];
            if($user_id and $level1){
                $Db_Region = $RegionObj->getSingleFiledValues(array('name')," id='{$level1}' ");
                $list[$k]['level1_name']=$Db_Region['name'];
            }
            if($user_id and $level2){
                $Db_Region = $RegionObj->getSingleFiledValues(array('name')," id='{$level2}' ");
                $list[$k]['level2_name']=$Db_Region['name'];
            }
            if($user_id and $level3){
                $Db_Region = $RegionObj->getSingleFiledValues(array('name')," id='{$level3}' ");
                $list[$k]['level3_name']=$Db_Region['name'];
            }

        }



        $strPages =  Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=bill&' .http_build_query($params).'&', $pagesize);

        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign('view',$view);

        $this->smarty->assign('searchType',$searchType);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign( 'keyword', $keyword );


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }




}