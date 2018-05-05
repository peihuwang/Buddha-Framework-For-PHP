<?php

/**
 * Created by PhpStorm.
 * User: TinyStar
 * Date: 2017/1/3
 * Time: 8:52
 */
class Sakyamuni
{
    protected static $_instance;
    private $param = array();
    public static $buddha_timestamp;
    public static $buddha_array = array();

    /**
     * 获取实例
     *
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @return array
     */
    public function getParam()
    {
        return $this->param;
    }

    public function __construct()
    {

        date_default_timezone_set('PRC');

        $nowtime = time();


        self::$buddha_array['buddha_tencent_key'] = '7HBBZ-R2J6V-UBQPX-UZZFY-H7XF3-5RF4H';
        self::$buddha_array['buddha_timestamp'] = $nowtime;
        self::$buddha_array['buddha_timestr'] = date('Y-m-d H:i:s', $nowtime);
        self::$buddha_array['cookie_pre'] = 'buddha';
        self::$buddha_array['cookie_domain'] = '';
        self::$buddha_array['cookie_path'] = '/';
        self::$buddha_array['cookie_hash'] = 'sakyamuni';
        self::$buddha_array['qrcodedomain'] = 'http://api.bendishangjia.com/';

        //upload
        self::$buddha_array['upload_maxsize'] = '900000000';
        $cache = '';
        @include_once PATH_ROOT . 'bootstrap/cache/cache_config.php';
        self::$buddha_array['cache'] = $cache;
        self::$buddha_array['page']['pagesize'] = '15';


        //获取当前 访问地址
        $_HSKENV = array();
        $_HSKENV['PHP_SELF'] = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $_HSKENV['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_HSKENV['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
        $sHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? (int)$_SERVER['SERVER_NAME'] : (int)getenv('SERVER_NAME'));
        $sPort = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : (int)getenv('SERVER_PORT');
        $sSecure = (isset($_SERVER['HTTPS']) || $sPort == 433) ? 1 : 0;
        $sDir = trim(dirname($_HSKENV['PHP_SELF']));
        $sDir = substr($sDir, 0, strrpos($sDir, '/') + 1);
        $hsk_siteurl = 'http' . (isset($_SERVER['HTTPS']) || $sPort == 433 ? 's' : '') . '://' . preg_replace('~[\\\\/]{2,}~i', '/', $sHost . ($sPort && (!$sSecure && $sPort != 80 || $sSecure && $sPort != 433) && strpos($sHost, ':') === FALSE ? ':' . $sPort : '') . $sDir);
        $hsk_siteurl = str_replace('\\', '/', $hsk_siteurl);
        self::$buddha_array['host'] = $hsk_siteurl;


    }

    public function init()
    {
        // error reporting - all errors for development (ensure you have
        // display_errors = On in your php.ini file)
        //error_reporting ( E_ALL | E_STRICT );
        error_reporting(E_ALL ^ E_NOTICE);
        header("Content-type: text/html; charset=utf-8");
        mb_internal_encoding('UTF-8');


        if (!defined('TPL_DIR')) define('TPL_DIR', 'front'); //模板路径
        //registe classes
        spl_autoload_register(array($this, 'loadAutoClass'));
        $this->param = Buddha_Run_Router::getInstance()->init(Buddha::getUrlConfig(include PATH_ROOT . 'bootstrap/Buddha/config/config.ini.php'))->makeUrl();

        return $this;
    }

    public function startup()
    {
        $param = $this->param;

        if (substr(TPL_DIR, 0, 2) == 'pc') {
            $SharedataOjb = new Sharedata();
            $SharedataOjb->head();
            $SharedataOjb->userhead();
            $SharedataOjb->footer();

        }

        if (TPL_DIR != 'NONE') {
            $controller = $param['controller'] ? $param['controller'] : 'index';
            $getMVC = ucfirst($controller) . 'Controller';
            $Controller = new $getMVC();
            $action = $param['action'] ? $param['action'] : 'index';
            $cls_methods = get_class_methods($getMVC);
            if (!in_array($action, $cls_methods)) exit($action . ' not exist');
            $Controller->$action();

        }


    }


