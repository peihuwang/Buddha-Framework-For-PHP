<?php
/**
 * Class HtmlController
 */
class HtmlController extends Buddha_App_Action{

	public function __construct(){
		parent::__construct();
		$this->classname=__CLASS__;
		$this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
	}


	public function index()
    {
		$welcome = "我的第一个结合smarty显示的页面";
        $this->smarty->assign('welcome',$welcome);

		$TPL_URL = $this->c . '.' . __FUNCTION__;
		$this->smarty->display($TPL_URL . '.html');
		
	}


}