<?php
class Heartpro extends  Buddha_App_Model{


    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }


    /**
     * @param $data
     * @return mixed
     * 会员参与条件
     */
    public function userJoinWhere ($userJoinWhereid=0)
    {
        $CommonObj = new Common();
        $userJoinWhere = array(
            0=>array('id'=>1,'msg'=>'只能新会员参与','select'=>1),
            1=>array('id'=>2,'msg'=>'新老会员都可以参与','select'=>1),
        );

        $userJoinWhere = $CommonObj->defaultSelectById($userJoinWhere,$userJoinWhereid);

        return $userJoinWhere;
    }

    /**
     * 判断1分购主表的内码id是否有效
     * @param $heartpro_id
     * @return int
     * @author 2017-12-22
     */
    public function isHasValidRecord($heartpro_id){

        $num = $this->countRecords("id='{$heartpro_id}' ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * 判断有没有库存  1代表有库存可以进行竟买 0代表当前没库存了
     * @param $heartpro_id
     * @return int
     *  @author 2017-12-22
     */
    public function isValidStock($heartpro_id){

        $Db_Heartpro = $this->getSingleFiledValues(array('stock'),"id='{$heartpro_id}'");
        $stock = (int)$Db_Heartpro['stock'];
        if($stock>0){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 判断此一分购是否失效 1代表不用用了也代表期限过了 0代表可以申请
     * @param $applyendtime
     * @return int
     * @author wph 2017-12-22
     */
    public function isExpire($heartpro_id){

        $Db_Heartpro = $this->getSingleFiledValues(array('applyendtime'),"id='{$heartpro_id}'");
        $applyendtime = $Db_Heartpro['applyendtime'];
        $nowtime = Buddha::$buddha_array['buddha_timestamp'];
        if($nowtime>$applyendtime){
            return 1;
        }
        if(!Buddha_Atom_String::isValidString($applyendtime)){
            return 1;
        }

        return 0;
    }

    /**
     * 判断活动开始了没 1代表活动开始了 0代表活动未开始
     * @param $heartpro_id
     * @return int
     * @author wph 2017-12-22
     */
    public function isStart($heartpro_id){

        $Db_Heartpro = $this->getSingleFiledValues(array('applystarttime'),"id='{$heartpro_id}'");
        $applystarttime = $Db_Heartpro['applystarttime'];
        $nowtime = Buddha::$buddha_array['buddha_timestamp'];
        if($applystarttime>$nowtime){
            return 0;
        }

        return 1;
    }


    /**
     * @param $shopid
     * @param $shoplogo  店铺logo
     * @param $shopname 店铺名称
     * @param string $event
     * @param string $eventpage
     * @param string $heartpro_name  1分购名称
     * @param string $heartpro_logo  1分购logo
     * 1分购二维码
     */
    public function createQrcodeForCodeSales($heartproid,$shoplogo,$shopname,$event='heartpro',$eventpage='info',$heartpro_name,$heartpro_logo)
    {
        $tt = 'heartprocodeimg';
        $CommonObj = new Common();
        $shopname = $CommonObj->intercept_strlen($shopname,7);//店铺名称
        $heartpro_name = $CommonObj->intercept_strlen($heartpro_name,11);//1分购名称

        if(!Buddha_Atom_String::isValidString($heartpro_logo)){
            $heartpro_logo = 'style/images/index_sq1.jpg';
        }
        if(!Buddha_Atom_String::isValidString($shoplogo)){
            $shoplogo = 'style/images/index_sq1.jpg';
        }
        $savefile_path = "storage/{$tt}/qrcode_{$tt}_{$heartproid}.jpg";
        $savefile= PATH_ROOT.$savefile_path;
        @mkdir(PATH_ROOT."storage/{$tt}");
        @chmod(PATH_ROOT."storage/{$tt}",0755);
        if(!file_exists($savefile))
        {
            //水印透明度
            $alpha = 100;
            //合并水印图片
            $dst_im = imagecreatefromstring(file_get_contents(PATH_ROOT . "style/images/qrcode_{$tt}.jpg"));

            $qrcodeimg = $CommonObj->getQRCode($event,$eventpage,$heartproid,$heartpro_logo);


            $src_im = imagecreatefromstring(file_get_contents(PATH_ROOT . $qrcodeimg));
            $chuli_src_im = imagecreatetruecolor(550, 550);
            imagecopyresampled($chuli_src_im, $src_im, 0, 0, 0, 0, 550, 550, imagesx($src_im),imagesy($src_im));
//
//            /**↓↓↓↓↓↓↓↓↓↓↓ 添加店招 ↓↓↓↓↓↓↓↓↓↓↓**/
//            $chuli_src_im_three = imagecreatetruecolor(180, 180);
//            $threelogo = imagecreatefromstring(file_get_contents(PATH_ROOT . $shoplogo));
//            imagecopyresampled($chuli_src_im_three, $threelogo, 0, 0, 0, 0, 180, 180, imagesx($threelogo),imagesy($threelogo));// imagecopy
//            imagecopymerge($dst_im,$chuli_src_im_three,65,130,0,0,180,180,100);
//            /**↑↑↑↑↑↑↑↑↑↑ 添加店招 ↑↑↑↑↑↑↑↑↑↑**/

            /**↓↓↓↓↓↓↓↓↓↓↓ 添加 二维码 ↓↓↓↓↓↓↓↓↓↓↓**/
//            imagecopymerge($dst_im,$chuli_src_im,imagesx($dst_im)-810,imagesy($dst_im)-1210,0,0,550,550,$alpha);
            imagecopymerge($dst_im,$chuli_src_im,imagesx($dst_im)-810,imagesy($dst_im)-1135,0,0,550,550,$alpha);

            /**↑↑↑↑↑↑↑↑↑↑ 添加 二维码 ↑↑↑↑↑↑↑↑↑↑**/

            $ttfroot = PATH_ROOT . 'style/font/simsun.ttc';
            //$font=imagecolorallocate($dst_im,41,163,238);
            $font=imagecolorallocate($dst_im,0,0,0);
            $shopname_str = '"'.$shopname.'"';
            $heartpro_name_str = '"'.$heartpro_name.'"';
//            imagettftext($dst_im, 40, 0, 570, 175, $font, $ttfroot, $shopname_str);//使用自定义的字体
//            imagettftext($dst_im, 40, 0, 410, 240, $font, $ttfroot, $heartpro_name_str);//使用自定义的字体
            imagejpeg($dst_im, $savefile);
            imagedestroy($dst_im);
            imagedestroy($chuli_src_im);
            imagedestroy($src_im);
            $haibaourl = PATH_ROOT."storage/{$tt}/qrcode_{$tt}_{$heartproid}.jpg";
        }
        return $savefile_path;
    }
















    /**
     * @param $data
     * @return string
     * 下架时间
     */
    public function shelvesend()
    {
        $shelvesend_date = Buddha::$buddha_array['buddha_timestamp'] + (7*24*60*60);
        return $shelvesend_date; // TODO: Change the autogenerated stub
    }

    /**
     * @param $data
     * @return array
     * 参与规则
     */
    public function partake()
    {
        $partake = array(0=>array('id'=>1,'name'=>'只能新会员参与'),1=>array('id'=>2,'name'=>'新老会员都可以参与'));

        return $partake;
    }

    /**
     * @param int $id
     * @return string
     * 推荐属于该店铺下的1分购
     */
    public function recommendBelongShop($shop_id,$id=0,$b_display=2)
    {
        $host = Buddha::$buddha_array['host'];

        $HeartproObj = new Heartpro();
        $CommonObj = new Common();
        $shownum = 6; //最大显示数量
        /**
         *  思路：
         *      1、先统计该店铺下的 1分购 有多少个(审核通过+上架)；
         *      2、如果该店铺下的 1分购 数量少于$shownum 数量则直接查询即可（前提是当前传过来的id=0；如果大于0，则要去除该ID）
         *      3、如果该店铺下的 1分购 数量多余$shownum 数量则直接查询后随机筛选出$shownum即可
         */

        /**↓↓↓↓↓↓↓↓↓↓↓↓ 随机显示该店铺下的 6 款 分购  ↓↓↓↓↓↓↓↓↓↓↓↓**/
        $random_filed = array('id as heartpro_id','name','price','keywords as brief','details','shop_id');
        if($b_display==2)
        {
            array_push($random_filed,'small as img');
        }elseif($b_display==1){
            array_push($random_filed,'medium as img');
        }
        $where = " shop_id='{$shop_id}' AND is_sure=1 AND buddhastatus=0 AND isdel=0";

        if($id>0)
        {
            $where .= " id!='{$id}'";
        }

        $order = ' ORDER BY id DESC';
        /***1、先统计该店铺下 1分购 的有多少个(审核通过+上架)；****/

        $Db_Demand_count = $HeartproObj->countRecords($where);

        if($Db_Demand_count==0)
        {
            $Db_Demand = array();
        }elseif(0<=$Db_Demand_count AND $Db_Demand_count<=$shownum)//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 小于等于 $shownum最大显示数量 则直接查询所有的
        {
            $Db_Demand = $HeartproObj->getFiledValues($random_filed,$where.$order);

        }else{//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 大于 $shownum最大显示数量 则直接查询所有的

            $Db_random_id = $HeartproObj->getFiledValues(array('id'),$where.' AND (is_hot=1 OR is_rec=1) '.$order);
            if(!Buddha_Atom_Array::isValidArray($Db_random_id)){
                $Db_random_id = $HeartproObj->getFiledValues(array('id'),$where.$order);
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

            $Db_Demand = $HeartproObj->getFiledValues($random_filed," id IN ({$random_id})");
        }

        foreach ($Db_Demand as $k=>$v)
        {
            if(Buddha_Atom_String::isValidString($v['price'])){
                $Db_Demand[$k]['price'] = '¥ '.$v['price'];
            }else{
                $Db_Demand[$k]['price'] = '面议';
            }

            if(Buddha_Atom_String::isValidString($v['brief'])){
                $Db_Demand[$k]['brief'] = $CommonObj->intercept_strlen($v['brief'],'10');
            }else{
                $Db_Demand[$k]['brief'] = $CommonObj->intercept_strlen($v['desc'],'10');
            }
            unset($Db_Demand[$k]['desc']);

            if(Buddha_Atom_String::isValidString($v['img']))
            {
                $Db_Demand[$k]['img'] = $host.Buddha_Atom_Dir::getformatDbStorageDir($v['img']);
            }else{
                $Db_Demand[$k]['img'] = '';
            }
        }
        /**↑↑↑↑↑↑↑↑↑↑↑↑ 随机显示 6款 1分购 ↑↑↑↑↑↑↑↑↑↑**/

        $list['headettitle'] = '1分购';
        $list['more'] = $Db_Demand;
        $list['url'] = "index.php?a=index&c={$this->table}&shop_id=".$shop_id;
        return $list;
    }



    //$webfield添加进来对应页面的字段名称：主要针对一个页面有多个相册或有多个单图
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
     * @param $order_id_or_order_sn
     *  当支付成功的时候 根据订单ID或订单编号 减去库存
     */
    public function changeStockByOrderIdOrOrderSn($order_id_or_order_sn)
    {
       $OrderObj = new Order();
       $MysqlplusObj = new Mysqlplus();
        $where=" isdel=0
                 AND (id='{$order_id_or_order_sn}' OR order_sn='{$order_id_or_order_sn}') AND pay_status=1 
                 AND order_type='heartpro' AND good_id>0 AND good_table='heartpro'";
        $num = $this->db->countRecords('order',$where);
        if($num>0)
        {
           $Db_Order = $OrderObj->getSingleFiledValues(array('good_table','good_id','order_total'),$where);

           $table_where= "id='{$Db_Order['good_id']}'";

           $DB_Table = $this->db->getSingleFiledValues(array('stock'),$Db_Order['good_table'],$table_where);
            /*减去库存 = 总库存-购买量*/
           $order_filed['stock'] = $DB_Table['stock']-$Db_Order['order_total'];

           if($MysqlplusObj->isValidTable($Db_Order['good_table']))
           {
               $this->db->updateRecords($order_filed,$Db_Order['good_table'],$table_where);
           }
        }
    }





}