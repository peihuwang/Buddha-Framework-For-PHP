<?php

/**
 * Class RecruitController
 */
class RecruitController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $RecruitcatObj=new Recruitcat();
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $where = " (isdel=0 or isdel=4) and user_id='{$uid}'";
        if($keyword){
            $where.=" and recruit_name like '%$keyword%'";
        }
        $rcount = $this->db->countRecords( $this->prefix.'recruit', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by add_time DESC ";
        $fields=array('*');
        $list = $this->db->getFiledValues ($fields, $this->prefix.'recruit', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=recruit&', $pagesize );
        foreach ($list as $k => $v) {
            $Recruitcat = $RecruitcatObj->goods_thumbgoods_thumb($v['recruit_id']);
            if ($Recruitcat) {
                $cat_name = '';
                foreach ($Recruitcat as $k1 => $v1) {
                    $cat_name .= $v1['cat_name'] . ', ';
                }
            }
        $list[$k]['cat_name'] = Buddha_Atom_String::toDeleteTailCharacter($cat_name);
        }
        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function add(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $RecruitObj=new Recruit();
        $RecruitcatObj=new Recruitcat();
        $num=$ShopObj->countRecords("isdel=0 and state=0 and is_sure=1 and user_id='{$uid}'");
        if($num==0){
            Buddha_Http_Head::redirect('您还没用创建店铺，或者店铺还未通过审核！','index.php?a=index&c=shop',2);
        }

        $recruit_name = Buddha_Http_Input::getParameter('recruit_name');
        $recruit_id = Buddha_Http_Input::getParameter('recruit_id');
        $pay = Buddha_Http_Input::getParameter('pay');
        $education = Buddha_Http_Input::getParameter('education');
        $work = Buddha_Http_Input::getParameter('work');
        $recruit_start_time = Buddha_Http_Input::getParameter('recruit_start_time');
        $recruit_end_time = Buddha_Http_Input::getParameter('recruit_end_time');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $treatment = Buddha_Http_Input::getParameter('treatment');
        $number = Buddha_Http_Input::getParameter('number');
        $contacts = Buddha_Http_Input::getParameter('contacts');
        $tel = Buddha_Http_Input::getParameter('tel');
        $recruit_desc = Buddha_Http_Input::getParameter('content');


        if(Buddha_Http_Input::isPost()) {

            $Db_level = $ShopObj->getSingleFiledValues(array('level0', 'level1', 'level2', 'level3'), "user_id='{$uid}' and id='{$shop_id}' and isdel=0");
            $data = array();
            $data['recruit_name'] = $recruit_name;
            $data['user_id'] = $uid;
            $data['recruit_id'] = $recruit_id;
            $data['shop_id'] = $shop_id;
            $data['pay'] = $pay;
            $data['education'] = $education;
            $data['work'] = $work;
            $data['treatment'] = $treatment;
            $data['number'] = $number;
            $data['contacts'] = $contacts;
            $data['tel'] = $tel;
            $data['level0'] = $Db_level['level0'];
            $data['level1'] = $Db_level['level1'];
            $data['level2'] = $Db_level['level2'];
            $data['level3'] = $Db_level['level3'];
            $data['recruit_start_time'] = strtotime($recruit_start_time);
            $data['recruit_end_time'] = strtotime($recruit_end_time);
            $data['recruit_desc'] = $recruit_desc;
            $data['add_time'] = Buddha::$buddha_array['buddha_timestamp'];


            $Recruit_id = $RecruitObj->add($data);
            if ($Recruit_id) {
                Buddha_Http_Output::makeValue(1);
            }else{
                Buddha_Http_Output::makeValue(0);
            }
        }



        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>'', 'width' => '100', 'height' => 500 )
            ));
        $getCateOption=$RecruitcatObj->getOption();
        $gettableOption=$RecruitObj->Recruitment_Qualifications();
        $gettableOption1=$RecruitObj->work_experience();
        $getshoplistOption=$ShopObj->getShoplistOption($uid,0);
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('gettableOption1', $gettableOption1);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('getCateOption', $getCateOption);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $RecruitObj=new Recruit();
        $ShopObj=new Shop();
        $RecruitcatObj=new Recruitcat();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirect('参数错误','index.php?a=index&c=demand');
        }
        $recruit=$RecruitObj->fetch($id);
        if(!$recruit){
            Buddha_Http_Head::redirect('信息不存在','index.php?a=index&c=demand');
        }
        $recruit_name = Buddha_Http_Input::getParameter('recruit_name');
        $recruit_id = Buddha_Http_Input::getParameter('recruit_id');
        $pay = Buddha_Http_Input::getParameter('pay');
        $education = Buddha_Http_Input::getParameter('education');
        $work = Buddha_Http_Input::getParameter('work');
        $recruit_start_time = Buddha_Http_Input::getParameter('recruit_start_time');
        $recruit_end_time = Buddha_Http_Input::getParameter('recruit_end_time');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $treatment = Buddha_Http_Input::getParameter('treatment');
        $number = Buddha_Http_Input::getParameter('number');
        $contacts = Buddha_Http_Input::getParameter('contacts');
        $tel = Buddha_Http_Input::getParameter('tel');
        $recruit_desc = Buddha_Http_Input::getParameter('content');

        if(Buddha_Http_Input::isPost()) {
            $Db_level = $ShopObj->getSingleFiledValues(array('level0', 'level1', 'level2', 'level3'), "user_id='{$uid}' and id='{$shop_id}' and isdel=0");
            $data = array();
            $data['recruit_name'] = $recruit_name;
            $data['user_id'] = $uid;
            $data['recruit_id'] = $recruit_id;
            $data['shop_id'] = $shop_id;
            $data['pay'] = $pay;
            $data['education'] = $education;
            $data['work'] = $work;
            $data['treatment'] = $treatment;
            $data['number'] = $number;
            $data['contacts'] = $contacts;
            $data['tel'] = $tel;
            $data['level0'] = $Db_level['level0'];
            $data['level1'] = $Db_level['level1'];
            $data['level2'] = $Db_level['level2'];
            $data['level3'] = $Db_level['level3'];
            $data['recruit_start_time'] = strtotime($recruit_start_time);
            $data['recruit_end_time'] = strtotime($recruit_end_time);
            $data['recruit_desc'] = $recruit_desc;
            $data['add_time'] = Buddha::$buddha_array['buddha_timestamp'];

            $RecruitObj->edit($data,$id);
            if ($RecruitObj) {
                Buddha_Http_Output::makeValue(1);
            }else{
                Buddha_Http_Output::makeValue(0);
            }
        }

        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>$recruit['recruit_desc'], 'width' => '100', 'height' => 500 )
            ));
        $getCateOption=$RecruitcatObj->getOption($recruit['recruit_id']);
        $gettableOption=$RecruitObj->Recruitment_Qualifications($recruit['education']);
        $gettableOption1=$RecruitObj->work_experience($recruit['work']);
        $getshoplistOption=$ShopObj->getShoplistOption($uid,$recruit['shop_id']);
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('gettableOption1', $gettableOption1);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('getCateOption', $getCateOption);
        $this->smarty->assign('recruit', $recruit);


        //消息置顶
        $OrderObj=new Order();
        $Top=$OrderObj->getFiledValues(array('final_amt','createtime'),"isdel=0 and good_id='{$id}' and order_type='info.top' and pay_status=1 and user_id='{$uid}'");
        if(count($Top)>0){
            foreach ($Top as $k=>$v){
                $Top[$k]['name']=$recruit['recruit_name'];
            }
        }
        $this->smarty->assign('Top', $Top);

        $infotop=array('id'=>$recruit['id'],'good_table'=>'recruit','order_type'=>'info.top','final_amt'=>'0.2','pc'=>'1');
        $this->smarty->assign('infotop', $infotop);


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        $RecruitObj=new Recruit();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $RecruitObj->del($id);
        if($RecruitObj){
            Buddha_Http_Head::redirect('删除成功','index.php?a=index&c=recruit');
        }else{
            Buddha_Http_Head::redirect('删除失败','index.php?a=index&c=recruit');
        }
    }

    public function auditfailure(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $RecruitObj=new Recruit();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $num=$RecruitObj->countRecords("id='{$id}' and user_id='{$uid}' and isdel=0 and is_sure=4");
        if($num==0){
            $data=array(
                'errcode'=>'1',
                'errmsg'=>'err',
                'data'=>'数据错误，联系管理员',
            );
            Buddha_Http_Output::makeJson($data);
        }
        $remarks= $RecruitObj->getSingleFiledValues(array('remarks'),"id='{$id}' and user_id='{$uid}' and isdel=0 and is_sure=4");

        $data=array(
            'data'=>$remarks['remarks'],
            'errcode'=>'0',
            'errmsg'=>'ok',
        );
        Buddha_Http_Output::makeJson($data);
    }


    //查询订单是否成功
    public function infosee(){
        $OrderObj=new Order();
        $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $good_id=Buddha_Http_Input::getParameter('id');
        $good_table=Buddha_Http_Input::getParameter('good_table');
        if(!$user_id){
            $jsondata = array();
            $jsondata['url'] = 'index.php?a=login&c=account';
            $jsondata['errcode'] = 1;
            $jsondata['errmsg'] = "请登陆";
            Buddha_Http_Output::makeJson($jsondata);
        }
        $startstr = date('Y-m-d',time());
        $start= strtotime($startstr);
        $end=time()+600;
        $Db_orderunm= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$good_id}' and good_table='{$good_table}' and pay_status=1 and createtime>$start and createtime<=$end order by createtime DESC" );
        if($Db_orderunm){
            $jsondata = array();
            $jsondata['url'] = 'index.php?a=detailed&c='.$good_table;
            $jsondata['errcode'] = 0;
            $jsondata['errmsg'] ='ok';
            Buddha_Http_Output::makeJson($jsondata);
        }
    }

}