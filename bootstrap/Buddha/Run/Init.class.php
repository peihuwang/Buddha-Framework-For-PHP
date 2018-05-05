<?php
/**
 * 框架路由类
 *
 * @author tinystar
 */
class Buddha_Run_Init {
    /**
     * System config.
     */
    public function init() {
        // error reporting - all errors for development (ensure you have
        // display_errors = On in your php.ini file)
        error_reporting ( E_ALL | E_STRICT );
        mb_internal_encoding ( 'UTF-8' );
        if (!defined('TPL_DIR')) define('TPL_DIR', 'front'); //模板路径
        //registe classes
        spl_autoload_register ( array ($this,'loadClass' ) );
    }

    /**
     * Class loader.
     */
    public function loadClass($class) {

        $_class = strtolower($class);


        if (strpos($_class, 'uddha')){
            $exarr = explode('_',$class);
            require_once PATH_ROOT.'bootstrap/'.implode("/",$exarr).'.class.php';


        }

        if (strpos($_class, 'smarty') !== 0 and strpos($_class, 'phpexcel') !== 0
            and strpos($_class, 'buddha') !== 0
        ) {

            $classes = Buddha_Run_Urils::getClasses(PATH_ROOT);
            if (! array_key_exists ( $class, $classes )) {
                die ( 'Class "' . $class . '" not found.' );
            }
            require_once $classes [$class];

        }



    }
 
}