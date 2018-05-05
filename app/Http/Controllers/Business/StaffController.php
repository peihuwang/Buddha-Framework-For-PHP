<?php
/**
 * Class StaffController
 */
class StaffController extends Buddha_App_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserObj = new User();
        $StaffObj = new Staff();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if($uid){
        	$staffinfo = $StaffObj->getFiledValues(array('id','staff_id'),"boss_id='{$uid}' AND state=1");
        	if($staffinfo){
        		foreach ($staffinfo as $k => $v) {
        			$stafflist[$k] = $UserObj->getSingleFiledValues(array('username,realname,mobile'),"id='{$v['staff_id']}'");
        			$stafflist[$k]['id'] = $v['id'];

        		}
        		$this->smarty->assign('stafflist',$stafflist);
        	}
	        
        }
        $act = Buddha_Http_Input::getParameter('act');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        if($act == 'list' && $keyword){
        	$page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
	        $pagesize = Buddha::$buddha_array['page']['pagesize'];
	        //$uid = $UserObj->getSingleFiledValues(array('id'),"mobile='{$keyword}'");
	        $where = " isdel=0 AND mobile='{$keyword}' ";
	        $rcount = $this->db->countRecords( $this->prefix.'user', $where);
	        $orderby = " ORDER BY id DESC ";
	        $userlist = $UserObj->getFiledValues(array('id','username','realname','mobile'),$where . $orderby);
	        if($userlist){
	        	$data['isok'] = 'true';
	        	$data['data'] = $userlist;
	        }else{
	        	$data['isok'] = 'true';
	        	$data['data'] = '输入的手机号不正确，或是此手机号还没注册会员';
	        }
	        Buddha_Http_Output::makeJson($data);
        }
        
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    function del(){
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserObj = new User();
        $StaffObj = new Staff();
        $id = Buddha_Http_Input::getParameter('id');
        if($id){
        	$num = $StaffObj->countRecords("boss_id='{$uid}'");
        	if($num){
        		$StaffObj->delRecords("id='{$id}'");
        		$data['isok'] = 1;
        		$data['info'] = '操作成功！';
        	}else{
        		$data['isok'] = 0;
        		$data['info'] = '服务器忙！';
        	}
        }else{
        	$data['isok'] = 0;
        	$data['info'] = '服务器忙！';
        }
        Buddha_Http_Output::makeJson($data);

    }

    function add(){
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserObj = new User();
        $StaffObj = new Staff();
        $id = Buddha_Http_Input::getParameter('id');
        if($id){
        	$num = $StaffObj->countRecords("staff_id='{$id}'");
        	if($id == $uid){
        		$data['isok'] = 0;
        		$data['info'] = '不能添加自己为员工';
        		Buddha_Http_Output::makeJson($data);
        	}
        	if($num){
        		$data['isok'] = 0;
        		$data['info'] = '已存在员工，不能重复添加';
        		Buddha_Http_Output::makeJson($data);
        	}else{
        		$staff['boss_id'] = $uid;
	        	$staff['staff_id'] = $id;
	        	$staff['ceratetime'] = Buddha::$buddha_array['buddha_timestamp'];
	        	$staff['ceratetimestr'] = Buddha::$buddha_array['buddha_timestr'];
	        	$staff_id = $StaffObj->add($staff);
	        	if($staff_id){
	        		$data['isok'] = 1;
	        		$data['info'] = '操作成功！';
	        	}else{
	        		$data['isok'] = 0;
	        		$data['info'] = '服务器忙！';
	        	}
	        	Buddha_Http_Output::makeJson($data);
        	}
        	
        }
        
    }








}