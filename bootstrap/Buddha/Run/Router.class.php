<?php
/**
 * 框架路由类
 *
 * @author tinystar
 */
class Buddha_Run_Router {
    private static $__instance = null;# 类中的私有成员：静态变量
    private $url_mode;
    private $var_controller;
    private $var_action;
    private $var_module;


    public static function getInstance(){
        if(self::$__instance instanceof self)
            return self::$__instance; # 给静态变量赋值
        return new self();
    }

    /**
     * 初始化方法
     * @param type $config
     */
     public function init($config) {
        $this->url_mode = $config['URL_MODE'];
         $this->var_controller = $config['VAR_CONTROLLER'];
         $this->var_action = $config['VAR_ACTION'];
         $this->var_module = $config['VAR_MODULE'];
         return $this;
    }
 
    /**
     * 获取url打包参数
     * @return type
     */
     public function makeUrl() {


        switch ($this->url_mode) {
            //动态url传参 模式
            case 0:
                return $this->getParamByDynamic();
                break;
            //pathinfo 模式
            case 1:
                return $this->getParamByPathinfo();
                break;
        }
    }
 
    /**
     * 获取参数通过url传参模式
     */
public function getParamByDynamic() {
        $arr = empty($_SERVER['QUERY_STRING']) ? array() : explode('&', $_SERVER['QUERY_STRING']);



        $data = array(
            'module' => '',
            'controller' => '',
            'action' => '',
            'param' => array()
        );
        if (!empty($arr)) {
            $tmp = array();
            $part = array();
            foreach ($arr as $v) {
                $tmp = explode('=', $v);
                $tmp[1] = isset($tmp[1]) ? trim($tmp[1]) : '';
                $part[$tmp[0]] = $tmp[1];
            }




            if (isset($part[$this->var_module])) {
                $data['module'] = $part[$this->var_module];
                unset($part[$this->var_module]);
            }
            if (isset($part[$this->var_controller])) {
                $data['controller'] = $part[$this->var_controller];
                unset($part[$this->var_controller]);
            }
            if (isset($part[$this->var_action])) {
                $data['action'] = $part[$this->var_action];
                unset($part[$this->var_action]);
            }
            if (isset($part['Services'])) {
                $Services_arr = explode('.',$part['Services']);

                $data['action']=$Services_arr[1];
                unset($part[$this->var_module]);
                $data['controller']=$Services_arr[0];
                unset($part[$this->var_controller]);

            }


            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    unset($_GET[$this->var_controller], $_GET[$this->var_action], $_GET[$this->var_module]);
                    $data['param'] = array_merge($part, $_GET);
                    //unset($_GET);
                    break;
                case 'POST':
                    unset($_POST[$this->var_controller], $_POST[$this->var_action], $_GET[$this->var_module]);
                    $data['param'] = array_merge($part, $_POST);
                   // unset($_POST);
                    break;
                case 'HEAD':
                    break;
                case 'PUT':
                    break;
            }
        }
        return $data;
    }
 
    /**
     * 获取参数通过pathinfo模式
     */
    public  function getParamByPathinfo() {
        $part = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $data = array(
            'module' => '',
            'controller' => '',
            'action' => '',
            'param' => array()
        );
        if (!empty($part)) {
            krsort($part);
            $data['module'] = array_pop($part);
            $data['controller'] = array_pop($part);
            $data['action'] = array_pop($part);
            ksort($part);
            $part = array_values($part);
            $tmp = array();
            if (count($part) > 0) {
                foreach ($part as $k => $v) {
                    if ($k % 2 == 0) {
                        $tmp[$v] = isset($part[$k + 1]) ? $part[$k + 1] : '';
                    }
                }
            }
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    unset($_GET[$this->var_controller], $_GET[$this->var_action]);
                    $data['param'] = array_merge($tmp, $_GET);
                    unset($_GET);
                    break;
                case 'POST':
                    unset($_POST[$this->var_controller], $_POST[$this->var_action]);
                    $data['param'] = array_merge($tmp, $_POST);
                    unset($_POST);
                    break;
                case 'HEAD':
                    break;
                case 'PUT':
                    break;
            }
        }
        return $data;
    }
 
}