<?php
class Demand extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }


    /**
     * @param int $id
     * @return string
     * 推荐属于该店铺下的需求
     */
    public function recommendBelongShop($shop_id,$id=0,$b_display=2)
    {
        $host = Buddha::$buddha_array['host'];

        $DemandObj = new Demand();
        $CommonObj = new Common();
        $shownum = 6; //最大显示数量
        /**
         *  思路：
         *      1、先统计该店铺下的 需求 有多少个(审核通过+上架)；
         *      2、如果该店铺下的 需求 数量少于$shownum 数量则直接查询即可（前提是当前传过来的id=0；如果大于0，则要去除该ID）
         *      3、如果该店铺下的 需求 数量多余$shownum 数量则直接查询后随机筛选出$shownum即可
         */

        /**↓↓↓↓↓↓↓↓↓↓↓↓ 随机显示该店铺下的 6 款产品 ↓↓↓↓↓↓↓↓↓↓↓↓**/
        $random_filed = array('id as demand_id','name','budget as price','demand_brief as `brief`','shop_id');
        if($b_display==2)
        {
            array_push($random_filed,'demand_thumb as img');
        }elseif($b_display==1){
            array_push($random_filed,'demand_img as img');
        }
        $where = " shop_id='{$shop_id}' AND is_sure=1 AND buddhastatus=0 AND isdel=0";

        if($id>0)
        {
            $where .= " id!='{$id}'";
        }
        $order = ' ORDER BY id DESC';
        /***1、先统计该店铺下 需求 的有多少个(审核通过+上架)；****/

        $Db_Demand_count = $DemandObj->countRecords($where);


        if($Db_Demand_count==0)
        {
            $Db_Demand = array();
        }elseif(0<=$Db_Demand_count AND $Db_Demand_count<=$shownum)//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 小于等于 $shownum最大显示数量 则直接查询所有的
        {
            $Db_Demand = $DemandObj->getFiledValues($random_filed,$where.$order);
        }else{//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 大于 $shownum最大显示数量 则直接查询所有的

            $Db_random_id = $DemandObj->getFiledValues(array('id'),$where.' AND (is_hot=1 OR is_rec=1) '.$order);
            if(!Buddha_Atom_Array::isValidArray($Db_random_id)){
                $Db_random_id = $DemandObj->getFiledValues(array('id'),$where.$order);
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

            $Db_Demand = $DemandObj->getFiledValues($random_filed," id IN ({$random_id})");
        }

        if(Buddha_Atom_Array::isValidArray($Db_Demand))
        {
            foreach ($Db_Demand as $k=>$v)
            {
                if(Buddha_Atom_String::isValidString($v['price'])){
                    $Db_Demand[$k]['price'] = '¥ '.$v['price'];
                }else{
                    $Db_Demand[$k]['price'] = '面议';
                }

                $Db_Demand[$k]['brief'] = $CommonObj->intercept_strlen($v['brief'],'10');

                if(Buddha_Atom_String::isValidString($v['img']))
                {
                    $Db_Demand[$k]['img'] = $host.Buddha_Atom_Dir::getformatDbStorageDir($v['img']);
                }else{
                    $Db_Demand[$k]['img'] = '';
                }
            }
        }

        /**↑↑↑↑↑↑↑↑↑↑↑↑ 随机显示6款产品 ↑↑↑↑↑↑↑↑↑↑**/

        $list['headettitle'] = '需求';
        $list['more'] = $Db_Demand;
        $list['url'] = "index.php?a=index&c={$this->table}&shop_id=".$shop_id;

        return $list;
    }



    /**
     * @param $recruit_id
     * @param $level3
     * @return int
     * @author wph 2017-09-14
     * 判断需求是否属于此代理商
     */
    public function isOwnerBelongToAgentByLeve3($demand_id,$level3){

        if($level3<1 or $demand_id<1){
            return 0;
        }

        $num = $this->countRecords(" id='{$demand_id}' AND  level3='{$level3}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }
    /**
     * 设置多图中的第一张图片会默认展示图片
     * @param $lease_id
     * @author wph 2017-09-20
     */
    public function setFirstGalleryImgToDemand($Demand_id){
        $num =  $this->db->countRecords ( 'album', " goods_id='{$Demand_id}' AND table_name='demand' " );

        if($num){

            $defaultgimages= $this->db->getSingleFiledValues('','album',"goods_id='{$Demand_id}' AND table_name='demand' order by id ASC");
            $this->db->updateRecords(array('isdefault'=>'1'),'album',"id='{$defaultgimages['id']}'  ");
            $dataImg=array();
            $dataImg ['demand_thumb'] = $defaultgimages['goods_thumb'];
            $dataImg ['demand_img'] = $defaultgimages['goods_img'];
            $dataImg ['demand_large'] = $defaultgimages['goods_large'];
            $dataImg ['sourcepic'] = $defaultgimages['sourcepic'];
            $this->updateRecords($dataImg,"id='{$Demand_id}'   ");

        }



    }
    /**
     * 把需求的多图加入Album相册表中
     * @param $MoreImage
     * @param $lease_id
     * @param $savePath
     * @param $user_id
     * @author wph 2018-09-20
     */
    public function addImageArrToLeaseAlbum($MoreImage,$demand_id,$savePath,$user_id)
    {
        $demand_id = (int)$demand_id;
        if (Buddha_Atom_Array::isValidArray($MoreImage) and $demand_id > 0) {

            foreach ($MoreImage as $k => $v) {

                $source_file_location = PATH_ROOT . $v;
                $source_filename = str_replace($savePath, '', $v);

                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 320, 320, 'S_');
                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 640, 640, 'M_');
                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 1200, 640, 'L_');

                $small_img = $savePath . "S_" . $source_filename;
                $medium_img = $savePath . "M_" . $source_filename;
                $large_img = $savePath . "L_" . $source_filename;


                $data = array();
                $data['goods_id'] = $demand_id;
                $data['user_id'] = $user_id;
                $data['table_name'] = 'demand';
                /*小图*/
                $data['goods_thumb'] = $small_img;
                /*中图*/
                $data['goods_img'] = $medium_img;
                /*大图*/
                $data['goods_large'] = $large_img;

                $this->db->addRecords($data, 'album');
                @unlink($source_file_location);


            }

        }
    }

    public  function deleteFIleOfPicture($id){
        $Db_Image =$this->fetch($id);
        $sourcepic = $Db_Image['demand_thumb'];
        $small = $Db_Image['emand_img'];
        $medium = $Db_Image['demand_large'];
        $large = $Db_Image['sourcepic'];

        @unlink(PATH_ROOT . $sourcepic);
        @unlink(PATH_ROOT . $small);
        @unlink(PATH_ROOT . $medium);
        @unlink(PATH_ROOT . $large);
    }
    public function getDemandArr($api_number,$b_display=2){
        $host = Buddha::$buddha_array['host'];//https安全连接
        $ShopObj = new Shop();
        $where = "";
        if($b_display == 2){
            $where .= "isdel=0 AND is_sure=1 AND buddhastatus=0 AND level3='{$api_number}' ORDER BY add_time  DESC   limit 0,6";
        }else{
            $where .= "isdel=0 AND is_sure=1 AND buddhastatus=0 AND level2='{$api_number}' ORDER BY add_time  DESC   limit 0,6";
        }
        $Db_demand_arr = $this->getFiledValues(array('id as demand_id','shop_id','name','budget','demand_thumb'),$where);
        if(count($Db_demand_arr)<1){
            $Db_demand_arr=$this->getFiledValues(array('id as demand_id','shop_id','name','budget','demand_brief','demand_thumb'),"id in(227,225,118,119,114) order by add_time  desc");//没有数据显示默认
        }
        foreach($Db_demand_arr as $k => $v){
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
            $Db_demand_arr[$k]['shopname'] = $Db_shop['name'];
            if(Buddha_Atom_String::isValidString($v['demand_thumb'])){
                $Db_demand_arr[$k]['demand_thumb'] = $host.$v['demand_thumb'];
            }else{
                $Db_demand_arr[$k]['demand_thumb'] =  $host.'style/images/demand_img.jpg';
            }
            if(mb_strlen($v['name']) > 15){
                $v['name'] = mb_substr($v['name'],0,15) . '...';
            }
            $Db_demand_arr[$k]['services'] = "multisingle.demandsingle";
            $Db_demand_arr[$k]['param'] = array('demand_id'=>$v['demand_id']);
        }
        return $Db_demand_arr;
    }

    /**
     * 判断此需求信息的创建者是否是目前的登录者
     * @param $recruit_id
     * @param $user_id
     * @return int
     */
    public function isSupplyBelongToUser($demand_id,$user_id){
        $num = $this->countRecords("id='{$demand_id}' and user_id='{$user_id}'  ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }
    /**
     * 返回需求的相册数组
     * @param $lease_id
     * @return array
     * @author wph 2017-09-21
     */
    public function getApiLeaseAlbumArr($demand_id)
    {
        $host = Buddha::$buddha_array['host'];
        $returnarr = array();
        $AlbumObj = new Album();
        $Services = 'album.deleteimage';
        $param = array();
        $num = $AlbumObj->countRecords("goods_id='{$demand_id}' AND table_name='demand' ");

        if($num){

            $Db_Album_Arr = $AlbumObj-> getFiledValues('',"goods_id='{$demand_id}' AND table_name='demand' ");

            if(Buddha_Atom_Array::isValidArray($Db_Album_Arr))
            {
                foreach($Db_Album_Arr as $k=>$Db_Album)
                {
                    $param= array('album_id'=>$Db_Album['id'],'table_name'=>'album');
                    $deletearr = array('Services'=>$Services,'param'=>$param);
                    $returnarr[] = array('delete'=>$deletearr,
                        'album_id'=>$Db_Album['id'],
                        'goods_thumb'=>$host.$Db_Album['goods_thumb'],
                        'goods_img'=>$host.$Db_Album['goods_img'],
                        'goods_large'=>$host.$Db_Album['goods_large'],
                    );
                }
            }
        }else{
            $Db_Denamd = $this->getSingleFiledValues('',"id='{$demand_id}'  ");
            $param= array('album_id'=>$demand_id,'table_name'=>'demand');
            $deletearr = array('Services'=>$Services,'param'=>$param);
            if(Buddha_Atom_Array::isValidArray($Db_Denamd) AND Buddha_Atom_String::isValidString($Db_Denamd['demand_thumb'])){
                $returnarr[] = array('delete'=>$deletearr,
                    'album_id'=>$Db_Denamd['id'],
                    'goods_thumb'=>$host.$Db_Denamd['demand_thumb'],
                    'goods_img'=>$host.$Db_Denamd['demand_img'],
                    'goods_large'=>$host.$Db_Denamd['demand_large'],

                );
            }
        }
        return $returnarr;
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

        $this->db->updateRecords(array('isdefault'=>'1'),'gallery',"id='{$defaultgimages['id']}'");
        $dataImg = array();
        $dataImg ['demand_thumb'] ='';
        $dataImg ['demand_img'] ='';
        $dataImg ['demand_large'] ='';
        $dataImg ['sourcepic'] ='';
        if(Buddha_Atom_Array::isValidArray($defaultgimages))
        {
            $this->db->updateRecords(array('isdefault'=>'1'),$this->prefix.'moregallery',"id='{$defaultgimages['id']}'");
            $dataImg ['demand_thumb'] = $defaultgimages['goods_thumb'];
            $dataImg ['demand_img'] = $defaultgimages['goods_img'];
            $dataImg ['demand_large'] = $defaultgimages['goods_large'];
            $dataImg ['sourcepic'] = $defaultgimages['sourcepic'];
        }
        $num = $this->updateRecords($dataImg,"id={$goods_id}");
        return $num;
    }

}