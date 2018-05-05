<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/19
 * Time: 0:27
 * author sys
 */
class Album extends  Buddha_App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = strtolower(__CLASS__);
    }




    /**
     * @param $table_id
     * @param int $b_display
     * @return array
     * 通过 tableid 获取Gallery相册
     */
    public function getAlbumByTableid($table_id,$table_name,$b_display=2)
    {
        $b_display = (int)$b_display;

        $filedarr = array();
        $Db_Album = array();
        if($b_display==2)
        {
            array_push($filedarr,'goods_img as img');
        }elseif($b_display==1)
        {
            array_push($filedarr,'goods_large as img');
        }
        if(!Buddha_Atom_Array::isValidArray($filedarr))
        {
            $Db_Album = array();
        }else{
            $Db_Album = $this->getFiledValues($filedarr,"goods_id='{$table_id}' AND table_name='{$table_name}'");
            if(!Buddha_Atom_Array::isValidArray($Db_Album)){
                $Db_Album = array();
            }
        }

        return $Db_Album;
    }











    public function getImage($id,$table_name){
        return $this->getFiledValues(array('id','goods_thumb'),"goods_id='{$id}' AND table_name='{$table_name}' ");
    }


    public function isDeleteImageFileOk($album_id,$table_name,$user_id){

        $MysqlplusObj = new Mysqlplus();
        if(!$MysqlplusObj->isValidTable($table_name)){
            return 0;
        }

        $num =  $this->db->countRecords('album',"id='{$album_id}' AND user_id='{$user_id}' AND table_name='{$table_name}' ");
        $Db_albuminfo = $this->getSingleFiledValues(array('goods_id'),"id='{$album_id}' AND user_id='{$user_id}' AND table_name='{$table_name}' ");
        if($num){
            if(!$this->isHasRecord($album_id)){
                return 0;
            }
            /*进行选择表中的记录*/
            $Db_Album = $this->getSingleFiledValues('',"id='{$album_id}' ");
            /*判断数据的主人对不对 如果不对返0 对返1*/
            $db_user_id = $Db_Album['user_id'];
            if($db_user_id>0 and $db_user_id!=$user_id){
                return 0;
            }
            if(Buddha_Atom_String::isValidString($Db_Album['goods_thumb'])){
                @unlink(PATH_ROOT.$Db_Album['goods_thumb']);
            }
            if(Buddha_Atom_String::isValidString($Db_Album['goods_img'])){
                @unlink(PATH_ROOT.$Db_Album['goods_img']);
            }
            if(Buddha_Atom_String::isValidString($Db_Album['goods_large'])){
                @unlink(PATH_ROOT.$Db_Album['goods_large']);
            }

            if(Buddha_Atom_String::isValidString($Db_Album['sourcepic'])){
                @unlink(PATH_ROOT.$Db_Album['sourcepic']);
            }

            if(!file_exists(PATH_ROOT.$Db_Album['goods_thumb'])){
                $goods_id = $Db_albuminfo['goods_id'];
                $row = array("{$table_name}_thumb" => '', "{$table_name}_img" => '', "{$table_name}_large" => '', 'sourcepic' => '');
                $this->db->updateRecords($row, $table_name, "id='{$goods_id}' ");
                $this->delRecords("id='{$album_id}' ");
                return 1;
            }


        }



/**屏蔽原因：王总，说下面的直观
    if(Buddha_Atom_String::isValidString($table_name)){
        $arr=array('shop','lease','demand');
        $aa=0;//不属于该相册中
        foreach($arr as $k=>$v){
            if($v==$table_name){
              $aa=1;
            }
        }
        if($aa==0){
            return 0;
        }else{

            if($table_name=='shop') {
                $filed=array('small', 'medium', 'large', 'sourcepic');
            }elseif($table_name=='lease'){
                $filed=array('lease_thumb', 'lease_img', 'lease_large', 'sourcepic');
            }elseif ($table_name=='demand'){
                $filed=array('demand_thumb', 'demand_img', 'demand_large', 'sourcepic');
            }

            $num = $this->db->countRecords($table_name, "id='{$album_id}' AND user_id='{$user_id}' ");
            if ($num == 0) {
                return 0;
            }
            $Db_Table = $this->db->getSingleFiledValues($filed, $table_name, "id='{$album_id}' AND user_id='{$user_id}' ");
            foreach ($filed as $k=>$v ){
                if (Buddha_Atom_String::isValidString($Db_Table[$v])) {
                    @unlink(PATH_ROOT . $Db_Table[$v]);
                }
            }
            if (!file_exists(PATH_ROOT . $Db_Table[$filed[0]])) {
                foreach ($filed as $k=>$v ){
                    $row[]=array(
                        $v=>'',
                    );
                }
                $this->db->updateRecords($row,$table_name, "id='{$album_id}' ");
                return 1;
            } else {
                return 0;
            }
        }

    }

 */




        if($table_name=='shop') {
            $num = $this->db->countRecords('shop', "id='{$album_id}' AND user_id='{$user_id}' ");
            if ($num == 0) {
                return 0;
            }

            $Db_Shop = $this->db->getSingleFiledValues(array('small', 'medium', 'large', 'sourcepic'), 'shop', "id='{$album_id}' AND user_id='{$user_id}' ");

            if (Buddha_Atom_String::isValidString($Db_Shop['small'])) {
                @unlink(PATH_ROOT . $Db_Shop['small']);
            }
            if (Buddha_Atom_String::isValidString($Db_Shop['medium'])) {
                @unlink(PATH_ROOT . $Db_Shop['medium']);
            }
            if (Buddha_Atom_String::isValidString($Db_Shop['large'])) {
                @unlink(PATH_ROOT . $Db_Shop['large']);
            }

            if (Buddha_Atom_String::isValidString($Db_Shop['sourcepic'])) {
                @unlink(PATH_ROOT . $Db_Shop['sourcepic']);
            }

            if (!file_exists(PATH_ROOT . $Db_Shop['small'])) {
                $row = array('small' => '', 'medium' => '', 'large' => '', 'sourcepic' => '');
                $this->db->updateRecords($row, 'shop', "id='{$album_id}' ");
                return 1;
            } else {
                return 0;
            }

        }


        if($table_name=='lease') {
            $num = $this->db->countRecords('lease', "id='{$album_id}' AND user_id='{$user_id}' ");
            if ($num == 0) {
                return 0;
            }

            $Db_Lease = $this->db->getSingleFiledValues(array('lease_thumb', 'lease_img', 'lease_large', 'sourcepic'), 'lease', "id='{$album_id}' AND user_id='{$user_id}' ");

            if (Buddha_Atom_String::isValidString($Db_Lease['lease_thumb'])) {
                @unlink(PATH_ROOT . $Db_Lease['lease_thumb']);
            }
            if (Buddha_Atom_String::isValidString($Db_Lease['lease_img'])) {
                @unlink(PATH_ROOT . $Db_Lease['lease_img']);
            }
            if (Buddha_Atom_String::isValidString($Db_Lease['lease_large'])) {
                @unlink(PATH_ROOT . $Db_Lease['lease_large']);
            }

            if (Buddha_Atom_String::isValidString($Db_Lease['sourcepic'])) {
                @unlink(PATH_ROOT . $Db_Lease['sourcepic']);
            }

            if (!file_exists(PATH_ROOT . $Db_Lease['lease_thumb'])) {
                $row = array('lease_thumb' => '', 'lease_img' => '', 'lease_large' => '', 'sourcepic' => '');
                $this->db->updateRecords($row, 'lease', "id='{$album_id}' ");
                return 1;
            } else {
                return 0;
            }

        }

        if($table_name=='demand') {

            $num = $this->db->countRecords('demand', "id='{$album_id}' AND user_id='{$user_id}' ");
            if ($num == 0) {
                return 0;
            }

            $Db_demand = $this->db->debug()->getSingleFiledValues(array('demand_thumb', 'demand_img', 'demand_large', 'sourcepic'), 'demand', "id='{$album_id}' AND user_id='{$user_id}' ");
            echo '4';
            if (Buddha_Atom_String::isValidString($Db_demand['demand_thumb'])) {
                @unlink(PATH_ROOT . $Db_demand['demand_thumb']);
            }
            if (Buddha_Atom_String::isValidString($Db_demand['demand_img'])) {
                @unlink(PATH_ROOT . $Db_demand['demand_img']);
            }
            if (Buddha_Atom_String::isValidString($Db_demand['demand_large'])) {
                @unlink(PATH_ROOT . $Db_demand['demand_large']);
            }

            if (Buddha_Atom_String::isValidString($Db_demand['sourcepic'])) {
                @unlink(PATH_ROOT . $Db_demand['sourcepic']);
            }
            if (!file_exists(PATH_ROOT . $Db_demand['demand_thumb'])) {
                $row = array('demand_thumb' => '', 'demand_img' => '', 'demand_large' => '', 'sourcepic' => '');
                $this->db->updateRecords($row, 'demand', "id='{$album_id}' ");
                return 1;
            } else {
                return 0;
            }

        }





      return 0;


    }

    /**
     * @param $goods_id记录id
     * @param $table_name表名
     *
     */
    public function setFirstGalleryImgToSupply($goods_id,$table_name){
        $defaultgimages= $this->db->getSingleFiledValues('','album',"goods_id='{$goods_id} AND table_name='{$table_name}' order by id ASC");
        $this->db->updateRecords(array('isdefault'=>'1'),'album',"id='{$defaultgimages['id']}'");
        $dataImg=array();
        $dataImg ['goods_thumb'] = $defaultgimages['goods_thumb'];
        $dataImg ['goods_img'] = $defaultgimages['goods_img'];
        $dataImg ['goods_large'] = $defaultgimages['goods_large'];
        $dataImg ['sourcepic'] = $defaultgimages['sourcepic'];
        $this->db->updateRecords($dataImg,'supply',"id='{$defaultgimages['id']}'  ");

    }

    public function addImageArrToShopAlbum($MoreImage,$tablename_id,$savePath,$tablename){

        $tablename_id = (int)$tablename_id;
        if(Buddha_Atom_Array::isValidArray($MoreImage) and $tablename_id>0){

            foreach($MoreImage as $k=>$v){

                $source_file_location = PATH_ROOT.$v;
                $source_filename  = str_replace($savePath, '', $v);

                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 320, 320, 'S_');
                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 640, 640, 'M_');
                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 1200, 640, 'L_');

                $small_img = $savePath."/S_" . $source_filename;
                $medium_img = $savePath."/M_" . $source_filename;
                $large_img= $savePath."/L_" . $source_filename;

                $data = array();
                $data['goods_id'] = $tablename_id;
                $data['table_name'] = $tablename;
                /*小图*/
                $data['goods_thumb'] =$small_img;
                /*中图*/
                $data['goods_img'] = $medium_img;
                /*大图*/
                $data['goods_large'] = $large_img;

                $this->db->addRecords ( $data, 'album' );
                @unlink($source_file_location);

            }
        }
    }

    /**
     * 判断album相册是否有此记录
     * @param $album_id
     * @return int
     */
    public function isHasRecord($album_id){
        $num = $this->countRecords("id='{$album_id}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }

//    public  function del_image($id,$gimages){
//        $MoregalleryObj=new Moregallery();
//        $thumimg=array();
//        if(!$id){
//            $thumimg['isok']=0;
////            $thumimg['data']='参数错误';
//        }
//        $mid=$gimages['id'];
//        if($gimages and $gimages['isdefault']==0){
//            $MoregalleryObj->del($mid);
//            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
//            @unlink(PATH_ROOT . $gimages ['goods_img']);
//            @unlink(PATH_ROOT . $gimages ['goods_large']);
//            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
//            $thumimg['isok']=0;
//            $thumimg['data']='图片删除成功';
//        }else{
//            $aa= $MoregalleryObj->del($mid);
//            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
//            @unlink(PATH_ROOT . $gimages ['goods_img']);
//            @unlink(PATH_ROOT . $gimages ['goods_large']);
//            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
//            $thumimg['isok']=1;
////            $thumimg['data']='图片删除成功';
//        }
//        return $thumimg;
//    }



    public function deletePhotos($lease_id,$table_name){
        if(!$lease_id){
            return 0;
        }
        $albumlist = $this->getFiledValues('',"goods_id='{$lease_id}' AND table_name='{$table_name}'");
        if($albumlist){
            foreach($albumlist as $k => $v){
                @unlink(PATH_ROOT . $v['goods_thumb']);
                @unlink(PATH_ROOT . $v['goods_img']);
                @unlink(PATH_ROOT . $v['goods_large']);
                $this->delRecords("id='{$v['id']}'");
            }
        }
        return 1;
    }

}