<?php
class Supply extends  Buddha_App_Model
{
    public function __construct()
    {
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }


    /**
     * @param $shop_id
     * @param $user_id
     * @param string $keyword
     * @return array
     * 通过店铺ID获取店铺下正常的产品
     */
    public function getSupplyBelongShopbyShopid($shop_id,$user_id,$keyword='')
    {

        $SupplyObj = new Supply();

        $where=" shop_id={$shop_id} AND isdel=0 AND user_id='{$user_id}'";

        $filed=array('id','goods_name as cat_name');

        if(Buddha_Atom_String::isValidString($keyword))
        {
            $where.=" goods_name LIKE %{$keyword}%";
        }

        $Db_Supply = $SupplyObj->getFiledValues($filed,$where);

        if(!Buddha_Atom_Array::isValidArray($Db_Supply))
        {
            $Db_Supply = array();
        }
        foreach($Db_Supply as $k=>$v)
        {
            $Db_Supply[$k]['sub']=0;
            $Db_Supply[$k]['child_count']=0;
        }


        return $Db_Supply;
    }


    /**
     * 判断用户是否正常( 正常:is_sure=1通过审核 、 state=0启用 、 isdel=0正常 )店铺的个数
     **/
    public function IsUserHasNormalSupply($Userid)
    {
        $user_id = (int)$Userid;
        if ($user_id) {
            $where = " is_sure=1 AND isdel=0 AND user_id='{$user_id}' AND buddhastatus=0";
            $Db_Supplyp_num = $this->countRecords($where);
            if($Db_Supplyp_num){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }

    /**
     * @param int $id
     * @return string
     * 推荐属于该店铺下的产品
     */
    public function recommendBelongShop($shop_id,$id=0,$b_display=2)
    {
        $host = Buddha::$buddha_array['host'];

        $SupplyObj = new Supply();
        $CommonObj = new Common();
        $shownum = 6; //最大显示数量
        /**
         *  思路：
         *      1、先统计该店铺下的产品有多少个(审核通过+上架)；
         *      2、如果该店铺下的产品数量少于$shownum 数量则直接查询即可（前提是当前传过来的id=0；如果大于0，则要去除该ID）
         *      3、如果该店铺下的产品数量多余$shownum 数量则直接查询后随机筛选出$shownum即可
         */

        /**↓↓↓↓↓↓↓↓↓↓↓↓ 随机显示该店铺下的 6 款产品 ↓↓↓↓↓↓↓↓↓↓↓↓**/
        $random_filed = array('id as supply_id','goods_name as name','is_promote','market_price','promote_price','goods_brief','goods_desc');

        if($b_display==2)
        {
            array_push($random_filed,'goods_thumb as img');
        }elseif($b_display==1){
            array_push($random_filed,'goods_img as img');
        }

        $where = " shop_id='{$shop_id}' AND is_sure=1 AND buddhastatus=0 AND isdel=0";

        if($id>0)
        {
            $where .= " id!='{$id}'";
        }
        $order = ' ORDER BY id DESC';
        /***1、先统计该店铺下的产品有多少个(审核通过+上架)；****/

        $Db_Supply_count = $SupplyObj->countRecords($where);

            //1.1如果在没有其他条件的情况下（推荐和热门）的总数量 小于等于 $shownum最大显示数量 则直接查询所有的
        if(0<=$Db_Supply_count AND $Db_Supply_count<=$shownum)
        {

            $Db_Supply = $SupplyObj->getFiledValues($random_filed,$where.$order);

        }else{//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 大于 $shownum最大显示数量 则直接查询所有的

            $Db_random_id = $SupplyObj->getFiledValues(array('id'),$where.' AND (is_hot=1 OR is_rec=1) '.$order);
            if(!Buddha_Atom_Array::isValidArray($Db_random_id)){
                $Db_random_id = $SupplyObj->getFiledValues(array('id'),$where.$order);
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

            $Db_Supply = $SupplyObj->getFiledValues($random_filed," id IN ({$random_id})");
        }

        foreach ($Db_Supply as $k=>$v)
        {

                $Db_Supply[$k]['shop_id']=$shop_id;
            if($v['is_promote']==1)
            {
                $Db_Supply[$k]['price'] = '¥ '.$v['promote_price'];
            }else{
                $Db_Supply[$k]['price'] = '¥ '.$v['market_price'];
            }

            if(Buddha_Atom_String::isValidString($v['goods_brief'])){
                $Db_Supply[$k]['brief'] = $CommonObj->intercept_strlen($v['goods_brief'],'10');
            }else{
                $Db_Supply[$k]['brief'] = $CommonObj->intercept_strlen($v['goods_desc'],'10');
            }


            if(Buddha_Atom_String::isValidString($v['img']))
            {
                $Db_Supply[$k]['img'] = $host.Buddha_Atom_Dir::getformatDbStorageDir($v['img']);
            }else{
                $Db_Supply[$k]['img'] = '';
            }

            unset( $Db_Supply[$k]['goods_desc']);
            unset( $Db_Supply[$k]['promote_price']);
            unset( $Db_Supply[$k]['market_price']);
            unset( $Db_Supply[$k]['is_promote']);
        }
        /**↑↑↑↑↑↑↑↑↑↑↑↑ 随机显示6款产品 ↑↑↑↑↑↑↑↑↑↑**/

        $list['headettitle'] = '供应';
        $list['more'] = $Db_Supply;
        $list['url'] = "index.php?a=index&c={$this->table}&shop_id=".$shop_id;

        return $list;
    }






    /*判断商品是否过了促销时间(当商品为促销时)*/
    public function updateispromote($supply_id){
        $newtime=time();
        /*判断商品是否过了促销时间(当商品为促销时)*/
        $Db_Supply_num=$this->countRecords("id='{$supply_id}' AND is_promote=1 AND (promote_start_date<{$newtime} AND {$newtime} < promote_end_date )");
        if($Db_Supply_num){
            $data['is_promote']=0;
            $data['promote_start_date']=0;
            $data['promote_end_date']=0;
            $num=$this->edit($data,$supply_id);
            return $num;
        }
    }




    /**
     * @param $recruit_id
     * @param $level3
     * @return int
     * @author wph 2017-09-14
     * 判断供应是否属于此代理商
     */
    public function isOwnerBelongToAgentByLeve3($supply_id,$level3){

        if($level3<1 or $supply_id<1){
            return 0;
        }

        $num = $this->countRecords(" id='{$supply_id}' AND  level3='{$level3}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * 返回供应的相册数组
     * @param $lease_id
     * @return array
     * @author wph 2017-09-21
     */
    public function getApiLeaseGalleryArr($supply_id){
        $host = Buddha::$buddha_array['host'];
        $returnarr = array();
        $GalleryObj = new Gallery();
        $Services = 'album.deleteSupplyImage';
        $param = array();
        $num = $GalleryObj->countRecords("goods_id='{$supply_id}'");
        if($num){
            $Db_Album_Arr = $GalleryObj-> getFiledValues('',"goods_id='{$supply_id}'");
            if(Buddha_Atom_Array::isValidArray($Db_Album_Arr)){
                foreach($Db_Album_Arr as $k=>$Db_Album){
                    $param= array('gallery_id'=>$Db_Album['id'],'table_name'=>'gallery');
                    $deletearr = array('Services'=>$Services,'param'=>$param);
                    $returnarr[] = array('delete'=>$deletearr,
                        'gallery_id'=>$Db_Album['id'],
                        'goods_thumb'=>$host.$Db_Album['goods_thumb'],
                        'goods_img'=>$host.$Db_Album['goods_img'],
                        'goods_large'=>$host.$Db_Album['goods_large'],
                    );
                }
            }
        }else{
            $Db_Supply = $this->getSingleFiledValues('',"id='{$supply_id}'  ");
            $param= array('gallery_id'=>$supply_id,'table_name'=>'supply');
            $deletearr = array('Services'=>$Services,'param'=>$param);
            if(Buddha_Atom_Array::isValidArray($Db_Supply) AND Buddha_Atom_String::isValidString($Db_Supply['goods_thumb'])){
                $returnarr[] = array('delete'=>$deletearr,

                    'gallery_id'=>$Db_Supply['id'],
                    'goods_thumb'=>$host.$Db_Supply['demand_thumb'],
                    'goods_img'=>$host.$Db_Supply['demand_img'],
                    'goods_large'=>$host.$Db_Supply['demand_large'],
                );
            }
        }
        return $returnarr;
    }

    /**
     * 得到促销的where语句拼接
     * @return string
     */
    public function getPromotionConditionStr(){
        return "  AND is_promote=1 ";
    }

    /**
     * 得到热门的where语句拼接
     * @return string
     */
    public function getHotConditionStr(){
        return "  AND is_hot=1 ";
    }


    /**
     * 得到最新的where语句拼接
     * @return string
     */
    public function getAddTimeConditionStr(){
        return "  ORDER BY add_time DESC ";
    }


    public function setFirstGalleryImgToSupply($goods_id)
    {
        $defaultgimages= $this->db->getSingleFiledValues('','gallery',"goods_id='{$goods_id}' order by id ASC");
        $this->db->updateRecords(array('isdefault'=>'1'),'gallery',"id='{$defaultgimages['id']}'");
        $dataImg=array();
        $dataImg ['goods_thumb'] = $defaultgimages['goods_thumb'];
        $dataImg ['goods_img'] = $defaultgimages['goods_img'];
        $dataImg ['goods_large'] = $defaultgimages['goods_large'];
        $dataImg ['sourcepic'] = $defaultgimages['sourcepic'];
        $this->updateRecords($dataImg,"id='{$goods_id}'");
    }



    /*
     * @shop_url   关于店铺跳转的URL
     * err ==1  表示手机（默认）
     *  err ==2  表示PC（默认）
     * */
    public function supply_url($err=1){
        if($err==1){
            return $url='index.php?a=info&c=supply&id=';
        }
    }

    /**
     * @param $api_number 地区编号
     * @param string $num 显示条数，为真是首页显示固定条数，反之列表页（带分页）
     * @$b_display 2移动端，1pc端，空默认移动端
     * @$view 1附近（默认） 2最新 3热门 4促销
     * @return mixed
     * @author sys
     */
    public function getSupplyArr($api_number,$num='',$b_display='',$view=''){
        $host = Buddha::$buddha_array['host'];//https安全连接
        $ShopObj = new Shop();
        $RegionObj = new Region();
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }
        $regnum = $RegionObj->getApiLocationByNumberArr($api_number);
        $view = $RegionObj->getApiLocationByNumberArr($view);
        $where = "";
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):15;
        $where .= "isdel=0 AND is_sure=1 AND buddhastatus=0 {$regnum['sql']} ";
        if($view){
            switch($view){
                case 3;
                    $where .= " AND is_hot=1";//热门
                    break;
                case 4;
                    $where .= " AND is_promote=1";//促销
                    break;
            }
        }
        if(!$num){
            $sql = "select count(*) as total from {$this->prefix}recruit where {$where}";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
            $where .= " ORDER BY  add_time DESC " . Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        }elseif(!$b_display){
            $where .= " LIMIT 0,6 ";
        }else{
            $where .= " LIMIT 0,10 ";
        }
        $Db_supply_arr=$this->getFiledValues(array('id as supply_id','shop_id','goods_name','market_price','promote_price','is_promote','promote_start_date','promote_end_date','goods_thumb'),$where);// desc,last_update
        if(count($Db_supply_arr)<1){
            $Db_supply_arr=$this->getFiledValues(array('id as supply_id','shop_id','goods_name','market_price','promote_price','is_promote','promote_start_date','promote_end_date','goods_thumb')," id in(1356,791,1347,1346,1345) order by  add_time DESC ");
        }//没有数据显示默认
        foreach($Db_supply_arr as $k => $v){
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
            $Db_supply_arr[$k]['shopname'] = $Db_shop['name'];
            $Db_supply_arr[$k]['goods_thumb'] = $host.$v['goods_thumb'];
            $Db_supply_arr[$k]['icon_position'] = $host . "apiindex/menuplus/icon_position.png";
            $Db_supply_arr[$k]['icon_pay'] = $host . "style/images/Price.png";
            $Db_supply_arr[$k]['icon_shop'] = $host . "style/images/shopgray.png";
            $Db_supply_arr[$k]['icon_shop'] = $host . "style/images/shopgray.png";
            $Db_supply_arr[$k]['services'] = "multisingle.supplysingle";
            $Db_supply_arr[$k]['param'] = array('supply_id'=>$v['supply_id']);
        }
        return $Db_supply_arr;
    }

    /**
     * 促销
     * @param $api_number
     * @param $lat
     * @param $lng
     * @param string $num
     * @return mixed
     * @author sys
     */

    public function getSupplyPromoteArr($api_number,$lat,$lng,$num='',$view,$page,$pagesize,$b_display=''){
        $host = Buddha::$buddha_array['host'];//https安全连接
        $ShopObj = new Shop();
        $RegionObj = new Region();
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }
        $regnum = $RegionObj->getApiLocationByNumberArr($api_number);
        $where = "";
        $page = $page?$page:1;
        $pagesize = $pagesize?$pagesize:15;
        $where .= "isdel=0 AND is_sure=1 AND buddhastatus=0 {$regnum['sql']} AND is_promote=1 ";
        if($view){
            switch($view){
                case 3;
                    $where .= " AND is_hot=1";//热门
                    break;
                case 4;
                    $where .= " AND is_promote=1";//促销
                    break;
            }
        }
        if(!$num){
            $sql = "select count(*) as total from {$this->prefix}supply where {$where}";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
            $where .= " ORDER BY  add_time DESC " . Buddha_Tool_Page::sqlLimit ( $page,$pagesize );
        }elseif(!$num && !$b_display){
            $where .= " LIMIT 0,6 ";
        }else{
            $where .= " LIMIT 0,10 ";
        }

        $Db_supply_promote_arr=$this->getFiledValues(array('id as promote_id','shop_id','goods_name','market_price','promote_price','is_promote','promote_start_date','promote_end_date','goods_thumb'),$where);// desc,last_update
        if(count($Db_supply_promote_arr)<1){
            $Db_supply_promote_arr=$this->getFiledValues(array('id as promote_id','shop_id','goods_name','market_price','promote_price','is_promote','promote_start_date','promote_end_date','goods_thumb')," id in(1340,1341,1345,1346) order by  add_time DESC ");
        }//没有数据显示默认

        foreach($Db_supply_promote_arr as $k => $v){
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
            $jingwei = $ShopObj->getSingleFiledValues(array('lat','lng','specticloc'),"id={$v['shop_id']}");
            $distance=$RegionObj->getDistance($lng,$lat,$jingwei['lng'],$jingwei['lat'],2);
            $Db_supply_promote_arr[$k]['shopname'] = $Db_shop['name'];
            $Db_supply_promote_arr[$k]['goods_thumb'] = $host.$v['goods_thumb'];
            $Db_supply_promote_arr[$k]['distance'] = $distance;
            $Db_supply_promote_arr[$k]['icon_position'] = $host . "apiindex/menuplus/icon_position.png";
        }
        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;
        $jsondata['list'] = $Db_supply_promote_arr;
        return $jsondata;
    }


    /**
     * 判断此招聘信息的创建者是否是目前的登录者
     * @param $recruit_id
     * @param $user_id
     * @return int
     */
    public function isSupplyBelongToUser($supply_id,$user_id){
        $num = $this->countRecords("id='{$supply_id}' and user_id='{$user_id}'  ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }





}