    public function  import($classdir)
    {

        $c = preg_replace('/\./', '/', $classdir);
        require_once(PATH_ROOT . $c . '.class.php');
        return $this;

    }

    public static function convertToChinese($str)
    {

        $returnarr = array(
            'index' => '后台', 'login' => '登录', 'dologin' => '尝试登录',
            'add' => '添加', 'del' => '删除', 'edit' => '编辑', 'view' => '查看', 'more' => '列表',
            'log' => '系统日志',


        );

        if (array_key_exists($str, $returnarr)) {
            return $returnarr[$str];
        } else {
            return $str;
        }
    }

    public static function getDatabaseConfig()
    {
        $databaseconfig = include PATH_ROOT . 'bootstrap/Buddha/config/config.ini.php';
        return $databaseconfig['database'];
    }

    public static function getSmartyConfig()
    {
        $databaseconfig = include PATH_ROOT . 'bootstrap/Buddha/config/config.ini.php';
        return $databaseconfig['smarty'];
    }

    public static function getUrlConfig()
    {
        $databaseconfig = include PATH_ROOT . 'bootstrap/Buddha/config/config.ini.php';
        return $databaseconfig['url'];
    }

    public static function getSiteConfig()
    {
        $databaseconfig = include PATH_ROOT . 'bootstrap/Buddha/config/config.ini.php';
        return $databaseconfig['site'];
    }

    /**
     * Class loader.
     */
    public function loadAutoClass($class)
    {

        $_class = strtolower($class);


        if (strpos($_class, 'uddha')) {
            $exarr = explode('_', $class);
            require_once PATH_ROOT . 'bootstrap/' . implode("/", $exarr) . '.class.php';


        }

        if (strpos($_class, 'smarty') !== 0 and strpos($_class, 'phpexcel') !== 0
            and strpos($_class, 'buddha') !== 0 and strpos($_class, 'lt') !== 0 and strpos($_class, 'aop') !== 0
            and strpos($_class, 'alipay') !== 0
        ) {

            $classes = Buddha_Run_Urils::getClasses(PATH_ROOT);
            if (!array_key_exists($class, $classes)) {
                die ('Class "' . $class . '" not found.');
            }
            require_once $classes [$class];

        }


    }

    public function run($obj)
    {
        return $obj;
    }

    public function loadClass($className)
    {

        if (file_exists(PATH_ROOT . 'bootstrap/Buddha/' . $className . '/' . $className . '.class.php')) {
            include_once(PATH_ROOT . 'bootstrap/Buddha/' . $className . '/' . $className . '.class.php');
            return $this;
        } else {
            exit('no file');
        }
    }

    /**
     * Configures an object with the initial property values.
     * @param object $object the object to be configured
     * @param array $properties the property initial values given in terms of name-value pairs.
     * @return object the object itself
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    public static function getRandom($param)
    {
        $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = "";
        for ($i = 0; $i < $param; $i++) {
            $key .= $str{mt_rand(0, 32)};    //生成php随机数
        }
        return $key;
    }

    public static function birthToken($str)
    {

        //return md5(date('Y-m-d',time()).':'.$str);
        return md5(date('Y', time()) . ':' . $str);
    }

    public static function getAgeByID($id)
    {

        //过了这年的生日才算多了1周岁
        if (empty($id)) return '';
        $date = strtotime(substr($id, 6, 8));
        //获得出生年月日的时间戳
        $today = strtotime('today');
        //获得今日的时间戳
        $diff = floor(($today - $date) / 86400 / 365);
        //得到两个日期相差的大体年数

        //strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age = strtotime(substr($id, 6, 8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;

        return $age;
    }

}