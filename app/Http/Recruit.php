<?php
class Recruit extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }


    /**
     * @param int $id
     * @return string
     * 推荐属于该店铺下的
     */
    public function recommendBelongShop($shop_id,$id=0)
    {
        $host = Buddha::$buddha_array['host'];

        $RecruitObj = new Recruit();
        $CommonObj = new Common();
        $shownum = 6; //最大显示数量
        /**
         *  思路：
         *      1、先统计该店铺下的 招聘 有多少个(审核通过+上架)；
         *      2、如果该店铺下的 招聘 数量少于$shownum 数量则直接查询即可（前提是当前传过来的id=0；如果大于0，则要去除该ID）
         *      3、如果该店铺下的 招聘 数量多余$shownum 数量则直接查询后随机筛选出$shownum即可
         */

        /**↓↓↓↓↓↓↓↓↓↓↓↓ 随机显示该店铺下的 6 款 招聘  ↓↓↓↓↓↓↓↓↓↓↓↓**/
        $random_filed = array('id as recruit_id','recruit_name as name','pay as price','recruit_desc as `brief`','small as img','shop_id');

        $where = " shop_id='{$shop_id}' AND is_sure=1 AND buddhastatus=0 AND isdel=0";

        if($id>0)
        {
            $where .= " id!='{$id}'";
        }

        $order = ' ORDER BY id DESC';
        /***1、先统计该店铺下 招聘 的有多少个(审核通过+上架)；****/

        $Db_Recruit_count = $RecruitObj->countRecords($where);


        if($Db_Recruit_count==0)
        {
            $Db_Recruit = array();

        }elseif(0<=$Db_Recruit_count AND $Db_Recruit_count<=$shownum)//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 小于等于 $shownum最大显示数量 则直接查询所有的
        {
            $Db_Recruit = $RecruitObj->getFiledValues($random_filed,$where.$order);

        }else{//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 大于 $shownum最大显示数量 则直接查询所有的
            $Db_random_id = $RecruitObj->getFiledValues(array('id'),$where.' AND  is_rec=1 '.$order);
            if(!Buddha_Atom_Array::isValidArray($Db_random_id)){
                $Db_random_id = $RecruitObj->getFiledValues(array('id'),$where.$order);
            }
            $random_id_arr = array();
            //ID二维数组 变为 ID一维数组
            foreach ($Db_random_id as $k=>$v)
            {
                $random_id_arr[]=$v['id'];
            }
            //如果总数量 大于 $shownum最大显示数量 则随机获取数组 的个数等于 $shownum最大显示数量
            if(sizeof($random_id_arr)>$shownum)
            {
                $number = $shownum;
            }else{//如果总数量 小于等于 $shownum最大显示数量 则随机获取数组 的个数等于 数组的数量
                $number = sizeof($random_id_arr);
            }

            //随机获取$number个数据
            $random_keys = array_rand($random_id_arr,$number);

            $random_id_str = '';
            //如果总数量 大于 $shownum最大显示数量
            if(sizeof($random_id_arr)>$shownum)
            {
                foreach ($random_keys as $k=>$v)
                {
                    $random_id_str .= $random_id_arr[$v].',';
                }
                $random_id = trim($random_id_str,',');
            }else{//如果总数量 小于等于 $shownum最大显示数量
                foreach ($Db_random_id as $k=>$v)
                {
                    $random_id_str .= $v['id'].',';
                }
                $random_id = trim($random_id_str,',');
            }

            $Db_Recruit = $RecruitObj->getFiledValues($random_filed," id IN ({$random_id})");
        }

        foreach ($Db_Recruit as $k=>$v)
        {
            $Db_Recruit[$k]['brief'] = $CommonObj->intercept_strlen($v['brief'],'10');

            if(Buddha_Atom_String::isValidString($v['price'])){
                $Db_Recruit[$k]['price'] = '¥ '.$v['price'];
            }else{
                $Db_Recruit[$k]['price'] = '面议';
            }

            if(Buddha_Atom_String::isValidString($v['img']))
            {
                $Db_Recruit[$k]['img'] = $host.Buddha_Atom_Dir::getformatDbStorageDir($v['img']);
            }else{
                $ShopObj = new Shop();
                $Db_Shop = $ShopObj->getSingleFiledValues(array('small'),"id= {$v['shop_id']}");
                $Db_Recruit[$k]['img'] =  $host.Buddha_Atom_Dir::getformatDbStorageDir($Db_Shop['small']);
                unset($Db_Recruit[$k]['shop_id']);
            }
        }
        /**↑↑↑↑↑↑↑↑↑↑↑↑ 随机显示 6款 招聘 ↑↑↑↑↑↑↑↑↑↑**/

        $list['headettitle'] = '招聘';
        $list['more'] = $Db_Recruit;
        $list['url'] = "index.php?a=index&c={$this->table}&shop_id=".$shop_id;

        return $list;
    }





    /**
     * 判断此招聘信息的创建者是否是目前的登录者
     * @param $recruit_id
     * @param $user_id
     * @return int
     */
    public function isRecruitBelongToUser($recruit_id,$user_id){
        $num = $this->countRecords("id='{$recruit_id}' and user_id='{$user_id}'  ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }

    public function getcategory(){
        $category = $this->getFiledValues(array('id', 'cat_name', 'sub'), "isdel=0 and ifopen=0 order  by id DESC");

        return $category;
    }


    public function getInSqlByID($cates,$sub){
        $retur_arr = $this->getSubs($cates,$sub,0);
        $temp_arr = array();
        if(count($retur_arr)){

            foreach($retur_arr as $k=>$v){
                $temp_arr[]=$v['id'];
            }
        }
        $temp_arr[]=$sub;
        return "(".implode(',',$temp_arr).")";


    }

    /**
     * @param $recruit_id
     * @param $level3
     * @return int
     * @author wph 2017-09-14
     */
    public function isOwnerBelongToAgentByLeve3($recruit_id,$level3){

        if($level3<1 or $recruit_id<1){
            return 0;
        }

        $num = $this->countRecords(" id='{$recruit_id}' AND  level3='{$level3}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * @return array
     * 学历数组
     */
    public function Recruitment_Education()
    {
        return $Education = array('1'=>'学历不限','2'=>'初中学历','3'=>'高中学历','4'=>'中专学历','5'=>'专科学历','6'=>'本科学历','7'=>'本科以上学历');
    }

    /**
     * @return array
     * 学历数组select option 默认选中
     */
    public function Recruitment_Qualifications($id=0){
        $table='';
//        $Education=array('1'=>'学历不限','2'=>'初中学历','3'=>'高中学历','4'=>'中专学历','5'=>'专科学历','6'=>'本科学历','7'=>'本科以前学历');
        $Education=$this->Recruitment_Education();
        foreach($Education as $k=>$v){
            $selected='';
            if($k==$id){
                $selected='selected';
            }
            $table.='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
        }
        return $table;
    }
    /**
     * @return array
     * 根据学历ID经验返回名称
     */
    public function getRecruitment_name($id=0)
    {
//        $Education=array('1'=>'学历不限','2'=>'初中学历','3'=>'高中学历','4'=>'中专学历','5'=>'专科学历','6'=>'本科学历','7'=>'本科以前学历');
        $Education=$this->Recruitment_Education();
        foreach($Education as $k=>$v){
            if($k==$id){
                return $v;
            }
        }
    }

    /**
     * @return array
     * 经验数组
     */
    public function Recruitment__Experience()
    {
        return $Experience=array('1'=>'工作经验不限','2'=>'1-2两年工作经验','3'=>'2-4两年工作经验','4'=>'5年以上相关经验');
    }
    /**
     * @return array
     * 经验数组 select option 默认选中
     */
    public function work_experience($id=0){
        $table='';
//        $Education = array('1'=>'工作经验不限','2'=>'1-2两年工作经验','3'=>'2-4两年工作经验','4'=>'5年以上相关经验');
        $Education = $this->Recruitment__Experience();
        foreach($Education as $k=>$v){
            $selected='';
            if($k==$id){
                $selected='selected';
            }
            $table.='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
        }
        return $table;
    }
    /**
     * @return array
     * 根据经验ID经验返回名称
     */
    public function getwork_experience_name($id=0){
        $Education=  $Education = $this->Recruitment__Experience();
//        $Education=array('1'=>'工作经验不限','2'=>'1-2两年工作经验','3'=>'2-4两年工作经验','4'=>'5年以上相关经验');
        foreach($Education as $k=>$v){
            if($k==$id){
                return $v;
            }
        }
    }

    /**
     * 最新招聘
     * @param $api_number 地区编号
     * @param string $num 固定显示条数
     * @param string $b_display 1pc 2mobile
     * @param int $view 显示类型
     * @return array
     */
    public function getRecruitArr($api_number,$num='',$b_display='',$view=''){
        $ShopObj = new Shop();
        $RegionObj = new Region();
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }
        $regnum = $RegionObj->getApiLocationByNumberArr($api_number);
        $where = "";
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):15;
        $where .= "isdel=0 AND is_sure=1 and buddhastatus=0 {$regnum['sql']}  ";
        $orderby = " ORDER BY add_time  DESC ";
        if(!$num){
            if($view){
                switch($view){
                    case 2;
                        //  $where .= ' and is_sure=0';
                        break;
                    case 3;
                        $orderby = " ORDER BY click_count DESC ";
                        break;
                    case 4;
                        $orderby = " ORDER BY pay DESC ";
                        break;
                    case 5;
                        $where .= " AND shop_id != 0 ";
                        break;
                }
            }
            $sql = "select count(*) as total from {$this->prefix}recruit where {$where}";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
            $where .= $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        }elseif($num && !$b_display){
            $where .= $orderby . " LIMIT 0,6 ";
        }
        $Db_region_arr=$this->getFiledValues(array('id as recruit_id','shop_id','recruit_name','pay'),$where);
        if(count($Db_region_arr)<1){
            $Db_region_arr=$this->getFiledValues(array('id','shop_id','recruit_name','pay'),"id in(296,162,286,158) order by   add_time  desc");//没有数据显示默认
        }
        foreach($Db_region_arr as $k=>$v){
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
            if(mb_strlen($v['recruit_name']) > 18){
                $v['recruit_name'] = mb_substr($v['recruit_name'],0,18) . '...';
            }
            $Db_region_arr[$k]['shop_name'] = $Db_shop['name'];
        }
        if($rcount){
            $jsondata = array();
            $jsondata['page'] = $page;
            $jsondata['pagesize'] = $pagesize;
            $jsondata['totalrecord'] = $rcount;
            $jsondata['totalpage'] = $pcount;
            $jsondata['list'] = $Db_region_arr;
        }else{
            $jsondata =  $Db_region_arr;
        }
        return $jsondata;
    }

    /**
     * 得到促销的where语句拼接
     * @return string
     */
    public function getAddTimeConditionStr(){
        return "  ORDER BY add_time DESC ";
    }

    /**
     * 得到热门的where语句拼接
     * @return string
     */
    public function getPayConditionStr(){
        return "  ORDER BY pay DESC ";
    }


    /**
     * 得到最新的where语句拼接
     * @return string
     */
    public function getClickCountConditionStr(){
        return "  ORDER BY click_count DESC ";
    }


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

}