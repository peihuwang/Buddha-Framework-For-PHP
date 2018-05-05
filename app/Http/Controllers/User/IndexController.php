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



$TPL_URL = __FUNCTION__;


$_COOKIE["lang"]=isset($_REQUEST['lang'])?$_REQUEST['lang']:''
		;


	$this->smarty->assign('subject', "标题");

$welcome = "Welcome To My Brain Program";
$this->smarty->assign('welcome', $welcome);


$this->smarty -> display($TPL_URL.'.html');

	}
	public static function getClassName(){
		return __CLASS__;
	}

	public  function phpinfo(){
		echo phpinfo();
	}

	
}