<?php
class BankController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }

    public function addbank(){//绑定银行卡
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
    	$BankObj = new Bank();
    	$number=Buddha_Http_Input::getParameter('number');//获取POST数据
    	$username=Buddha_Http_Input::getParameter('username');
    	$bankname=Buddha_Http_Input::getParameter('bankname');
    	$openbank=Buddha_Http_Input::getParameter('openbank');
    	$act=Buddha_Http_Input::getParameter('act');
    	if($act == 'add'){
    		$data['uid'] = $uid;
	    	$data['name'] = $username;
	    	$data['carenum'] = $number;
	    	$data['bankname'] = $bankname;
	    	$data['openbank'] = $openbank;
	    	$data['addtime'] = time();
	    	$data['addtimestr'] = date('Y-m-d H:i:s');
	    	if($BankObj->add($data)){//写入数据库
	    		$datas['isok'] = 'true';
	    		$datas['info'] = '添加成功!';
	    	}else{
	    		$datas['isok'] = 'false';
	    		$datas['info'] = '服务器忙!';
	    	}
	    	Buddha_Http_Output::makeJson($datas);
    	}
    	
    	$TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function getbankname(){//根据填写的卡号获取对用的银行名称
    	require('bankList.php');
    	$number=Buddha_Http_Input::getParameter('number');
    	$banknames = $this->bankInfo($number,$bankList);
    	$banknames = explode('-',$banknames);
    	$num = count($banknames);
    	$bankname = $banknames[0].$banknames[$num-1];
    	if($bankname){
    		$data['isok'] = 'true';
    		$data['info'] = $bankname;
    	}else{
    		$data['isok'] = 'false';
    		$data['info'] = '您输入的银行卡号不正确，请您重新输入！';
    	}
    	Buddha_Http_Output::makeJson($data);
    	$TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function bankInfo($card,$bankList){//获取银行name function
		$card_8 = substr($card, 0, 8); 
		if (isset($bankList[$card_8])){ 
		    return $bankList[$card_8]; 
		} 
		$card_6 = substr($card, 0, 6); 
		if (isset($bankList[$card_6])){ 
		    return $bankList[$card_6];  
		} 
		$card_5 = substr($card, 0, 5); 
		if (isset($bankList[$card_5])){ 
		    return $bankList[$card_5];   
		}
		$card_4 = substr($card, 0, 4); 
		if (isset($bankList[$card_4])){ 
		    return $bankList[$card_4];   
		} 
	}
	public function editbank(){//编辑绑定的银行卡
		list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
		$id=Buddha_Http_Input::getParameter('id');
    	$BankObj = new Bank();
    	$bankinfo = $BankObj->getSingleFiledValues('',"id={$id}");
    	$this->smarty->assign('bankinfo',$bankinfo);
    	$number=Buddha_Http_Input::getParameter('number');//获取POST数据
    	$username=Buddha_Http_Input::getParameter('username');
    	$bankname=Buddha_Http_Input::getParameter('bankname');
    	$openbank=Buddha_Http_Input::getParameter('openbank');
    	$act=Buddha_Http_Input::getParameter('act'); 	
    	if($act == 'edit'){
	    	$data['name'] = $username;
	    	$data['carenum'] = $number;
	    	$data['bankname'] = $bankname;
	    	$data['openbank'] = $openbank;
	    	$data['addtime'] = time();
	    	$data['addtimestr'] = date('Y-m-d H:i:s');
	    	$re = $BankObj->edit($data,$id);
	    	if($re){//写入数据库
	    		$datas['isok'] = 'true';
	    		$datas['info'] = '修改成功!';
	    	}else{
	    		$datas['isok'] = 'false';
	    		$datas['info'] = '服务器忙!';
	    	}
	    	Buddha_Http_Output::makeJson($datas);
    	}
    	$TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
	}
	public function delbank(){
		$id=Buddha_Http_Input::getParameter('id');
    	$BankObj = new Bank();
    	$re = $BankObj->del($id);
    	if($re){//写入数据库
	    		$datas['isok'] = 'true';
	    		$datas['info'] = '删除成功!';
	    	}else{
	    		$datas['isok'] = 'false';
	    		$datas['info'] = '服务器忙!';
	    	}
	    	Buddha_Http_Output::makeJson($datas);
    	$TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
	}
}