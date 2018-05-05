<?php
class Singleinformation extends  Buddha_App_Model{



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

        $SingleinformationObj = new Singleinformation();
        $CommonObj = new Common();
        $shownum = 6; //最大显示数量
        /**
         *  思路：
         *      1、先统计该店铺下的 单页信息（即传单） 有多少个(审核通过+上架)；
         *      2、如果该店铺下的 单页信息 数量少于$shownum 数量则直接查询即可（前提是当前传过来的id=0；如果大于0，则要去除该ID）
         *      3、如果该店铺下的 单页信息 数量多余$shownum 数量则直接查询后随机筛选出$shownum即可
         */

        /**↓↓↓↓↓↓↓↓↓↓↓↓ 随机显示该店铺下的 6 款 单页信息  ↓↓↓↓↓↓↓↓↓↓↓↓**/
        $random_filed = array('id as singleinformation_id','name','number','`brief`','shop_id');
        if($b_display==2)
        {
            array_push($random_filed,'singleinformation_thumb as img');
        }elseif($b_display==1){
            array_push($random_filed,'singleinformation_img as img');
        }
        $where = " shop_id='{$shop_id}' AND is_sure=1 AND buddhastatus=0 AND isdel=0";

        if($id>0)
        {
            $where .= " id!='{$id}'";
        }

        $order = ' ORDER BY id DESC';
        /***1、先统计该店铺下 单页信息 的有多少个(审核通过+上架)；****/

        $Db_Recruit_count = $SingleinformationObj->countRecords($where);


        if($Db_Recruit_count==0)
        {
            $Db_Recruit = array();

        }elseif(0<=$Db_Recruit_count AND $Db_Recruit_count<=$shownum)//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 小于等于 $shownum最大显示数量 则直接查询所有的
        {
            $Db_Recruit = $SingleinformationObj->getFiledValues($random_filed,$where.$order);

        }else{//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 大于 $shownum最大显示数量 则直接查询所有的
            $Db_random_id = $SingleinformationObj->getFiledValues(array('id'),$where.' AND is_rec=1 '.$order);
            if(!Buddha_Atom_Array::isValidArray($Db_random_id)){
                $Db_random_id = $SingleinformationObj->getFiledValues(array('id'),$where.$order);
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

            $Db_Recruit = $SingleinformationObj->getFiledValues($random_filed," id IN ({$random_id})");
        }

        foreach ($Db_Recruit as $k=>$v)
        {

            $Db_Recruit[$k]['brief'] = $CommonObj->intercept_strlen($v['brief'],'10');

            if(Buddha_Atom_String::isValidString($v['img']))
            {
                $Db_Recruit[$k]['img'] = $host.Buddha_Atom_Dir::getformatDbStorageDir($v['img']);
            }else{
                $Db_Recruit[$k]['img'] =  '';
            }
        }
        /**↑↑↑↑↑↑↑↑↑↑↑↑ 随机显示 6款 招聘 ↑↑↑↑↑↑↑↑↑↑**/

        $list['headettitle'] = '传单';
        $list['more'] = $Db_Recruit;
        $list['url'] = "index.php?a=index&c={$this->table}&shop_id=".$shop_id;

        return $list;
    }

    public function getcategory()
    {
        $category = $this->getFiledValues(array('id', 'cat_name', 'sub'), "isdel=0 and ifopen=0 order  by id DESC");

        return $category;
    }

    /**
     * @param $singleinformation_id
     * @return mixed
     * @author csh
     * 判断 $singleinformation_id 是否有效
     * $user_id >0 表示是个人中心
     */
    public function getShopidIsVerify($singleinformation_id,$user_id=0)
    {
        if($user_id>0){
            $DB_Singleinformation_num= $this->countRecords("id='{$singleinformation_id}' AND user_id='$user_id' AND isdel=0");
        }else{
            $DB_Singleinformation_num= $this->countRecords("id='{$singleinformation_id}' AND isdel=0");
        }

        return $DB_Singleinformation_num;
    }




    public function setFirstGalleryImgToShop($shop_id){


        $table_name='singleinformation';

        $num =  $this->db->countRecords ('moregallery', " goods_id='{$shop_id}' AND tablename='{$table_name}' " );

        if($num){

            $defaultgimages= $this->db->getSingleFiledValues('','moregallery',"goods_id='{$shop_id}' AND tablename='{$table_name}' order by id ASC");

            $this->db->updateRecords(array('isdefault'=>'1'),'moregallery',"id='{$defaultgimages['id']}'  ");
            $dataImg=array();
            $dataImg [$table_name.'_thumb'] = $defaultgimages['goods_thumb'];
            $dataImg [$table_name.'_img'] = $defaultgimages['goods_img'];
            $dataImg [$table_name.'_large'] = $defaultgimages['goods_large'];
            $dataImg ['sourcepic'] = $defaultgimages['sourcepic'];


            $this->updateRecords($dataImg,"id='{$shop_id}'");

        }

    }



