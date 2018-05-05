<?php
require_once PATH_ROOT.'bootstrap/Cross.Fun.php';
require_once PATH_ROOT.'bootstrap/Buddha/Sakyamuni.class.php';

/**
 *
 *
 * @author tinystar
 */

class Buddha  extends Sakyamuni{

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






}