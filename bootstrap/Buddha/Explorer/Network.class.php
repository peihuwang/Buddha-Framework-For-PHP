<?php

class Buddha_Explorer_Network{
    protected static $_instance;
    /**
     * 实例化
     *
     * @static
     * @access	public
     * @return	object 返回对象
     */
    public static function getInstance($options)
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
    public function __construct(){

    }





    /**
     * 判断内网IP
     *
     * @param $ip
     *
     * @returns
     */
    public static function isPrivateIp($ip) {
        if($ip=='127.0.0.1')
            return true;

//分割字符串
        $token = strtok($ip, '.');
//组合数组
        while ($token !== false)
        {
            $strIP[] = $token;
            $token = strtok(".");

        }
//判断IP地址是否合法
        if(count($strIP)!=4)
        {
            return false;
        }
//判断是否为A类内网IP
        if($strIP[0] == '10')
        {
            if($strIP[1]>=0 && $strIP[1] <= 255)
            {
                if($strIP[2]>=0 && $strIP[2] <= 255)
                {
                    if($strIP[3]>=0 && $strIP[3] <= 255)
                    {
                        return true;
                    }
                }
            }
            return false;
        }
//判断是否为B类内网IP
        if($strIP[0] == '172')
        {
            if($strIP[1] >= 16 && $strIP[1] <= 31)
            {
                if($strIP[2]>=0 && $strIP[2] <= 255)
                {
                    if($strIP[3]>=0 && $strIP[3] <= 255)
                    {
                        return true;
                    }
                }
            }
            return false;
        }
//判断是否为C类内网IP
        if($strIP[0] == '192')
        {
            if($strIP[1] == '168')
            {
                if($strIP[2]>=0 && $strIP[2] <= 255)
                {
                    if($strIP[3]>=0 && $strIP[3] <= 255)
                    {
                        return true;
                    }
                }
            }
            return false;
        }
//错误的IP地址
        return false;
    }


    public static function getRealIp(){
        $isagent = TRUE;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $currentIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $currentIP = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $currentIP = $_SERVER['REMOTE_ADDR'];
            $isagent = FALSE;
        }
        $ip= array((preg_match('~[\d\.]{7,15}~', $currentIP, $match) ? $match[0] : 'unknow'), $isagent);
        $ip = $ip[0];
        list($ip1,$ip2,$ip3,$ip4)=explode(".",$ip);
        return $ip1*pow(256,3)+$ip2*pow(256,2)+$ip3*256+$ip4;
    }

    public static function convertRealIp($ip) {
        list($ip1,$ip2,$ip3,$ip4)=explode(".",$ip);
        return $ip1*pow(256,3)+$ip2*pow(256,2)+$ip3*256+$ip4;
    }


    /**
     * 获取访问者的ip
     * @static
     * @access	public
     * @return string
     **/

    public static function getIp() {
        $isagent = TRUE;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $currentIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $currentIP = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $currentIP = $_SERVER['REMOTE_ADDR'];
            $isagent = FALSE;
        }
        $ip= array((preg_match('~[\d\.]{7,15}~', $currentIP, $match) ? $match[0] : 'unknow'), $isagent);
        return $ip[0];
    }

}