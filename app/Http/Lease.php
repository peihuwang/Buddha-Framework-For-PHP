<?php
class Lease extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }



    /**
     * @param int $id
     * @return string
     * 推荐属于该店铺下的
     */
    public function recommendBelongShop($shop_id,$id=0,$b_display=2)
    {
        $host = Buddha::$buddha_array['host'];

        $LeaseObj = new Lease();
        $CommonObj = new Common();
        $shownum = 6; //最大显示数量
        /**
         *  思路：
         *      1、先统计该店铺下的 租赁 有多少个(审核通过+上架)；
         *      2、如果该店铺下的 租赁 数量少于$shownum 数量则直接查询即可（前提是当前传过来的id=0；如果大于0，则要去除该ID）
         *      3、如果该店铺下的 租赁 数量多余$shownum 数量则直接查询后随机筛选出$shownum即可
         */

        /**↓↓↓↓↓↓↓↓↓↓↓↓ 随机显示该店铺下的 6 款 租赁  ↓↓↓↓↓↓↓↓↓↓↓↓**/
        $random_filed = array('id as lease_id','lease_name as name','rent as price','lease_brief as `brief`','lease_desc','shop_id');
        if($b_display==2)
        {
            array_push($random_filed,'lease_thumb as img');
        }elseif($b_display==1){
            array_push($random_filed,'lease_img as img');
        }
        $where = " shop_id='{$shop_id}' AND is_sure=1 AND buddhastatus=0 AND isdel=0";

        if($id>0)
        {
            $where .= " id!='{$id}'";
        }

        $order = ' ORDER BY id DESC';
        /***1、先统计该店铺下 租赁 的有多少个(审核通过+上架)；****/

        $Db_Lease_count = $LeaseObj->countRecords($where);


        if($Db_Lease_count==0)
        {
            $Db_Lease = array();

        }elseif(0<=$Db_Lease_count AND $Db_Lease_count<=$shownum)//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 小于等于 $shownum最大显示数量 则直接查询所有的
        {
            $Db_Lease = $LeaseObj->getFiledValues($random_filed,$where.$order);

        }else{//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 大于 $shownum最大显示数量 则直接查询所有的
            $Db_random_id = $LeaseObj->getFiledValues(array('id'),$where.' AND (is_hot=1 OR is_rec=1) '.$order);
            if(!Buddha_Atom_Array::isValidArray($Db_random_id)){
                $Db_random_id = $LeaseObj->getFiledValues(array('id'),$where.$order);
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

            $Db_Lease = $LeaseObj->getFiledValues($random_filed," id IN ({$random_id})");
        }

        foreach ($Db_Lease as $k=>$v)
        {
            $Db_Lease[$k]['shop_id'] = $shop_id;
            if(Buddha_Atom_String::isValidString($v['brief'])){
                $Db_Lease[$k]['brief'] = $CommonObj->intercept_strlen($v['brief'],'10');
            }else{
                $Db_Lease[$k]['brief'] = $CommonObj->intercept_strlen($v['lease_desc'],'10');
            }

            if(Buddha_Atom_String::isValidString($v['price'])){
                $Db_Lease[$k]['price'] = '¥ '.$v['price'];
            }else{
                $Db_Lease[$k]['price'] = '面议';
            }

            if(Buddha_Atom_String::isValidString($v['img']))
            {
                $Db_Lease[$k]['img'] = $host.Buddha_Atom_Dir::getformatDbStorageDir($v['img']);
            }else{
                $ShopObj = new Shop();
                $Db_Shop = $ShopObj->getSingleFiledValues(array('small'),"id='{$v['shop_id']}'");
                $Db_Lease[$k]['img'] =  $host.Buddha_Atom_Dir::getformatDbStorageDir($Db_Shop['small']);
            }
            unset($Db_Lease[$k]['shop_id']);
            unset($Db_Lease[$k]['lease_desc']);
        }
        /**↑↑↑↑↑↑↑↑↑↑↑↑ 随机显示 6款 租赁 ↑↑↑↑↑↑↑↑↑↑**/

        $list['headettitle'] = '租赁';
        $list['more'] = $Db_Lease;
        $list['url'] = "index.php?a=index&c={$this->table}&shop_id=".$shop_id;

        return $list;
    }










    /**
     * @param $recruit_id
     * @param $level3
     * @return int
     * @author wph 2017-09-14
     * 判断供应是否属于此代理商
     */
    public function isOwnerBelongToAgentByLeve3($lease_id,$level3){

        if($level3<1 or $lease_id<1){
            return 0;
        }

        $num = $this->countRecords(" id='{$lease_id}' AND  level3='{$level3}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }
    /**
     * 返回招聘的相册数组
     * @param $lease_id
     * @return array
     * @author wph 2017-09-21
     */
    public function getApiLeaseAlbumArr($lease_id,$b_display=2){
        $host = Buddha::$buddha_array['host'];
        $returnarr = array();
        $AlbumObj = new Album();
        $Services = 'album.deleteimage';
        $param = array();
        $num = $AlbumObj->countRecords("goods_id='{$lease_id}' AND table_name='lease' ");
        if($num){
            $filedarr=array('id as album_id');
            if($b_display==2){
                array_push($filedarr,'goods_thumb as img');
            }elseif($b_display==1){
                array_push($filedarr,'goods_img as img');
            }


            $Db_Album_Arr = $AlbumObj-> getFiledValues($filedarr,"goods_id='{$lease_id}' AND table_name='lease' ORDER BY id DESC");



            if(Buddha_Atom_Array::isValidArray($Db_Album_Arr)){

                foreach($Db_Album_Arr as $k=>$Db_Album){
                    if($Db_Album['img']){
                        $param= array('album_id'=>$Db_Album['album_id'],'table_name'=>'album');
                        $deletearr = array('Services'=>$Services,'param'=>$param);
                        $returnarr[] = array('delete'=>$deletearr,
                            'album_id'=>$Db_Album['album_id'],
                            'img'=>$host.$Db_Album['img'],
                        );
                    }else{
                        $param= array('album_id'=>$Db_Album['album_id'],'table_name'=>'album');
                        $deletearr = array('Services'=>$Services,'param'=>$param);
                        $returnarr[] = array('delete'=>$deletearr,
                            'album_id'=>'',
                            'img'=>'',
                        );
                    }

                }

            }

        }else{
            $filedarr=array('id as lease_id');
            if($b_display==2){
                array_push($filedarr,'lease_thumb as img');
            }elseif($b_display==1){
                array_push($filedarr,'lease_img as img');
            }
            $Db_Lease = $this->getSingleFiledValues($filedarr,"id='{$lease_id}'");
            $param= array('album_id'=>$lease_id,'table_name'=>'lease');
            $deletearr = array('Services'=>$Services,'param'=>$param);
            if(Buddha_Atom_Array::isValidArray($Db_Lease) AND ($Db_Lease['img'] !='' || $Db_Lease['img'] !=0)){
                $returnarr[] = array('delete'=>$deletearr,
                    'album_id'=>$Db_Lease['lease_id'],
                    'img'=>$host.$Db_Lease['img'],
                );
            }else{
                $returnarr[] = array('delete'=>$deletearr,
                    'album_id'=>'',
                    'img'=>'',
                );
            }

        }

        return $returnarr;
    }

    /**
     * 设置多图中的第一张图片会默认展示图片
     * @param $lease_id
     * @author wph 2017-09-20
     */
    public function setFirstGalleryImgToLease($lease_id){
        $num =  $this->db->countRecords ( 'album', " goods_id='{$lease_id}' AND table_name='lease' " );
        if($num){
            $defaultgimages= $this->db->getSingleFiledValues('','album',"goods_id='{$lease_id}' AND table_name='lease'  ORDER BY id DESC");
            $this->db->updateRecords(array('isdefault'=>'1'),'album',"id='{$defaultgimages['id']}'  ");
            $dataImg=array();
            $dataImg ['lease_thumb'] = $defaultgimages['goods_thumb'];
            $dataImg ['lease_img'] = $defaultgimages['goods_img'];
            $dataImg ['lease_large'] = $defaultgimages['goods_large'];
            $dataImg ['sourcepic'] = $defaultgimages['sourcepic'];
            $this->updateRecords($dataImg,"id='{$lease_id}' ");

        }

    }

    /**
     * 把招聘的多图加入Album相册表中
     * @param $MoreImage
     * @param $lease_id
     * @param $savePath
     * @param $user_id
     * @author wph 2018-09-20
     */
    public function addImageArrToLeaseAlbum($MoreImage,$lease_id,$savePath,$user_id)
    {
        $lease_id = (int)$lease_id;
        if (Buddha_Atom_Array::isValidArray($MoreImage) and $lease_id > 0) {

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
                $data['goods_id'] = $lease_id;
                $data['table_name'] = 'lease';
                $data['user_id'] = $user_id;
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

        /**
     * 判断此租赁信息的创建者是否是目前的登录者
     * @param $lease_id
     * @param $user_id
     * @return int
     */
    public function isLeaseBelongToUser($lease_id,$user_id){
        $num = $this->countRecords("id='{$lease_id}' and user_id='{$user_id}'  ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }



    public  function deleteFIleOfPicture($id){
        $Db_Image =$this->fetch($id);
        $sourcepic = $Db_Image['lease_thumb'];
        $small = $Db_Image['lease_img'];
        $medium = $Db_Image['lease_large'];
        $large = $Db_Image['sourcepic'];
        @unlink(PATH_ROOT . $sourcepic);
        @unlink(PATH_ROOT . $small);
        @unlink(PATH_ROOT . $medium);
        @unlink(PATH_ROOT . $large);
    }

    /**
     * 租赁接口
     * @param $api_number 地区编号
     * @param string $num 固定显示条数
     * @param string $b_display 1pc 2mobile 默认移动端
     * @param string $view
     * @return array
     */
    public function getLeaseArr($api_number,$num='',$b_display='',$view=''){
        $host = Buddha::$buddha_array['host'];//https安全连接
        $ShopObj = new Shop();
        $RegionObj = new Region();
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }
        $regnum = $RegionObj->getApiLocationByNumberArr($api_number);
        $where = "";
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):10;
        $where .= "isdel=0 AND is_sure=1 AND buddhastatus=0 {$regnum['sql']} ";
        $orderby = " ORDER BY  add_time DESC ";
        if(!$num){
            if($view) {
                switch ($view) {
                    case 2;
                        //  $where .= ' and is_sure=0';
                        break;
                    case 3;
                        $orderby = " ORDER BY click_count DESC ";
                        break;
                    case 4;
                        $orderby = " ORDER BY rent DESC ";
                        break;
                    case 5;
                        $where .= " AND shop_id != 0 ";
                        break;
                }
            }
            $sql = "select count(*) as total from {$this->prefix}lease where {$where}";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
            $where .= $orderby .  Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        }elseif($num && !$b_display){
            $where .= " LIMIT 0,6 ";
        }
        $Db_lease_arr=$this->getFiledValues(array('id as lease_id','shop_id','lease_name','rent','lease_thumb'),$where);// desc,last_update
        if(count($Db_lease_arr)<1){
            $Db_lease_arr=$this->getFiledValues(array('id as lease_id','shop_id','lease_name','rent','lease_thumb')," id in(1340,1341,1345,1346) order by  add_time DESC ");
        }//没有数据显示默认
        foreach($Db_lease_arr as $k => $v){
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
            $Db_lease_arr[$k]['shopname'] = $Db_shop['name'];
            if($v['lease_thumb'] ){
                $Db_lease_arr[$k]['lease_thumb'] = $host.$v['lease_thumb'];
            }
        }
        if($rcount){
            $jsondata = array();
            $jsondata['page'] = $page;
            $jsondata['pagesize'] = $pagesize;
            $jsondata['totalrecord'] = $rcount;
            $jsondata['totalpage'] = $pcount;
            $jsondata['list'] = $Db_lease_arr;
        }else{
            $jsondata =  $Db_lease_arr;
        }
        return $jsondata;
    }


    public function setFirstGalleryImgToSupply($goods_id,$tablename,$webfield)
    {
        $defaultgimages = $this->db->getSingleFiledValues(array('id','goods_thumb','goods_img','goods_large','sourcepic'),$this->prefix.'moregallery',"goods_id={$goods_id} and tablename='{$tablename}' and isdel=0 and webfield='{$webfield}' order by isdefault,id ASC");

        $dataImg = array();
        $dataImg ['lease_thumb'] ='';
        $dataImg ['lease_img'] ='';
        $dataImg ['lease_large'] ='';
        $dataImg ['sourcepic'] ='';
        if(Buddha_Atom_Array::isValidArray($defaultgimages))
        {
            $this->db->updateRecords(array('isdefault'=>'1'),$this->prefix.'moregallery',"id='{$defaultgimages['id']}'");
            $dataImg ['lease_thumb'] = $defaultgimages['goods_thumb'];
            $dataImg ['lease_img'] = $defaultgimages['goods_img'];
            $dataImg ['lease_large'] = $defaultgimages['goods_large'];
            $dataImg ['sourcepic'] = $defaultgimages['sourcepic'];
        }
        $num = $this->updateRecords($dataImg,"id={$goods_id}");
        return $num;
    }






}