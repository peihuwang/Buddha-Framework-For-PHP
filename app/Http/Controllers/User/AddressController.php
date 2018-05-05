<?php
/**
 * Class 收货地址
 */

class AddressController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }


    /**
     * 收货地址列表
     */
    public function index(){
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
    	$AddressObj = new Address();
    	$RegionObj = new Region();
    	$addreinfo = $AddressObj->getFiledValues('',"uid={$uid}");//获取收货地址详情
    	foreach($addreinfo as $k => $v){
    		if($v['pro']){
    			$pro = $RegionObj->getSingleFiledValues(array('name'),"id={$v['pro']}");
    			$addreinfo[$k]['pro'] = $pro['name'];
    		}
    		if($v['city']){
    			$city = $RegionObj->getSingleFiledValues(array('name'),"id={$v['city']}");
    			$addreinfo[$k]['city'] = $city['name'];
    		}
    		if($v['area']){
    			$area = $RegionObj->getSingleFiledValues(array('name'),"id={$v['area']}");
    			$addreinfo[$k]['area'] = $area['name'];
    		}
    	}
    	$this->smarty->assign('addreinfo',$addreinfo);
    	$TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    /**
     * 添加收货地址
    **/
    public function add(){
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
    	$AddressObj = new Address();
    	$num = $AddressObj->countRecords("uid={$uid}");
    	if($_POST){
    		$realname = Buddha_Http_Input::getParameter('realname');
	    	$mobile = Buddha_Http_Input::getParameter('mobile');
	   		$regionstr = Buddha_Http_Input::getParameter('regionstr');
			$specticloc = Buddha_Http_Input::getParameter('specticloc');
			$pro = explode(',', $regionstr);
			$data['uid'] = $uid;
			$data['mobile'] = $mobile;
			$data['name'] = $realname;
			$data['pro'] = $pro[0];
			$data['city'] = $pro[1];
			$data['area'] = $pro[2];
			$data['address'] = $specticloc;
			if(!$num){
				$data['isdef'] = 1;
			}
			
			if($AddressObj->add($data)){
				$datas['isok'] = 'true';
			}else{
				$datas['isok'] = 'false';
			}
			Buddha_Http_Output::makeJson($datas);
    	}
    	
    	$TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    /***收货地址编辑****/
    public function edit()
    {
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
    	$id = Buddha_Http_Input::getParameter('id');
    	$AddressObj = new Address();
    	$RegionObj = new Region();
    	$addreinfo = $AddressObj->getSingleFiledValues('',"id={$id}");
    	if($addreinfo['pro']){//省
			$pro = $RegionObj->getSingleFiledValues(array('name'),"id={$addreinfo['pro']}");
			$addreinfo['addre'] = $pro['name'];
		}
		if($addreinfo['city']){//市
			$city = $RegionObj->getSingleFiledValues(array('name'),"id={$addreinfo['city']}");
			$addreinfo['addre'] .= '>'.$city['name'];
		}
		if($addreinfo['area']){//区县
			$area = $RegionObj->getSingleFiledValues(array('name'),"id={$addreinfo['area']}");
			$addreinfo['addre'] .= '>'.$area['name'];
		}
		if($_POST){//编辑收货地址
    		$realname = Buddha_Http_Input::getParameter('realname');
	    	$mobile = Buddha_Http_Input::getParameter('mobile');
	   		$regionstr = Buddha_Http_Input::getParameter('regionstr');
			$specticloc = Buddha_Http_Input::getParameter('specticloc');
			$pro = explode(',', $regionstr);
			$data['uid'] = $uid;
			$data['mobile'] = $mobile;
			$data['name'] = $realname;
			$data['pro'] = $pro[0];
			$data['city'] = $pro[1];
			$data['area'] = $pro[2];
			$data['address'] = $specticloc;
			$data['isdef'] = 1;
			if($AddressObj->edit($data,$id)){
				$datas['isok'] = 'true';
			}else{
				$datas['isok'] = 'false';
			}
			Buddha_Http_Output::makeJson($datas);
    	}
    	$this->smarty->assign('addreinfo',$addreinfo);
    	$TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    /**修改默认收货地址***/
    public function defadd(){
    	$id = Buddha_Http_Input::getParameter('addid');
    	$AddressObj = new Address();
    	$addressinfo = $AddressObj->getSingleFiledValues('',"id={$id}");
    	$data['isdef'] = 0;
    	$AddressObj->updateRecords($data,"uid={$addressinfo['uid']}");
    	$datas['isdef'] = 1;
    	$AddressObj->edit($datas,$id);
    }

    /***删除收货地址***/
    public function deladdress(){
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
    	$id = Buddha_Http_Input::getParameter('id');
    	$AddressObj = new Address();
    	$re = $AddressObj->del($id);//删除对应项
    	$num = $AddressObj->countRecords("isdef=1");
    	if(!$num){//如果没有默认自动添加一个默认地址
    		$addressinfo = $AddressObj->getSingleFiledValues(array('id'),"uid={$uid}");
	    	if($addressinfo){
	    		$data['isdef'] = 1;
	    		$AddressObj->edit($data,$addressinfo['id']);
	    	}
    	}
    	
    	if($re){
			$datas['isok'] = 'true';
		}else{
			$datas['isok'] = 'false';
		}
		Buddha_Http_Output::makeJson($datas);

    }


}