<?php

/**
 * Class Buddha_Atom_Secury
 */
class Buddha_Atom_Secury
{
    protected static $_instance;

    public static function getInstance($options)
    {
        if (self::$_instance === null) {
            $createObj = new self();
            if (is_array($options)) {
                foreach ($options as $option => $value) {
                    $createObj->$option = $value;
                }
            }
            self::$_instance = $createObj;
        }
        return self::$_instance;
    }

    public function __construct()
    {

    }

    /**
     * 后端权限管理
     * @param $services
     * @author wph
     */
    public static function backendPrivilege($services)
    {
        Buddha_Db_Monitor::getInstance()->memberPrivilege($services);
    }

    /**
     *操作日志
     * @param $services
     * @param string $other
     */
    public static function logWrite($services, $other = 'operateuse::operatedesc::operateolddesc')
    {
        Buddha_Db_Monitor::getInstance()->logWrite($services, $other);

    }

    /**
     * 根据客户端提供的pagesize来控制一页显示的条数
     * @param $pagesize
     * @return int
     */
    public static function getMaxPageSize($pagesize)
    {

        $pagesize = (int)$pagesize;
        if ($pagesize == 0) {
            return 1;
        } elseif ($pagesize >= 20) {
            return 20;
        } else {
            return $pagesize;
        }

    }

    /**
     * @param $filePath
     */
    public static function  setFileSecury($filePath)
    {
        $filePath = strtolower($filePath);
        $fileexe = Buddha_Tool_File::getExt($filePath);

        if (strpos($filePath, 'php') !== false or $fileexe == 'php') {
            exit;
        }

    }

    /**
     * @param $filePath
     */
    public static function  checkFileSecury($filePath)
    {
        $filePath = strtolower($filePath);
        $fileexe = Buddha_Tool_File::getExt($filePath);

        if (strpos($filePath, 'php') !== false or $fileexe == 'php') {
            exit;
        }

    }

}