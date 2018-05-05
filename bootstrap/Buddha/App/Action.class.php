<?php

class Buddha_App_Action
{
    protected $db;
    protected $prefix;
    protected $smarty;
    protected $classname;
    protected $c;

    public function __construct()
    {
        $this->db = Buddha_Driver_Db::getInstance(
            Buddha::getDatabaseConfig()
        );
        $this->prefix = $this->db->getPrefix();
        $this->smarty = Smarty::getInstance(
            Buddha::getSmartyConfig()
        );
        $this->classname = __CLASS__;
    }

    public function index(){
       echo "Welcome To Visit ". $this->c.'.'.__FUNCTION__;
    }
    public function captcha(){

        $builder = Buddha_Captcha_CaptchaBuilder::create(null,null,4)->setIgnoreAllEffects(true)
            ->build();
       $_SESSION['buddha_checkcode'] = $builder->getPhrase();
        ob_clean();
        header('Content-type: image/jpeg');
        $builder->output();



}

}