    public function setFirstGalleryImgToSupply($goods_id,$tablename)
    {
        $defaultgimages= $this->db->getSingleFiledValues('','moregallery',"goods_id={$goods_id} and tablename='{$tablename}' and isdel=0 order by isdefault,id ASC");
        $this->db->updateRecords(array('isdefault'=>'1'),'moregallery',"id='{$defaultgimages['id']}'");
        $dataImg=array();
        $dataImg ['singleinformation_thumb'] = $defaultgimages['goods_thumb'];
        $dataImg ['singleinformation_img'] = $defaultgimages['goods_img'];
        $dataImg ['singleinformation_large'] =  $defaultgimages['goods_large'];
        $dataImg ['sourcepic'] =  $defaultgimages['sourcepic'];
        $num =$this->updateRecords($dataImg,"id={$goods_id}");
        return $num;
    }


    /**
     * @param int $is_area
     * @return string
     * @author csh
     * 查询信息 的公共条件（必须为第一条件）
     * 对应角色显示的公共条件和对应角色要显示的公共字段
     */

    public  function public_where($usertoken,$usergroupid=0){
        if(!empty($usertoken)){
            if($usergroupid==1){

                /*商家*/
                $where=' AND isdel=0 ';
                $public_filed=' , state, is_sure, isdel, buddhastatus ';
            }elseif($usergroupid==4){
                /*普通会员*/
                $where=' AND isdel=0 ';
                $public_filed=' , state, is_sure, isdel, buddhastatus';
            }elseif($usergroupid==2){

                /*代理商*/
                $where='';
                $public_filed=' , state, is_sure, isdel, buddhastatus ';
            }elseif($usergroupid==3){

                /*合伙人*/
                $where=' AND isdel=0 ';
                $public_filed=' , is_sure ';
            }
        }else{

            /*首页*/
            $where=' state=0 AND is_sure=1 AND isdel=0 AND buddhastatus=0 ';
            $public_filed=' ';
        }

        $publicarray['where']=$where;
        $publicarray['filed']=$public_filed;

        return $publicarray;
    }

    /**
     * @param int $is_area
     * @return string
     * @author csh
     * 查询信息 的公共条件
     * is_area =0 不加入查询地区条件 =1  加入地区条件
     */

    public function act_public_where($is_area=0){
        $where=' state=0 and is_sure=1 and isdel=0 ';
        if($is_area==1){
            $RegionObj=new Region();
            $locdata = $RegionObj->getLocationDataFromCookie();
            $where.=$locdata['sql'];
        }
        return $where;
    }

    /**
     * @param $singleinfomation_id
     * @param $level3
     * @return int
     * @author wph 2017-09-13
     */
    public function isOwnerBelongToAgentByLeve3($singleinfomation_id,$level3){

          if($level3<1 or $singleinfomation_id<1){
              return 0;
          }

          $num = $this->countRecords(" id='{$singleinfomation_id}' AND  level3='{$level3}' ");
          if($num){
              return 1;
          }else{
              return 0;
          }

    }




    /**
     * 相册删除及信息删除
    */
    public function  toCleanTrash($singleinformation_id,$user_id)
    {
        $where = "goods_id='{$singleinformation_id}' AND (user_id=0 or user_id='{$user_id}') AND tablename='singleinformation' ";
        $MoregalleryObj = new Moregallery();
        $num = $MoregalleryObj->countRecords($where);
        if($num){
            /*删除图片 同时删除信息*/
            $Db_Moregallery = $MoregalleryObj->getFiledValues(array('id','goods_thumb','goods_img','goods_large','sourcepic'),$where);

            if(Buddha_Atom_Array::isValidArray($Db_Moregallery))
            {
                $idstr='';
                foreach($Db_Moregallery as $k=>$v)
                {
                    $temp_id = $v['id'];
                    $MoregalleryObj->delGelleryimage($temp_id);
                    $idstr .=$v['id'].',';
                }
                $idstr = Buddha_Atom_String::toDeleteTailCharacter($idstr,1);

                $MoregalleryObj->delRecords("id IN ({$idstr}) ");
            }
        }

        $Db_Singleinformation_num =  $this->delRecords("id='{$singleinformation_id}' AND user_id='{$user_id}'");
        return $Db_Singleinformation_num;
    }
//    /**
//     * 相册 全部删除 及 信息删除
//     */
//    public function  CommonToCleanTrash($table_name,$table_id,$user_id)
//    {
//        $where = "goods_id='{$table_id}' AND (user_id=0 or user_id='{$user_id}') AND tablename='{$table_name}' ";
//        $MoregalleryObj = new Moregallery();
//        $num = $MoregalleryObj->countRecords($where);
//        if($num){
//            /**删除图片 同时删除信息*/
//            $Db_Moregallery = $MoregalleryObj->getFiledValues(array('id','goods_thumb','goods_img','goods_large','sourcepic'),$where);
//
//            if(Buddha_Atom_Array::isValidArray($Db_Moregallery))
//            {
//                $idstr='';
//                foreach($Db_Moregallery as $k=>$v)
//                {
//                    $temp_id = $v['id'];
//                    $MoregalleryObj->delGelleryimage($temp_id);
//                    $idstr .=$v['id'].',';
//                }
//                $idstr = Buddha_Atom_String::toDeleteTailCharacter($idstr,1);
//
//                $MoregalleryObj->delRecords("id IN ({$idstr}) ");
//            }
//        }
//
//        $Db_num =  $this->db->delRecords($table_name,"id='{$table_id}' AND user_id='{$user_id}'");
//        return $Db_num;
//    }

}