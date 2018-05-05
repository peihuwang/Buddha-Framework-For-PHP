<?php

/**
 * Class IndexController
 */
class IndexController extends Buddha_App_Action{

	public function __construct(){
		parent::__construct();
		$this->classname=__CLASS__;
		$this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

	}

	public function index(){
		$RegionObj = new Region();
        $ShopObj=new Shop();
		$locdata = $RegionObj->getLocationDataFromCookie();

		$url=__FUNCTION__;
		$this->smarty->assign('pname',$url);
        $storetype=$ShopObj->getstoretypeindex();
        $this->smarty->assign('storetype',$storetype);
		 $TPL_URL = __FUNCTION__;
		$this->smarty->display($TPL_URL . '.html');
	}



	
}