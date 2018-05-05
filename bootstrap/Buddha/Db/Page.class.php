<?php

class Buddha_Db_Page{
    protected $db;
    protected $smarty;
    protected $prefix;
    protected $classname;
    protected $c;
    protected static $_instance;

    /**
     * @param null $options
     * @return Buddha_Db_Monitor
     */
    public static function getInstance($options=NULL)
    {
        if (self::$_instance === null) {
            $createObj=  new self();
            if (is_array($options))
            {
                foreach ($options as $option => $value)
                {
                    $createObj->$option = $value;
                }
            }
            self::$_instance =$createObj;
        }
        return self::$_instance;
    }

    /**
     * Buddha_Db_Monitor constructor.
     */
    public function __construct(){
        $this->db = Buddha_Driver_Db::getInstance(
            Buddha::getDatabaseConfig()
        );
        $this->prefix = $this->db->getPrefix();
        $this->smarty = Smarty::getInstance(
            Buddha::getSmartyConfig()
        );
        $this->classname = __CLASS__;
    }


    public function getApiPage($tablewhere,$where,$pagesize,$page){
        $temp_sql ="SELECT count(*) AS total
                      FROM {$tablewhere}
                      WHERE {$where} ";


        $count_arr = $this->db->query($temp_sql)->fetchAll(PDO::FETCH_ASSOC);

        $rcount = $pcount = 0;

        if(Buddha_Atom_Array::isValidArray($count_arr))
        {
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
        }

        $temp_Common = array();
        /*当前页*/
        $temp_Common['page'] = $page;
        /*每页数量*/
        $temp_Common['pagesize'] = $pagesize;
        /*总条数*/
        $temp_Common['totalrecord'] = $rcount;
        /*总页数*/
        $temp_Common['totalpage'] = $pcount;

        return $temp_Common;
    }


}