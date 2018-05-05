<?php

/**
 * Class JumpController
 */
class JumpController extends Buddha_App_Action{

    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }

    public function index()
    {
        $keyvalud = Buddha_Http_Input::getParameter('keyvalud');
        $keyvalud = Buddha_Tool_Password::decrypt($keyvalud,'D','hacker');
        $url = "http://www.bendishangjia.com/index.php?{$keyvalud}";
        if (isset($url))
        {
            Header("HTTP/1.1 303 See Other");
            Header("Location: $url");
            exit;
        }
    }




}