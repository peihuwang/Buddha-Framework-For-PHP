<?php

/**
 * Class Buddha_Atom_Sql
 */
class Buddha_Atom_Sql
{
    protected static $_instance;

    /**
     * @param $options
     * @return Buddha_Atom_String
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
     * sqymoble  可以就 LIKE 或者 =
     * 返回模糊搜索的sql语句
     * @param $keyword
     * @param $fieldsarr
     * @param string $joinstr 例如关联表 user
     * @return string
     */
    public static function getSqlByFeildsForVagueSearchstring($keyword, $fieldsarr, $joinstr = '')
    {
        if (strlen($keyword) == 0) {

            return '';
        }


        if (!Buddha_Atom_Array::isValidArray($fieldsarr)) {

            return '';
        }


        if (Buddha_Atom_String::isValidString($joinstr)) {

            $joinstring = $joinstr . '.';

        } else {

            $joinstring = $joinstr;
        }


        $where = ' AND ( ';
        foreach ($fieldsarr as $j => $v) {

            if (strlen($v)) {
                $where .= " {$joinstring}{$v} LIKE '%{$keyword}%'  OR";
            }

        }

        $where = Buddha_Atom_String::toDeleteTailCharacter($where, 2);

        $where .= ' ) ';

        return $where;


    }


    /**
     * sqymoble  可以就 LIKE 或者 =
     * @param $keyword
     * @param $fieldsarr
     * @param string $joinstr
     * @return string
     */
    public static function getSqlByFeildsForSearchstring($keyword, $fieldsarr, $sqymoble = 'AND', $joinstr = '')
    {
        if (strlen($keyword) == 0) {

            return '';
        }


        if (!Buddha_Atom_Array::isValidArray($fieldsarr)) {

            return '';
        }


        if (Buddha_Atom_String::isValidString($joinstr)) {

            $joinstring = $joinstr . '.';

        } else {

            $joinstring = $joinstr;
        }


        $where = ' AND ( ';
        foreach ($fieldsarr as $j => $v) {

            if (strlen($v)) {
                $where .= " {$joinstring}{$v} = '{$keyword}'  {$sqymoble}";
            }

        }


        $len = strlen($sqymoble);
        $where = Buddha_Atom_String::toDeleteTailCharacter($where, $len);

        $where .= ' ) ';

        return $where;


    }


    /**
     * 返回时间区间的sql语句字符串  当天
     * @param $timestart
     * @param $timeend
     * @param $fieldstr
     * @param string $joinstr 例如关联表 别名字段
     * @param int $is_today 当天is_today =1
     * @return string
     */
    public static function getSqlByTimeIntervalString($timestart, $timeend, $fieldstr, $is_today = 0, $joinstr = '')
    {

        if (Buddha_Atom_String::isValidString($joinstr)) {
            $fieldstring = $joinstr . '.' . $fieldstr;

        }


        if ($is_today == 1) {

            if (Buddha_Atom_String::isValidString($timestart)) {
                $today_start_time = $timestart;
            } elseif (Buddha_Atom_String::isValidString($timeend)) {
                $today_start_time = $timeend;
            }

            $today_end_time = $today_start_time + 24 * 3600;

            $where = " AND  ({$fieldstring}>={$today_start_time} AND  {$fieldstring}<{$today_end_time}) ";

            return $where;

        }


        if (Buddha_Atom_String::isValidString($timestart) AND !Buddha_Atom_String::isValidString($timeend)) {

            $where = " AND  {$fieldstring}>={$timestart}  ";

        }
        if (Buddha_Atom_String::isValidString($timestart) AND Buddha_Atom_String::isValidString($timeend)) {

            $where = " AND  ({$fieldstring}>={$timestart} AND  {$fieldstring}<{$timeend}) ";

        } elseif (!Buddha_Atom_String::isValidString($timestart) AND Buddha_Atom_String::isValidString($timeend)) {

            $where = "  AND  {$fieldstring}<{$timeend} ";

        } else {

            $where = '';
        }

        return $where;


    }


}


