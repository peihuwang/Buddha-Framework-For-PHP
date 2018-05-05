<?php

class Region extends Buddha_App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = strtolower(__CLASS__);

    }

    function getProName()
    {//获取省
        $proName = $this->getFiledValues(array('id', 'name'), "level=1");
        return $proName;
    }

    function getCityName($id)
    {//根据省获取市或区县
        $cityName = $this->getFiledValues(array('id', 'name'), "father='{$id}'");
        return $cityName;
    }

    function getAgentUsurId($level3)
    {//根据地区编号获取代理商的userid
        $UserObj = new User();
        $agent_id = $UserObj->getSingleFiledValues(array('id'), "groupid=2 AND level3='{$level3}'");
        if ($agent_id) {
            return $agent_id['id'];
        } else {
            return 0;
        }
    }


    /**
     * $level1代表地区的内码id
     * 是不是省
     * @param $level1
     * @return int
     */
    public function isCountries($level1)
    {
        $num = $this->countRecords("id='{$level1}' AND level=0 ");
        if ($num) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * $level1代表地区的内码id
     * 是不是省
     * @param $level1
     * @return int
     */
    public function isProvince($level1)
    {
        $num = $this->countRecords("id='{$level1}' AND level=1 ");
        if ($num) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * $level1代表地区的内码id
     * 是不是市
     * @param $level1
     * @return int
     */
    public function isCity($level3)
    {
        $num = $this->countRecords("id='{$level3}' AND level=2 ");
        if ($num) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * $level1代表地区的内码id
     *是不是地区
     * @param $level1
     * @return int
     */
    public function isArea($level3)
    {
        $num = $this->countRecords("id='{$level3}' AND level=3 ");
        if ($num) {
            return 1;
        } else {
            return 0;
        }
    }


    /**
     * 判断 地区编码准确与否 1=准确  0=错误
     * @param $api_number
     * @return int
     * @ author wph
     */
    public function isValidRegion($api_number)
    {

        $Db_Redgion_Num = $this->countRecords(" isdel=0 and  number='{$api_number}' ");;
        if ($Db_Redgion_Num) {
            return 1;
        } else {
            return 0;
        }

    }

    /**
     * 在条件中加入地区编码
     * @param $api_number
     * @return string
     * @ author csh
     *
     */
    public function whereJoinRegion($api_number)
    {
        /* $api_number城市编号*/
        if (!$this->isValidRegion($api_number)) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }
        $locdata = $this->getApiLocationByNumberArr($api_number);
        $locdataWhere = " {$locdata['sql']} ";
        return $locdataWhere;
    }


    /**
     * @param $api_number
     * @return array
     * @ author wph
     */
    public function getApiLocationByNumberArr($api_number)
    {
        $locdata = array();
        $lat = '';
        $lng = '';
        $number = '';

        if ($this->isValidRegion($api_number)) {

            $Db_Region = $this->getSingleFiledValues(array('id', 'level', 'number', 'lat', 'lng'), " isdel=0 AND  number='{$api_number}'
             ORDER BY  level  DESC ");
            $region_id = $Db_Region['id'];
            $level = $Db_Region['level'];
            $number = $Db_Region['number'];
            $lat = $Db_Region['lat'];
            $lng = $Db_Region['lng'];
            if ($level == 3) {
                $locdata['lockmsg'] = "精确定位到区";
                $locdata['sql'] = " AND level3='{$region_id}' ";
                $locdata['joinsql'] = " AND main.level3='{$region_id}' ";
                $locdata['region_id'] = $region_id;
                $locdata['lat'] = $lat;
                $locdata['lng'] = $lng;
                $locdata['api_number'] = $number;


            } elseif ($level == 1 or $level == 2) {
                $locdata['lockmsg'] = "精确定位到市";
                $locdata['sql'] = " AND level2='{$region_id}' ";
                $locdata['joinsql'] = " AND main.level2='{$region_id}' ";
                $locdata['region_id'] = $region_id;
                $locdata['lat'] = $lat;
                $locdata['lng'] = $lng;
                $locdata['api_number'] = $number;
            } else {
                $locdata['lockmsg'] = "内部数据库层级错误";
                $locdata['sql'] = "  ";
                $locdata['joinsql'] = " ";
                $locdata['lat'] = $lat;
                $locdata['lng'] = $lng;
                $locdata['api_number'] = $number;

            }


        } else {
            $locdata['lockmsg'] = "精确定位信息获取但数据库定位信息没有添加";
            $locdata['sql'] = "  ";
            $locdata['joinsql'] = " ";
            $locdata['lat'] = $lat;
            $locdata['lng'] = $lng;
            $locdata['api_number'] = $number;
        }
        return $locdata;
    }


    /**
     * @param $api_number
     * @return string
     * @ author csh
     * $api_nearbydistance  附近的距离
     * $lats,$lngs 当前位置的经纬度
     * 在条件中加入附近
     */
    public function whereJoinNearby($distance = 1, $lats, $lngs, $api_number)

    {
        $lats = (int)$lats;
        $lngs = (int)$lngs;
//    public function whereJoinNearby($api_nearbydistance,$lats,$lngs,$api_number)
//    {
        if (!Buddha_Atom_String::isValidString($lats) AND !Buddha_Atom_String::isValidString($lngs)) {
            $RegionObj = new Region();
            $Db_Region = $RegionObj->getSingleFiledValues(array('lat', 'lng'), " number ='{$api_number}'");
            $lats = $Db_Region['lat'];
            $lngs = $Db_Region['lng'];
        }
//
//        define('EARTH_RADIUS', 6371);//地球半径，平均半径为6371km
//        $dlng = 2 * asin(sin($api_nearbydistance / (2 * EARTH_RADIUS)) / cos(deg2rad($lats)));
//        $dlng = rad2deg($dlng);
//        $dlat = $api_nearbydistance/EARTH_RADIUS;
//        $dlat = rad2deg($dlat);
//        $squares = array(
//                          'left-top'=>array('lat'=>$lats + $dlat,'lng'=>$lngs-$dlng),
        //              'right-top'=>array('lat'=>$lats + $dlat, 'lng'=>$lngs + $dlng),
        //              'left-bottom'=>array('lat'=>$lats - $dlat, 'lng'=>$lngs - $dlng),
        //              'right-bottom'=>array('lat'=>$lats - $dlat, 'lng'=>$lngs + $dlng)
//        );
//        $nearbywhere="  AND (({$squares['right-bottom']['lat']} < lat AND lat<{$squares['left-top']['lat']}) AND
//                           ({$squares['left-top']['lng']} < lng AND lng<{$squares['right-bottom']['lng']})) ";
//
//
//
        define("EARTH_RADIUS", 6378.137);
        $range = 180 / pi() * $distance / EARTH_RADIUS;
        $lngR = $range / cos($lats * pi() / 180);
        $data = array();
        $maxLat = $data["maxLat"] = $lats + $range;
        $minLat = $data["minLat"] = $lats - $range;
        $maxLng = $data["maxLng"] = $lngs + $lngR;//最大经度
        $minLng = $data["minLng"] = $lngs - $lngR;//最小经度
        $nearbywhere = "  AND (({$minLat} < lat AND lat < {$maxLat}) AND ({$minLng} < lng AND lng < {$maxLng})) ";

        return $nearbywhere;
    }


    /**
     * 根据省市区的地区内码id返回省市区
     * @param $level1  相当省编码
     * @param $level2  相当市编码
     * @param level3   相当区编码
     * @return string
     * @csh   2017-09-14
     */
    public function getDetailOfAdrressByRegionIdStr($level1, $level2, $level3, $Spacer = '   ')
    {

        $province = $this->getSingleFiledValues(array('fullname'), " id='{$level1}' ");
        $city = $this->getSingleFiledValues(array('fullname'), " id='{$level2}' ");
        $area = $this->getSingleFiledValues(array('fullname'), " id='{$level3}' ");

        return $province['fullname'] . $Spacer . $city['fullname'] . $Spacer . $area['fullname'];
    }


    /**
     * 根据省市区的地区内码id返回详细地址
     * @param $level1  相当省编码
     * @param $level2  相当市编码
     * @param level3   相当区编码
     * @return string
     * @csh   2017-09-14
     */
    public function getAllDetailOfAdrressByRegionIdStr($Db_Shop)
    {

        $address = $this->getDetailOfAdrressByRegionIdStr($Db_Shop['level1'], $Db_Shop['level2'], $Db_Shop['level3'], '');

        $detailedAddress = $address;

        if (Buddha_Atom_String::isValidString($Db_Shop['level4'])) {

            $detailedAddress .= $Db_Shop['level4'] . ' 街道  ';

        }

        if (Buddha_Atom_String::isValidString($Db_Shop['level5'])) {

            $detailedAddress .= $Db_Shop['level5'] . ' 路  ';

        }

        if (Buddha_Atom_String::isValidString($Db_Shop['endstep'])) {
            $detailedAddress .= $Db_Shop['endstep'] . ' 号/弄  ';
        }


        if (Buddha_Atom_String::isValidString($Db_Shop['roadfullname'])) {
            $detailedAddress .= $Db_Shop['roadfullname'];
        }

        if (Buddha_Atom_String::isValidString($Db_Shop['roadfullname'])) {

            $detailedAddress .= $Db_Shop['specticloc'];

        }


        return $detailedAddress;
    }


    /**
     * @param $Db_User
     * @return string
     */
    public function getAllDetailOfUserAdrressByRegionIdStr($Db_User)
    {

        $address = $this->getDetailOfAdrressByRegionIdStr($Db_User['level1'], $Db_User['level2'], $Db_User['level3'], '');

        $detailedAddress = $address;

        if (Buddha_Atom_String::isValidString($Db_User['address'])) {

            $detailedAddress .= $Db_User['address'];

        }

        return $detailedAddress;
    }


    public function getLocaDataByNumber($number, $jsonarr = NULL)
    {
        if ($jsonarr != NULL) {
            $lat = $jsonarr['lat'];
            $lng = $jsonarr['lng'];
            $number = $jsonarr['adcode'];;
        } else {

            $Db_Region = $this->getSingleFiledValues('', "isdel=0 and  number='{$number}' order by  id  desc ");
            $lat = $Db_Region['lat'];
            $lng = $Db_Region['lng'];
            $number = $Db_Region['number'];
        }


        $locdata = array();

        $num = $this->countRecords(" isdel=0 and  number='{$number}' ");
        if ($num) {
            $Db_Region = $this->getSingleFiledValues('', "isdel=0 and  number='{$number}' order by  id  desc ");
            $region_id = $Db_Region['id'];
            $level = $Db_Region['level'];
            $number = $Db_Region['number'];
            if ($level == 3) {
                $locdata['lockmsg'] = "精确定位到区";
                $locdata['sql'] = " AND level3='{$region_id}' ";
                $locdata['joinsql'] = " AND main.level3='{$region_id}' ";
                $locdata['region_id'] = $region_id;
                $locdata['lat'] = $lat;
                $locdata['lng'] = $lng;
                $locdata['number'] = $number;


            } elseif ($level == 1 or $level == 2) {
                $locdata['lockmsg'] = "精确定位到市";
                $locdata['sql'] = " AND level2='{$region_id}' ";
                $locdata['joinsql'] = " AND main.level2='{$region_id}' ";
                $locdata['region_id'] = $region_id;
                $locdata['lat'] = $lat;
                $locdata['lng'] = $lng;
                $locdata['number'] = $number;
            } else {
                $locdata['lockmsg'] = "内部数据库层级错误";
                $locdata['sql'] = "  ";
                $locdata['joinsql'] = " ";
                $locdata['lat'] = $lat;
                $locdata['lng'] = $lng;
                $locdata['number'] = $number;

            }
        } else {
            $locdata['lockmsg'] = "精确定位信息获取但数据库定位信息没有添加";
            $locdata['sql'] = "  ";
            $locdata['joinsql'] = " ";
            $locdata['lat'] = $lat;
            $locdata['lng'] = $lng;
            $locdata['number'] = $number;
        }

        return $locdata;
    }


    public function getLocationDataFromCookie()
    {

        $number = Buddha_Http_Input::getParameter('number');
        $locdata = $this->getLocaDataByNumber($number);
        if (trim($locdata['sql'])) {
            if ($locdata['number'] != '')
                $location_area = "&number={$locdata['number']}";
            else
                $location_area = "";
            $this->smarty->assign('location_area', $location_area);
            return $locdata;
        }

        $locdata = array();

        if (isset($_COOKIE['sName']) and $_COOKIE['sName']) {
            $str = str_replace('\\', '', $_COOKIE['sName']);
            $jsonarr = json_decode($str, true);

            if (isset($jsonarr['adcode']) and $jsonarr['adcode']) {
                $number = $jsonarr['adcode'];
                $locdata = $this->getLocaDataByNumber($number, $jsonarr);

                if ($locdata['number'] != '')
                    $location_area = "&number={$locdata['number']}";
                else
                    $location_area = "";
                $this->smarty->assign('location_area', $location_area);
                return $locdata;

            } else {
                $locdata['lockmsg'] = "获取定位失败";
                $locdata['sql'] = "  ";
                $locdata['joinsql'] = " ";
                $locdata['lat'] = '';
                $locdata['lng'] = '';
                $locdata['number'] = '';

                if ($locdata['number'] != '')
                    $location_area = "&number={$locdata['number']}";
                else
                    $location_area = "";

                $this->smarty->assign('location_area', $location_area);

                return $locdata;
            }
        } else {

            $locdata['lockmsg'] = "获取定位失败";
            $locdata['sql'] = "  ";
            $locdata['joinsql'] = " ";
            $locdata['lat'] = '';
            $locdata['lng'] = '';
            $locdata['number'] = '';

            if ($locdata['number'] != '')
                $location_area = "&number={$locdata['number']}";
            else
                $location_area = "";

            $this->smarty->assign('location_area', $location_area);

            return $locdata;

        }

    }


    //$lon1起点经度
    //$lat1起点纬度
    //$log2终点经度
    //$lat2 终点纬度
    //$unit单位米/KM
    //$decimal保留小数点几位
    function getDistance($lon1, $lat1, $log2, $lat2, $unit = 2, $decimal = 2)
    {
        $EARTH_RADIUS = 6370.996; // 地球半径系数
        $PI = 3.1415926;
        $radLat1 = $lat1 * $PI / 180.0;
        $radLat2 = $lat2 * $PI / 180.0;
        $radLng1 = $lon1 * $PI / 180.0;
        $radLng2 = $log2 * $PI / 180.0;
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $distance = $distance * $EARTH_RADIUS * 1000;
        if ($unit == 2) {
            $distance = $distance / 1000;
            return round($distance, $decimal) . 'km';
        } else {
            return round($distance, $decimal) . 'm';
        }
    }


    //API接口获取距离
    public function getdriving($from = '', $to = '')
    {
        $key = Buddha::$buddha_array['buddha_tencent_key'];
        $oauurl = 'http://apis.map.qq.com/ws/distance/v1/?mode=driving&from=' . $from . '&to=' . $to . '&key=' . $key . '';
        $re = $this->curl_file_get_contents($oauurl);
        $rearr = json_decode($re, true);
        $Result = '';
        if ($rearr['status']) {
            $Result['message'] = '起终点距离超长,最大直线距离不大于10公里';
        } else {
            if ($rearr['result']['status'] == 0) {
                foreach ($rearr['result']['elements'] as $k => $v) {
                    $Result['distance'] = round($v['distance'] / 1000, 1);
                    $Result['duration'] = round($v['duration'] / 60);
                }
            }
        }
        return $Result;

    }

    public function curl_file_get_contents($durl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $durl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }


    public function getAddress($region_id)
    {
        $getArr = $this->getAllArrayAddressByLever($region_id);
        if ($getArr) {
            $str = "";
            foreach ($getArr as $k => $v) {
                if ($k != 0)
                    $str .= $v['name'];
            }
        }
        return $str;
    }


    public function getAllArrayAddressByLever($region_id)
    {
        if ($region_id) {
            $num = $this->countRecords(" isdel=0 and id='{$region_id}' ");

            if ($num == 0) {
                return 0;
            }

            $Db_region = $this->fetch($region_id);
            $father = $Db_region['father'];
            $row[] = array('id' => $Db_region['id'], 'name' => $Db_region['name'], 'father' => $father);
            $this->getArrayTr($row);
            $sort_arr = Buddha_Atom_Array::sort($row, 'id');
            return $sort_arr;
        } else {
            return 0;
        }

    }


    public function getArrayTr(&$row)
    {

        $region_id = $row[count($row) - 1]['id'];
        $father = $row[count($row) - 1]['father'];
        if ($father) {
            $Db_region = $this->fetch($father);
            $father = $Db_region['father'];
            $row[] = array('id' => $Db_region['id'], 'name' => $Db_region['name'], 'father' => $father);
            $this->getArrayTr($row);
        }
    }


    public function hasChild($region_id)
    {
        return $this->db->countRecords($this->table, "father={$region_id}");
    }

    public function addChilds($id, $level4name, $level5name)
    {
        $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $level4name = trim($level4name);
        $level5name = trim($level5name);

        if (strlen($level4name) == 0 or strlen($level5name) == 0) {
            return 0;
        }
        $Db_Find_Region = $this->getSingleFiledValues('', " id='{$id}' ");
        if (!isset($Db_Find_Region['id'])) {
            return 0;
        }
        if ($Db_Find_Region['level'] != 3) {
            return 0;
        }
        $father_id = $Db_Find_Region['father'];
        $Db_Find_Father_Region = $this->getSingleFiledValues('', " id='{$father_id}' ");
        if (!isset($Db_Find_Father_Region['id'])) {
            return 0;
        }
        if ($Db_Find_Father_Region['level'] != 2) {
            return 0;
        }
        $Db_Search_Region = $this->getSingleFiledValues('', " father='{$id}' and name='{$level4name}' ");
        if (isset($Db_Search_Region['id'])) {
            $level4id = $Db_Search_Region['id'];
        } else {
            $level4id = $this->add(array('level' => 4, 'name' => $level4name, 'father' => $id, 'user_id' => $user_id,
                'createtime' => Buddha::$buddha_array['buddha_timestamp'],
                'createtimestr' => Buddha::$buddha_array['buddha_timestr']

            ));

            $immchildnum3level = $this->countRecords(" isdel=0 and father='{$id}' ");
            $this->edit(array('immchildnum' => $immchildnum3level), $id);
        }
        $Db_Searchlevel5_Region = $this->getSingleFiledValues('', " father='{$level4id}' and name='{$level5name}' ");

        if (isset($Db_Searchlevel5_Region['id'])) {
            $level5id = $Db_Searchlevel5_Region['id'];
        } else {
            $level5id = $this->add(array('level' => 5, 'name' => $level5name, 'father' => $level4id, 'user_id' => $user_id,
                'createtime' => Buddha::$buddha_array['buddha_timestamp'],
                'createtimestr' => Buddha::$buddha_array['buddha_timestr']
            ));
            $immchildnum4level = $this->countRecords(" isdel=0 and father='{$level4id}' ");
            $this->edit(array('immchildnum' => $immchildnum4level), $level4id);
        }
        return 1;
    }


    public function manageRoad($level3_id)
    {
        $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $Db_Find_Region = $this->getSingleFiledValues('', " id='{$level3_id}' ");
        if (!isset($Db_Find_Region['id'])) {
            return 0;
        }
        if ($Db_Find_Region['level'] != 3) {
            return 0;
        }

        $level3name = $Db_Find_Region['name'];
        $level3childnum = $this->countRecords(" isdel=0 and father='{$level3_id}' ");
        if ($level3childnum == 0) {
            return 0;
        }
        $level4region = $this->getFiledValues('', " isdel=0 and  father='{$level3_id}' ");

        $road = array();
        foreach ($level4region as $k => $v) {
            $level4id = $v['id'];
            $level4name = $v['name'];
            $road[$level4id] = $level3name . '-' . $level4name;

            $level4childnum = $this->countRecords(" isdel=0 and father='{$level4id}' ");

            if ($level4childnum) {
                $level5region = $this->getFiledValues('', " isdel=0 and  father='{$level4id}' ");

                foreach ($level5region as $k1 => $v1) {
                    $level5id = $v1['id'];
                    $level5name = $v1['name'];
                    $road[$level5id] = $level3name . '-' . $level4name . '-' . $level5name;
                    unset($road[$level4id]);
                }
            }
        }
        $prefix = $this->db->getPrefix();
        $deletesql = "DELETE FROM {$prefix}agentroad  WHERE user_id='{$user_id}' or  level3_id='{$level3_id}'   ";
        $this->db->query($deletesql);
        $createtime = Buddha::$buddha_array['buddha_timestamp'];
        $createtimestr = Buddha::$buddha_array['buddha_timestr'];
        $sql = "INSERT INTO {$prefix}agentroad  (`user_id` ,`level3_id` ,`endstep` ,`roadname` ,`createtime` ,`createtimestr`  ) values ";
        foreach ($road as $k3 => $v3) {
            $roadname = $v3;
            $sql .= " ('{$user_id}','{$level3_id}','{$k3}','{$roadname}','{$createtime}','{$createtimestr}'),";
        }
        $sql = substr($sql, 0, strlen($sql) - 1);
        $this->db->query($sql);

    }


    public function   modifyRoad($id, $name)
    {
        $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $name = trim($name);
        if (strlen($name) < 0) {
            return 0;
        }

        $Db_Find_Region = $this->getSingleFiledValues('', " id='{$id}' ");
        if (!isset($Db_Find_Region['id'])) {
            return 0;
        }
        if ($Db_Find_Region['level'] <= 3) {
            return 0;
        }

        $father = $Db_Find_Region['father'];
        $hasThisChild = $this->countRecords(" isdel=0 and father='{$father}' and  name='{$name}' ");
        if ($hasThisChild) {
            return 0;
        } else {
            $this->db->updateRecords(array('name' => $name), $this->table, "id ='{$id}' and user_id='{$user_id}'");
            return 1;
        }

    }

    /**
     * @param int $father
     * @param int $user_level1 用户当前省ID
     * @param int $user_level2 用户当前市ID
     * @param int $user_level3 用户当前区ID
     * @return array
     */
    public function getChildlist($father = 1, $user_level1 = 0, $user_level2 = 0, $user_level3 = 0)
    {
        if ($father == '') {
            $father = 1;
        }
        $Db_Shopcat = $this->db->getFiledValues(array('id', 'immchildnum', 'name', 'father', 'level'), $this->table, "father='{$father}' and isdel=0");

        $RegionObj = new Region();

        if ($Db_Shopcat[0]['level'] == 1 AND $RegionObj->isProvince($user_level1)) {
            $Db_Shopcat = $RegionObj->regionOrderby($Db_Shopcat, $user_level1);
        }


        if ($Db_Shopcat[0]['level'] == 2 AND $RegionObj->isCity($user_level2)) {
            $Db_Shopcat = $RegionObj->regionOrderby($Db_Shopcat, $user_level2);
        }

        if ($Db_Shopcat[0]['level'] == 3 AND $RegionObj->isArea($user_level3)) {
            $Db_Shopcat = $RegionObj->regionOrderby($Db_Shopcat, $user_level3);
        }

        return $Db_Shopcat;
    }

    /**
     * @param $Db_Shopcat
     * @param $user_level
     * @return array
     * 地区重新排序，根据当前用户的地区ID
     */
    public function regionOrderby($Db_Shopcat, $user_level)
    {
        foreach ($Db_Shopcat as $k => $v) {
            if ($v['id'] == $user_level) {
                $Region = $v;
                unset($Db_Shopcat[$k]);
            }
        }

        $Db_Shop = array();
        $Db_Shop[0] = $Region;

        foreach ($Db_Shopcat as $k => $v) {
            $Db_Shop[] = $v;
        }

        return $Db_Shop;
    }


    public function getChildlistpc($father)
    {
        $Db_Shopcat = $this->db->getFiledValues(array('id', 'immchildnum', 'name', 'father'), $this->table, "father='{$father}' and isdel=0");
        return $Db_Shopcat;
    }

    public function getOptionOfRegionByLevel($father, $level = 1)
    {
        $Db_Region = $this->db->getFiledValues(array('id', 'immchildnum', 'name', 'father'), $this->table, "level='{$level}' and father='{$father}'
        and isdel=0");
        return $Db_Region;
    }


    public function ajax_adderr($fid)
    {
        $RegionObj = new Region();
        if ($fid == '') {
            $fid = 1;
        }
        $Db_Region = $RegionObj->getFiledValues(array('id', 'immchildnum', 'name', 'father', 'level'), "father='{$fid}' and isdel=0");
        $datas = array();
        if ($Db_Region) {
            $datas['isok'] = 'true';
            $datas['data'] = $Db_Region;
        } else {
            $datas['isok'] = 'false';
            $datas['data'] = '';
        }
        return $datas;
    }

    /*
    *    ajaxadderr   ajax 请求地区(地区查询)
     *
     * */
    public function ajaxadderr($fid)
    {
        $RegionObj = new Region();
        if (!$fid) {
            $fid = '1';
        }
        $Db_Region = $RegionObj->getFiledValues(array('id', 'immchildnum', 'name', 'father', 'level'), "father='{$fid}' and isdel=0");
        $datas = array();
        if ($Db_Region) {
            $datas['isok'] = 'true';
            $datas['data'] = $Db_Region;
        } else {
            $datas['isok'] = 'false';
            $datas['data'] = '';
        }
        return $datas;
    }

    /*
     *   @Region_area  根据ID查询对应的地区名称格式1
     * */

    public function Region_area($id_string)
    {
        $area = $this->getFiledValues(array('id', 'fullname'), "id in ($id_string) and isdel=0");
        $area_name = $area[0]['fullname'] . $area[1]['fullname'] . $area[2]['fullname'];
        return $area_name;
    }

    /**
     * 根据ID查询对应的地区名称格式2
     * @param $level2 市编号
     * @param $level3 区县编号
     * @return array
     */
    public function Region_area2($level2, $level3)
    {
        if ($level2) {
            $citys['level2'] = $this->getSingleFiledValues(array('name'), "id={$level2}");
        }
        if ($level3) {
            $citys['level3'] = $this->getSingleFiledValues(array('name'), "id={$level3}");
        }
        return $citys;
    }

    /*
    *  @   select_provincialcity  查询省市区
    *  @   $id   包含省份ID 、市 ID、区县ID
    *      $id['level1']省份ID
    *      $id['level2']市 ID
    *      $id['level3']区县ID
     */
    public function select_provincialcity($id, $is_where = 'id')
    {
        if ($is_where == 'id') {
            $Db_Region['level1'] = $this->getSingleFiledValues('', "id='{$id['level1']}' and isdel=0");
            $Db_Region['level2'] = $this->getSingleFiledValues('', "id='{$id['level2']}' and isdel=0");
            $Db_Region['level3'] = $this->getSingleFiledValues('', "id='{$id['level3']}' and isdel=0");
        } else if ($is_where == 'father') {
            $Db_Region = $this->getSingleFiledValues('', "father='{$id}' and isdel=0");
        }
        return $Db_Region;
    }
//@RegionCookieselect 根据首页选择的地区查询对应的省市区的编号和名称
//使用地方有：单页信息

    function RegionCookieSelect()
    {
        $RegionObj = new Region();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $fid = array('father');
        $where = 'id=';
        $qx = $RegionObj->getSingleFiledValues($fid, $where . $locdata['region_id']);//查找父级
        $sq = $RegionObj->getSingleFiledValues($fid, $where . $qx['father']);//查找父级
        $reg['level0'] = 1;
        $reg['level1'] = $sq['father'];
        $reg['level2'] = $qx['father'];
        $reg['level3'] = $locdata['region_id'];
        return $reg;
    }
    /*   public function changeProvince($id,$name,$pinyin,$fullname){
           /*
               $RegionObj = new Region();
               $RegionObj->changeProvince(2,'北京','beijing','北京');
               $RegionObj->changeProvince(2236,'重庆','chongqing','重庆');
               $RegionObj->changeProvince(791,'上海','shanghai','上海');
               $RegionObj->changeProvince(19,'天津','tianjin','天津');
               $RegionObj->changeProvince(3608,'香港','xianggang','香港');
               $RegionObj->changeProvince(3627,'澳门','aomen','澳门');
            */

    /*$num=  $this->countRecords(""," isdel=0 and id='{$id}' ");
    if($num)	{

        $v =  $this->getSingleFiledValues(""," isdel=0 and id='{$id}' ");
        $level =$v['level'];

        if($level==1){
            $data =array();
            $data['father'] =1;
            $data['level'] =1;
            $data['immchildnum'] =$v['immchildnum'];
            $data['number'] =$v['id'];
            $data['name'] =$name;
            $data['fullname'] =$fullname;
            $data['pinyin'] =$pinyin;
            $data['lat'] =$v['lat'];
            $data['lng'] =$v['lng'];
            $data['createtime'] =Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] =Buddha::$buddha_array['buddha_timestr'];
            $insert_shenglevel_id = $this->add($data);

            $this->updateRecords(array('level'=>2,'father'=>$insert_shenglevel_id), " isdel=0 and id='{$id}' ");

            $this->updateRecords(array('level'=>3), " isdel=0 and father='{$id}' ");
        }
    }

}*/


    /* public function addRegionFromQqRegionData(){
         $key = Buddha::$buddha_array['buddha_tencent_key'];
         $url = "http://apis.map.qq.com/ws/district/v1/list?key={$key}";
         $result =file_get_contents($url);
         $arr = json_decode($result,true);
         $data_result = $arr['result'];
         foreach($data_result[0] as $k=>$v){
             $data =array();
             $data['father'] =1;
             $data['level'] =1;

             $immchildnum = $v['cidx'][1]-$v['cidx'][0]+1;
             $data['immchildnum'] =$immchildnum;

             $data['number'] =$v['id'];
             $data['name'] =$v['name'];
             $data['fullname'] =$v['fullname'];

             $pinyin='';
             foreach($v['pinyin'] as $k1=>$v1){
                 $pinyin.=$v1;
             }
             $data['pinyin'] =$pinyin;
             $data['lat'] =$v['location']['lat'];
             $data['lng'] =$v['location']['lng'];
             $data['createtime'] =Buddha::$buddha_array['buddha_timestamp'];
             $data['createtimestr'] =Buddha::$buddha_array['buddha_timestr'];

             $shengbiaozhi = substr($v['id'] , 0 , 2);
              if($v['name']=='北京' or $v['name']=='天津' or $v['name']=='上海' or $v['name']=='重庆'){

              }
             $num =  $this->countRecords(" number='{$data['number']}' and isdel=0 ");
             if($num==0){
                 $shengfather = $this->add($data);
             }else{
                $row =  $this->getSingleFiledValues(" number='{$data['number']}'  and isdel=0");
                 $shengfather = $row['id'];
             }

             $childkstart =$v['cidx'][0];
             $childkend =$v['cidx'][1];
             foreach($data_result[1] as $shik=>$shiv){
                 if($shik>=$childkstart and $shik<=$childkend){
                     $data =array();
                     $data['father'] =$shengfather;
                     $data['level'] =2;
                     $immchildnum = $shiv['cidx'][1]-$shiv['cidx'][0]+1;
                     $data['immchildnum'] =$immchildnum;
                     $data['number'] =$shiv['id'];
                     $data['fullname'] =$shiv['fullname'];
                     if(isset($shiv['name']))
                         $data['name'] =$shiv['name'];
                     else
                         $data['name'] =$shiv['fullname'];

                     $pinyin='';
                     foreach($shiv['pinyin'] as $shik1=>$shiv1){
                         $pinyin.=$shiv1;
                     }

                     $data['pinyin'] =$pinyin;
                     $data['lat'] =$shiv['location']['lat'];
                     $data['lng'] =$shiv['location']['lng'];
                     $data['createtime'] =Buddha::$buddha_array['buddha_timestamp'];
                     $data['createtimestr'] =Buddha::$buddha_array['buddha_timestr'];


                     $shi_shengbiaozhi = substr($shiv['id'] , 0 , 2);
                     $shibiaozhi = substr($shiv['id'] , 0 , 4);
                     if($shi_shengbiaozhi==$shengbiaozhi){

                         $num =  $this->countRecords(" number='{$data['number']}' and isdel=0 ");
                         if($num==0){
                             $shifather = $this->add($data);
                         }else{
                             $row =  $this->getSingleFiledValues(" number='{$data['number']}'  and isdel=0");
                             $shifather = $row['id'];
                         }

                     }

                     $quchildkstart =$shiv['cidx'][0];
                     $quchildkend =$shiv['cidx'][1];
                     foreach($data_result[2] as $quk=>$quv){
                         if($quk>=$quchildkstart and $quk<=$quchildkend){
                             $data =array();
                             $data['father'] =$shifather;
                             $data['level'] =3;
                             $data['immchildnum'] =0;
                             $data['number'] =$quv['id'];
                             $data['fullname'] =$quv['fullname'];
                             if(isset($quv['name']))
                                 $data['name'] =$quv['name'];
                             else
                                 $data['name'] =$quv['fullname'];
                             $pinyin='';
                             foreach($quv['pinyin'] as $quk1=>$quv1){
                                 $pinyin.=$quv1;
                             }

                             $data['pinyin'] =$pinyin;
                             $data['lat'] =$quv['location']['lat'];
                             $data['lng'] =$quv['location']['lng'];
                             $data['createtime'] =Buddha::$buddha_array['buddha_timestamp'];
                             $data['createtimestr'] =Buddha::$buddha_array['buddha_timestr'];
                             $qu_shibiaozhi = substr($quv['id'] , 0 , 4);
                             if($qu_shibiaozhi==$shibiaozhi){
                                 $num =  $this->countRecords(" number='{$data['number']}' and isdel=0 ");
                                 if($num==0){
                                     $qufather = $this->add($data);
                                 }else{
                                     $row =  $this->getSingleFiledValues(" number='{$data['number']}'  and isdel=0");
                                     $qufather = $row['id'];
                                 }
                             }
                         }
                     }

                 }
             }



         }

     }*/

}