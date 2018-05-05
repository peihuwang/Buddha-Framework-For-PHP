<?php

class Apptoken extends Buddha_App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = strtolower(__CLASS__);

    }


    public function del($id)
    {
        return $this->db->update($this->table, array(
            'isdel' => 1
        ), array(
            "id" => $id
        ));
    }

    function accessEdit($ip, $key, $starttime, $endtime, $access_token)
    {
        $nowtime = time();
        $nowdate = date('Y-m-d', $nowtime);
        $nowtimestr = date('h时i分s秒', $nowtime);


        $strTimeToString = "000111222334455556666667";
        $strWenhou = array('夜深', '凌晨', '早上', '上午', '中午', '下午', '晚上', '夜深');
        $tip = $strWenhou[(int)$strTimeToString[(int)date('G', $nowtime)]];

        $info = $this->db->select($this->table, array('allowip', 'static'), array('key' => $key));


        $static = $info[0]['static'];
        $static_arr = explode(':', $static);

        if ($static_arr[0] == $nowdate) {
            $visit_count = $static_arr[1] + 1;

        } else {
            $visit_count = 1;
        }


        $static = "{$nowdate}:{$visit_count} ({$tip}{$nowtimestr}";


        $apptoken = $this->db->update($this->table, array('ip' => $ip,
            'starttime' => $starttime, 'endtime' => $endtime, 'access_token' => $access_token,
            'static' => $static
        ), array(
            "key" => $key
        ));


        return $visit_count;

    }

    function getTokenNum($access_token)
    {
        if ($access_token == '' or strlen($access_token) == 0) {
            return 0;
        }
        return $this->db->countRecords($this->prefix . 'apptoken', "access_token='{$access_token}'");
    }


}