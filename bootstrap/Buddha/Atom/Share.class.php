<?php

/**
 * Class Buddha_Atom_Share
 */
class Buddha_Atom_Share
{
    protected static $_instance;

    /**
     * @param $options
     * @return Buddha_Http_Input
     */
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
     * @param $var_name
     * @param $id
     * @param $arr
     * @return string
     */
    public static function getShareUrl($var_name, $id, $arr = array())
    {
        //   demand.detail  需求详情 http://www.bendishangjia.com/index.php?a=info&c=demand&id=343
        //   recruit.detail 招聘详情 http://www.bendishangjia.com/index.php?a=info&c=recruit&id=371
        //   lease.detail   租赁详情 http://www.bendishangjia.com/index.php?a=info&c=lease&id=134
        //   supply.detail  供应详情 http://www.bendishangjia.com/index.php?a=info&c=supply&id=2098

        //   activity.mylistdetail   活动：单家多家详情 http://www.bendishangjia.com/index.php?a=mylist&c=activity&id=2098
        //   activity.vodelistdetail 活动： 投票详情 http://www.bendishangjia.com/index.php?a=vodelist&c=activity&id=183
        //   heartpro.detail 分购： 详情 http://www.bendishangjia.com/index.php?a=info&c=heartpro&id=10


        $url = 'http://www.bendishangjia.com/';

        //activity.mylistdetail 活动：单家多家详情
        if ($var_name == 'activity.mylistdetail' and $id > 0) {
            $url = $url . "index.php?a=mylist&c=activity&id={$id}";
        }

        //activity.vodelistdetail 活动： 投票详情
        if ($var_name == 'activity.vodelistdetail' and $id > 0) {
            $url = $url . "index.php?a=vodelist&c=activity&id={$id}";
        }

        //heartpro.detail 1分购： 详情
        if ($var_name == 'heartpro.detail' and $id > 0) {
            $url = $url . "index.php?a=info&c=heartpro&id={$id}";
        }


        //supply.detail 供应详情
        if ($var_name == 'supply.detail' and $id > 0) {
            $url = $url . "index.php?a=info&c=supply&id={$id}";
        }

        //demand.detail 需求详情
        if ($var_name == 'demand.detail' and $id > 0) {
            $url = $url . "index.php?a=info&c=demand&id={$id}";
        }

        //recruit.detail 招聘详情
        if ($var_name == 'recruit.detail' and $id > 0) {
            $url = $url . "index.php?a=info&c=recruit&id={$id}";
        }

        //lease.detail 租赁详情
        if ($var_name == 'lease.detail' and $id > 0) {
            $url = $url . "index.php?a=info&c=lease&id={$id}";
        }

        return $url;


    }


}