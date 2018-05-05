<?php
class Commonindex extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }


    /**
     * @param $tabla_name      哪一张表
     * @param $filedarr         显示字段的数组
     * @param $page             当前页
     * @param $pagesize         每页显示数量
     * @param $where            显示条件
     * @param string $orderbyfiled   排序字段名称
     * @param string $groupbyfiled      分组字段名称
     * @param string $grouporderbyfiled   分组排序字段名称
     * @return array
     * 显是每个店铺下值显示一条，并且显示的店铺下新家的或者置顶的
     */
    public function newestmore($tabla_name,$filedarr,$page,$pagesize,$where,$orderbyfiled ='add_time',$groupbyfiled ='shop_id',$grouporderbyfiled='id')
    {

        if(!Buddha_Atom_String::isValidString($tabla_name)
            or !Buddha_Atom_String::isValidString($page)
            or !Buddha_Atom_String::isValidString($pagesize)
            or !Buddha_Atom_String::isValidString($where)
            or !Buddha_Atom_Array::isValidArray($filedarr))
        {
            return array();
        }

        $Table_Simplify = 't';//主表的简化名称
        $Table_Simplify_l = 'tl';//附表的简化名称
        $filedstr = '';
        $orderbyfiled = $Table_Simplify.'.'.$orderbyfiled;
        $groupbyfiled = $Table_Simplify_l.'.'.$groupbyfiled;
        $grouporderbyfiled = $Table_Simplify_l.'.'.$grouporderbyfiled;


        foreach ($filedarr as $k=>$v)
        {
            $filedstr .= $Table_Simplify.'.'.$v.',';
        }

        $filedstr= rtrim($filedstr,',');

        $sql = "SELECT * FROM 
                    (SELECT {$filedstr}
                      FROM {$this->prefix}{$tabla_name} as {$Table_Simplify} WHERE {$where} ORDER BY {$Table_Simplify}.toptime,{$orderbyfiled} DESC LIMIT 50) 
                    as {$Table_Simplify_l} GROUP BY $groupbyfiled ORDER BY {$grouporderbyfiled} DESC ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        if(!Buddha_Atom_Array::isValidArray($list))
        {
            return array();
        }

        return $list;
    }


    /**
     * @param $tabla_name   哪张表
     * @return array
     * 首页导航集合
     */
    public function indexmorenav($tabla_name)
    {
        $indexmorenav = array();

        if(!Buddha_Atom_String::isValidString($tabla_name)){
            return $indexmorenav;
        }

        $indexmorenav = array(
            0=>array(
                'id'=>2,
                'deputy'=>'zuixin',
                'is_show'=>2,
                'c'=>$tabla_name,
                'chinesename'=>'最新',
            ),
            1=>array(
                'id'=>1,
                'deputy'=>'fujin',
                'is_show'=>1,
                'select'=>0,
                'c'=>$tabla_name,
                'chinesename'=>'附近',
            ),
            2=>array(
                'id'=>3,
                'deputy'=>'remen',
                'is_show'=>1,
                'select'=>0,
                'c'=>$tabla_name,
                'chinesename'=>'热门',
            ),
            3=>array(
                'id'=>4,
                'deputy'=>'fenlei',
                'is_show'=>1,
                'select'=>0,
                'c'=>$tabla_name,
                'chinesename'=>'分类',
            ),
            4=>array(
                'id'=>5,
                'deputy'=>'shangjia',
                'is_show'=>1,
                'select'=>0,
                'c'=>$tabla_name,
                'chinesename'=>'商家',
            ),
            5=>array(
                'id'=>6,
                'deputy'=>'xinchou',
                'is_show'=>1,
                'select'=>0,
                'c'=>$tabla_name,
                'chinesename'=>'薪酬',
            ),
            6=>array(
                'id'=>7,
                'deputy'=>'tuijian',
                'is_show'=>1,
                'select'=>0,
                'c'=>$tabla_name,
                'chinesename'=>'推荐',
            ),

        );
        return $indexmorenav;
    }


    /**
     * @param $tabla_name   哪张表
     * @param $filarr        要显示的字段名称和a
     * @return array
     * 首页导航拆分组合
     */
    public function indexmorenavlist($tabla_name,$filarr)
    {

        $CommonindexObj = new Commonindex();

        $Common = $CommonindexObj->indexmorenav($tabla_name);

        foreach ($filarr as $k=>$v)
        {
            foreach ($Common as $kk=>$vv)
            {
                if($v['filed']==$vv['deputy'])
                {
                    $vv['a'] = $v['a'];
                    $vv['view'] = $v['view'];
                    $navCommon[] = $vv;
                }
            }
        }


        if (Buddha_Atom_Array::isValidArray($navCommon))
        {
            foreach ($navCommon as $k=>$v)
            {
                $navCommon[$k]['url'] = "index.php?a={$v['a']}&c={$v['c']}&view=".$v['view'];//app要屏蔽此条
            }
        }


        return $navCommon;
    }


}