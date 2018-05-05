<?php
class District extends  Buddha_App_Model{
    protected $tablename;
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
        $this->tablename='district';
    }


    /**
     * @param $table_name
     * @return array
     * 商圈中心列表导航(管理列表)
     */
    public function managemore()
    {
        $host  = Buddha::$buddha_array['host'];
        $imgpublic = $host.'apidistrict/menuplus/district_';
        $managemore = array(
            0=>array(
                'id'=>1,
                'deputy'=>'xiangqing',
                'img'=>$imgpublic.'xiangqing.png',
                'chinesename'=>'详情管理',
                'is_show'=>1,
                'a'=>'petitionlist',
                'c'=>$this->tablename,
            ),
            1=>array(
                'id'=>2,
                'deputy'=>'wuye',
                'img'=>$imgpublic.'wuye.png',
                'chinesename'=>'物业管理',
                'is_show'=>1,
                'a'=>'index',
                'c'=>'district',
            ),
            2=>array(
                'id'=>3,
                'deputy'=>'louceng',
                'img'=>$imgpublic.'louceng.png',
                'chinesename'=>'楼层管理',
                'is_show'=>1,
                'a'=>'index',
                'c'=>'district',
            ),
            3=>array(
                'id'=>4,
                'deputy'=>'gonggao',
                'img'=>$imgpublic.'gonggao.png',
                'chinesename'=>'公告管理',
                'is_show'=>1,
                'a'=>'index',
                'c'=>'district',
            ),
            4=>array(
                'id'=>5,
                'deputy'=>'huiyuan',
                'img'=>$imgpublic.'huiyuan.png',
                'chinesename'=>'会员管理',
                'is_show'=>1,
                'a'=>'index',
                'c'=>'district',
            ),
            5=>array(
                'id'=>6,
                'deputy'=>'shenqing',
                'img'=>$imgpublic.'shenqing.png',
                'chinesename'=>'生活圈申请',
                'is_show'=>1,
                'a'=>'petition',
                'c'=>$this->tablename,
            ),
        );

        return $managemore;
    }




    /**
     * @param $tabla_name   哪张表
     * @param $filarr        要显示的字段名称和a
     * @return array
     * 商圈中心列表导航 拆分组合
     */
    public function indexmorenavlist($filarr,$user_id)
    {

        $DistrictObj = new District();

        $Common = $DistrictObj->managemore();

        $District_count = $DistrictObj->isUserDistrict($user_id);//判断该会员是否有商圈(开发阶段屏蔽：上线即可打开)
//        $District_count = 1;
        $navCommon = array();

        foreach ($filarr as $k=>$v)
        {
            foreach ($Common as $kk=>$vv)
            {
                if($v['filed']==$vv['deputy'])
                {
                    $vv['view'] = $v['view'];
                    if(Buddha_Atom_String::isValidString($District_count))
                    {
                        if($vv['deputy']=='shenqing')
                        {
                            $vv['is_show'] = 0;
                        }else{
                            $vv['is_show'] = 1;
                        }
                    }else{
                        if($vv['deputy']=='shenqing')
                        {
                            $vv['is_show'] = 1;
                        }else{
                            $vv['is_show'] = 0;
                        }
                    }

                    $navCommon[] = $vv;
                }
            }
        }


        if (Buddha_Atom_Array::isValidArray($navCommon))
        {
            foreach ($navCommon as $k=>$v)
            {
                $navCommon[$k]['url'] = "index.php?a={$v['a']}&c={$v['c']}";//app要屏蔽此条
            }
        }


        return $navCommon;
    }


    /**
     * @param $user_id
     * @return int
     * 判断该用户是有已经有商圈
     */
    public function isUserDistrict($user_id)
    {
        if(!Buddha_Atom_String::isValidString($user_id)){
            return 0;
        }

        $DistrictObj = new District();

        $District = $DistrictObj->countRecords("user_id='{$user_id}'");

        return $District;
    }
    /**
     * @param $user_id
     * @param $typeid  列表类型  1为编辑 ;0为其它; 2 代理商
     * @return int
     * 该用户商圈 select 列表
     *
     */
    public function userDistrictSelectList($user_id,$typeid=0)
    {
        if(!Buddha_Atom_String::isValidString($user_id))
        {
            return 0;
        }

        $DistrictObj = new District();

        $where = "user_id='{$user_id}' AND isdel=0 ";

        if($typeid==0){
            $where .=" AND is_sure=1";
        }


        $filed = array('id','name');

        $District = $DistrictObj->getFiledValues($filed,$where);

        if(!Buddha_Atom_Array::isValidArray($District))
        {
            return  array();
        }

        return $District;
    }

    /**
     * @param $District
     * @return mixed
     * 数组select重组
     */
    public function districtListReorganization($user_id,$typeid=0,$district_id=0)
    {

        $DistrictObj = new District();
        $District = $DistrictObj-> userDistrictSelectList($user_id,$typeid);

        if(!Buddha_Atom_Array::isValidArray($District)){
            return $District;
        }

        $District = $DistrictObj-> defaultSelct($District,$district_id);

        return $District;
    }

    /**
     * @param $District
     * @param $district_id
     * @return array
     *  商圈select 默认选中
     */
    public function defaultSelct($District,$district_id=0)
    {
        if(!Buddha_Atom_Array::isValidArray($District))
        {
            return $District;
        }

        foreach ($District as $k=>$v)
        {
            if (Buddha_Atom_String::isValidString($district_id))
            {
                if($v['id'] == $district_id)
                {
                    $District[$k]['select'] = 1;
                }else{
                    $District[$k]['select'] = 0;
                }
            }else{
                if($k == 0)
                {
                    $District[$k]['select'] = 1;
                }else{
                    $District[$k]['select'] = 0;
                }
            }

        }

        return $District;
    }


    /**
     * @param $goods_id
     * @param $tablename
     * @param $webfield
     * @return mixed
     * 设置代表图片
     */
    public function setFirstGalleryImgToSupply($goods_id,$tablename,$webfield)
    {
        $defaultgimages = $this->db->getSingleFiledValues(array('id','goods_thumb','goods_img','goods_large','sourcepic'),$this->prefix.'moregallery',"goods_id={$goods_id} and tablename='{$tablename}' and isdel=0 and webfield='{$webfield}' order by isdefault,id ASC");

        $dataImg = array();
        $dataImg ['small'] ='';
        $dataImg ['medium'] ='';
        $dataImg ['large'] ='';
        $dataImg ['sourcepic'] ='';
        if(Buddha_Atom_Array::isValidArray($defaultgimages))
        {
            $this->db->updateRecords(array('isdefault'=>'1'),$this->prefix.'moregallery',"id='{$defaultgimages['id']}'");
            $dataImg ['small'] = $defaultgimages['goods_thumb'];
            $dataImg ['medium'] = $defaultgimages['goods_img'];
            $dataImg ['large'] = $defaultgimages['goods_large'];
            $dataImg ['sourcepic'] = $defaultgimages['sourcepic'];
        }
        $num = $this->updateRecords($dataImg,"id={$goods_id}");
        return $num;
    }


    /**
     * @param $filed            要显示的字段除了图片
     * @param $user_id          当前用户ID
     * @param $keyword          搜索词
     * @param $view             点击的奶哥
     * @param $page              当前页
     * @param $pagesize         每页显示数量
     * @param int $b_display    手机2；pc 1
     * @param string $orderby  排序字段
     * @param array $imgfiled  图片字段
     * @return mixed
     * 商圈列表
     */
    public function  userDistrictList($filed,$user_id,$keyword='',$view,$page,$pagesize,$b_display=2,$orderby='id',$imgfiled=array('small','medium'))
    {
        $where = "user_id='{$user_id}' AND isdel=0 ";

        if($keyword)
        {
            $where.=" AND name LIKE '%{$keyword}%'";
        }

        $whereView = array(
            0=>array('view'=>0,'viewwhere'=>''),
            1=>array('view'=>2,'viewwhere'=>' AND is_sure=0'),
            2=>array('view'=>3,'viewwhere'=>" AND is_sure=1"),
            3=>array('view'=>4,'viewwhere'=>" AND is_sure=4"),
        );


        foreach ($whereView as $k=>$v)
        {
            if($v['view']==$view)
            {
                $where.=$v['viewwhere'];
            }
        }

        if(Buddha_Atom_Array::isValidArray($imgfiled))
        {
            if($b_display==2)
            {
                array_push($filed,"{$imgfiled[0]} as img");

            }elseif($b_display==1)
            {
                array_push($filed,"{$imgfiled[1]} as img");
            }
        }


        $orderby = " ORDER BY {$orderby} DESC ";

        $list = $this->db->getFiledValues($filed,$this->tablename,$where.$orderby);

        $CommonObj = new Common();
        $UsercommonObj = new Usercommon();

        foreach ($list as $k => $v)
        {
            $list[$k]['img'] = $CommonObj->handleImgSlashByImgurl($v['img']);
            $list[$k]['issureimg'] = $UsercommonObj->businessissurestr($v['is_sure']);
        }

        $Nws= $CommonObj->page_where($page,$list,$pagesize);

        $datas['info'] = $Nws;

        if (Buddha_Atom_Array::isValidArray($list))
        {
            $datas['isok'] = 'true';
            $datas['data'] = $list;
        } else {
            $datas['isok'] = 'false';
            $datas['data'] = array();
        }

        return $datas;
    }

    /**
     * @return array
     * 列表导航
     */
    public function listnav($a,$c)
    {
        $nav = array(
            0=>array('id'=>1,'filed'=>'quanbu','a'=>$a,'c'=>$c,'view'=>0,'chinesename'=>'全部'),
            1=>array('id'=>2,'filed'=>'xinjia','a'=>$a,'c'=>$c,'view'=>2,'chinesename'=>'新加'),
            2=>array('id'=>3,'filed'=>'yishenhe','a'=>$a,'c'=>$c,'view'=>3,'chinesename'=>'已审核'),
            3=>array('id'=>4,'filed'=>'weitongguuo','a'=>$a,'c'=>$c,'view'=>4,'chinesename'=>'未通过'),
        );
        return $nav;
    }

    /**
     * @param $District
     * @return mixed
     * 数组 导航 重组
     */
    public function navReorganization($a,$c,$nav_id=0)
    {
        $DistrictObj = new District();
        $listnav = $DistrictObj-> listnav($a,$c);
        if(!Buddha_Atom_Array::isValidArray($listnav))
        {
            return array();
        }

        $listnav = $DistrictObj->defaultSelct($listnav);

        foreach ($listnav as $k=>$v)
        {
            $listnav[$k]['url'] = "index.php?a={$a}&c={$c}&view={$v['view']}";//app要屏蔽此条
        }

        return $listnav;
    }




    /**
     * @param $a
     * @param $c
     * @return string
     *      获取头部标题根据a和c
     */
    public function getTitleAC($a,$c)
    {
        $DistrictObj = new District();

        $navlist = $DistrictObj->managemore();

        foreach ($navlist as $k=>$v)
        {
            if($v['a']==$a AND $v['c']==$c)
            {
                return $v['chinesename'];
            }
        }

        return '';
    }



}



