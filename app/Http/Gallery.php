<?php
class Gallery extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }

    /**
     * @param $table_id
     * @param int $b_display
     * @return array
     * 通过 tableid 获取Gallery相册
     */
     public function getGalleryByTableid($table_id,$b_display=2)
     {
         $b_display = (int)$b_display;

         $filedarr = array();
         $Db_Gallery = array();
         if($b_display==2)
         {
             array_push($filedarr,'goods_img as img');

         }elseif($b_display==1)
         {
             array_push($filedarr,'goods_large as img');
         }
         if(!Buddha_Atom_Array::isValidArray($filedarr))
         {
             $Db_Gallery = array();
         }else{
             $Db_Gallery = $this->getFiledValues($filedarr,"goods_id='{$table_id}' ");
             if(!Buddha_Atom_Array::isValidArray($Db_Gallery)){
                 $Db_Gallery = array();
             }
         }
        return $Db_Gallery;
     }


    public function base64contentToImg($filePath,$base64_string){
        Buddha_Atom_Secury::setFileSecury($filePath);
        $ifp = fopen($filePath, "wb");
        fwrite($ifp, base64_decode($base64_string));
        fclose($ifp);

    }

    public function getImageInfo($filePath){
        return $imginfo = @getimagesize($filePath);
    }

    public  function resolveImageForRotate($filePath,$base64_string=NULL){//解决图像的旋转
        $imginfo =  $this->getImageInfo($filePath);

        if($base64_string!=NULL){
            $this->base64contentToImg($filePath,$base64_string);
            $image = imagecreatefromstring(base64_decode($base64_string));
        }else{
            $image = imagecreatefromstring(file_get_contents($filePath));
        }
        $exif = @exif_read_data($filePath);
        $fugai = 0;

      if(!empty($exif['Orientation'])) {
            switch($exif['Orientation']) {
                case 8:
                    $fugai = 1;
                    $fugaiimage = imagerotate($image,90,0);
                    break;
                case 3:
                    $fugai = 1;
                    $fugaiimage = imagerotate($image,180,0);
                    break;
                case 6:
                    $fugai = 1;
                    $fugaiimage = imagerotate($image,-90,0);
                    break;
            }
        }

        if($fugai){
            switch ($imginfo[2]) {
                case '1':
                    imagegif($fugaiimage, $filePath);
                    break;
                case '2':
                    imagejpeg($fugaiimage, $filePath);
                    break;
                case '3':
                    imagepng($fugaiimage, $filePath);
                    break;
                default :
                    imagejpeg($fugaiimage, $filePath);
                    break;
            }
        }
    }

    public function addimage($dataImg = array(), $goods_id,$actionval='supply'){
        foreach($dataImg as $k=>$v){
            if (base64_encode(base64_decode($v))) {
                $imgurl = explode(',', $v);
                @mkdir(PATH_ROOT . "storage/'.$actionval.'/" . $goods_id . '/'); // 如果不存在则创建
                $savePath = 'storage/'.$actionval.'/' . $goods_id . '/';
                if (!file_exists($savePath)) {
                    @mkdir($savePath, 0777);
                }
                $base64_string = $imgurl[1];
                $output_file = date('ymdhis',time()) . rand(10000, 99999) . '.jpg';
                $filePath = PATH_ROOT . $savePath . $output_file;
                $this->resolveImageForRotate($filePath,$base64_string);

                Buddha_Tool_File::thumbImageSameWidth($filePath, 320, 320, 'S_');
                Buddha_Tool_File::thumbImageSameWidth($filePath, 640, 640, 'M_');
                Buddha_Tool_File::thumbImageSameWidth($filePath, 1200, 1200, 'L_');
                $data=array();
                $data['goods_thumb'] = $savePath . 'S_' . $output_file;
                $data['goods_img'] = $savePath . 'M_' . $output_file;
                $data['goods_large'] = $savePath . 'L_' . $output_file;
                $data['sourcepic'] = $savePath . $output_file;
                $data['goods_id'] =$goods_id;
            }
            $this->add($data);
        }
    }

    public function pcaddimage($dataImg = array(),$goods_id,$actionval='supply',$uid=0){

        foreach($dataImg as $k=>$v){
            if($v){
                //存在图片
                $Image= $v;
                /*print_r(PATH_ROOT.$Image);
                exit;*/
                //$this->resolveImageForRotate(PATH_ROOT.$Image,NULL);
                
                if($Image){
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 1200, 1200, 'L_');
                }
                $data['user_id']=$uid;
                $sourcepic = str_replace("storage/{$actionval}/{$goods_id}/",'',$Image);
                $data['goods_thumb'] = "storage/{$actionval}/{$goods_id}/S_" . $sourcepic;
                $data['goods_img'] = "storage/{$actionval}/{$goods_id}/M_" . $sourcepic;
                $data['goods_large'] = "storage/{$actionval}/{$goods_id}/L_" . $sourcepic;

                @unlink(PATH_ROOT."storage/{$actionval}/{$goods_id}/" . $sourcepic);

                $data['sourcepic'] = "";
                $data['goods_id'] =$goods_id;

                $this->add($data);
            }

        }
    }


    public function getGoodsImage($goods_id)
    {
       return $this->getFiledValues(array('id','goods_thumb'),"goods_id='{$goods_id}'");
    }

    public function delGelleryimage($goods_id)
    {
       $goodimg= $this->getFiledValues('',"goods_id='{$goods_id}'");
        if(is_array($goodimg) and count($goodimg)){
          foreach($goodimg as $k=>$v){
              $this->del($v['id']);
              @unlink(PATH_ROOT . $v ['goods_thumb'] );
              @unlink(PATH_ROOT . $v ['goods_img']);
              @unlink(PATH_ROOT . $v ['goods_large']);
              @unlink(PATH_ROOT . $v ['sourcepic'] );
          }
        }
    }

    public function delGelleryimages($goods_id)
    {
        $goodimg= $this->getFiledValues('',"goods_id='{$goods_id}'");
        if(is_array($goodimg) and count($goodimg)){
            foreach($goodimg as $k=>$v){
                $this->del($v['id']);
                @unlink(PATH_ROOT . $v ['goods_thumb'] );
                @unlink(PATH_ROOT . $v ['goods_img']);
                @unlink(PATH_ROOT . $v ['goods_large']);
                @unlink(PATH_ROOT . $v ['sourcepic'] );
            }
            return 1;
        }else{
            return 0;
        }
    }

    public function delGelleryimagesss($id){
        $SupplyObj = new Supply();
        $goodimg= $this->getSingleFiledValues('',"id='{$id}'");
        if(is_array($goodimg) and count($goodimg)){
            $supplyid = $SupplyObj->getSingleFiledValues(array('id'),"goods_thumb='{$goodimg ['goods_thumb']}'");
            if($supplyid){
                $data = array();
                $data['goods_thumb'] = '';
                $data['goods_img'] = '';
                $data['goods_large'] = '';
                $data['sourcepic'] = '';
                $SupplyObj->updateRecords($data,"id='{$supplyid['id']}'");
            }
                $this->del($id);
                @unlink(PATH_ROOT . $goodimg ['goods_thumb'] );
                @unlink(PATH_ROOT . $goodimg ['goods_img']);
                @unlink(PATH_ROOT . $goodimg ['goods_large']);
                @unlink(PATH_ROOT . $goodimg ['sourcepic'] );
            return 1;
        }else{
            return 0;
        }
    }


    /**
     * @param  $table_name           来源于哪一张相册名称的添加
     * @param  $table_id             来源于哪一张相册ID的添加
     * @param  $user_id              添加相册人的用户ID
     * @return int|void
     * 往Moregallery相册中  添加图片
     */

    public function Gellerydel($table_name='supply',$table_id,$user_id=0,$deltype)
    {
        if (!Buddha_Atom_String::isValidString($table_id))
        {
            return 0;
        }

        $GalleryObj = new Gallery();
        $CommonObj = new Common();

        $Gallery_where = "goods_id='{$table_id}'";


        if(!Buddha_Atom_String::isValidString($deltype))
        {
            $Gallery_where .=  " AND (user_id='{$user_id}' OR user_id=0)";
        }else{

            $MemberObj = new Member();

            if(!$MemberObj->isHasMemberPrivilege($user_id))
            {
                return 0;
            }
        }

        $Db_Gallery = $GalleryObj->getFiledValues(array('id', 'goods_thumb', 'goods_img', 'goods_large', 'sourcepic'), $Gallery_where);

        if(Buddha_Atom_Array::isValidArray($Db_Gallery))
        {
            $idstr = '';

            foreach ($Db_Gallery as $k => $v)
            {
                if (Buddha_Atom_String::isValidString($v['goods_thumb']))
                {
                    @unlink(PATH_ROOT . $v['goods_thumb']);
                }

                if (Buddha_Atom_String::isValidString($v['goods_img']))
                {
                    @unlink(PATH_ROOT . $v['goods_img']);
                }

                if (Buddha_Atom_String::isValidString($v['goods_large']))
                {
                    @unlink(PATH_ROOT . $v['goods_large']);
                }

                if (Buddha_Atom_String::isValidString($v['sourcepic']))
                {
                    @unlink(PATH_ROOT . $v['sourcepic']);
                }

                $idstr .= $v['id'] . ',';
            }

            @unlink(PATH_ROOT."{$CommonObj->photoalDirectory}{$table_name}/{$table_id}");

            $idstr = rtrim($idstr, ',');

            $Gallery_where .= " AND id IN ({$idstr})";

            $Db_Gallery_Num =  $this->db->delRecords('gallery', $Gallery_where);

        }else{


            $Table_where = "id='{$table_id}'";

            if(!Buddha_Atom_String::isValidString($deltype))
            {
                $Table_where .=  " AND (user_id='{$user_id}' OR user_id=0)";
            }else{

                $MemberObj = new Member();

                if(!$MemberObj->isHasMemberPrivilege($user_id))
                {
                    return 0;
                }
            }

            $DB_Table = $this->db->getSingleFiledValues(array('id', 'goods_thumb', 'goods_img', 'goods_large', 'sourcepic'),$table_name,$Table_where);

            if(Buddha_Atom_Array::isValidArray($DB_Table))
            {
                if (Buddha_Atom_String::isValidString($DB_Table['goods_thumb']))
                {
                    @unlink(PATH_ROOT . $DB_Table['goods_thumb']);
                }

                if (Buddha_Atom_String::isValidString($DB_Table['goods_img']))
                {
                    @unlink(PATH_ROOT . $DB_Table['goods_img']);
                }

                if (Buddha_Atom_String::isValidString($DB_Table['goods_large']))
                {
                    @unlink(PATH_ROOT . $DB_Table['goods_large']);
                }

                if (Buddha_Atom_String::isValidString($DB_Table['sourcepic']))
                {
                    @unlink(PATH_ROOT . $DB_Table['sourcepic']);
                }
            }
        }

        $Table_where = "id='{$table_id}'";

        if(!Buddha_Atom_String::isValidString($deltype))
        {
            $Table_where .=  " AND (user_id='{$user_id}' OR user_id=0)";
        }else{

            $MemberObj = new Member();

            if(!$MemberObj->isHasMemberPrivilege($user_id))
            {
                return 0;
            }
        }

        $Db_Table_Num = $this->db->delRecords($table_name, $Table_where);

        return $Db_Table_Num;

    }

    /**
     * @param  $table_name           来源于哪一张相册名称的添加
     * @param $photoal_id           相册ID的
     * @param  $user_id              添加相册人的用户ID
     * @return int|void
     * 往Moregallery相册中  添加图片
     */

    public function GellerySingledel($table_name='supply',$photoalbu_id,$user_id=0)
    {
        if (!Buddha_Atom_String::isValidString($photoalbu_id))
        {
            return 0;
        }

        $GalleryObj = new Gallery();

        $Gallery_where = "id='{$photoalbu_id}'";

        $Gallery_where .=  " AND (user_id='{$user_id}' OR user_id=0)";

        $Db_Gallery = $GalleryObj->getFiledValues(array('id', 'goods_thumb', 'goods_img', 'goods_large', 'sourcepic'), $Gallery_where);

        if(Buddha_Atom_Array::isValidArray($Db_Gallery))
        {
            if (Buddha_Atom_String::isValidString($Db_Gallery['goods_thumb'])) {
                @unlink(PATH_ROOT . $Db_Gallery['goods_thumb']);
            }

            if (Buddha_Atom_String::isValidString($Db_Gallery['goods_img'])) {
                @unlink(PATH_ROOT . $Db_Gallery['goods_img']);
            }

            if (Buddha_Atom_String::isValidString($Db_Gallery['goods_large'])) {
                @unlink(PATH_ROOT . $Db_Gallery['goods_large']);
            }

            if (Buddha_Atom_String::isValidString($Db_Gallery['sourcepic'])) {
                @unlink(PATH_ROOT . $Db_Gallery['sourcepic']);
            }

            $Db_Gallery_Num = $this->db->delRecords('gallery', $Gallery_where);
        }
        return $Db_Gallery_Num;

    }


    /**
     * @param $goods_desc
     * @param $goods_id
     * @param string $tablename
     * @return mixed
     * 富文本框中的图片上传
     */
    public function base_upload($goods_desc,$goods_id,$tablename=''){

        //首先正则匹配出文章所有图片

        if(preg_match_all('/src=[\'\"]data:?([^\'\"]*)[\'\"]?/i',$goods_desc,$arr)){ //首先正则匹配出文章所有图片

            $base64_img = $arr[1];
            if($tablename==''){
                $up_dir = PATH_ROOT . "storage/quill/{$goods_id}/";//文件上传路径
            }else{
                $up_dir = PATH_ROOT . "storage/quill/{$tablename}/{$goods_id}/";//文件上传路径
            }
            if(!file_exists($up_dir))
            @mkdir($up_dir,0777,true);
            foreach ($base64_img as $k => $v){
                if(preg_match('/^(image\/(\w+);base64,)/',$v,$result)){
                    $type = $result[2];
                    if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                        $new_file = $up_dir.date('YmdHis_').mt_rand(100000,999999).'.'.$type;
                        //print_r($new_file);

                        if(file_put_contents($new_file,base64_decode(str_replace($result[1],'',$v)))){//把一个字符串写入文件中
                            $img_path = str_replace('../../..','',$new_file);
                            //echo $img_path,'<br/>';
                            if($k == 0){
                                $saveData=str_replace($v,$img_path,$goods_desc);//将文章的base64地址替换成网站的图片址
                            }else{
                                $saveData=str_replace($v,$img_path,$saveData);//将文章的base64地址替换成网站的图片址
                            }
                            $saveData=str_replace('data:','', $saveData);
                            $this->image_resize($img_path,$img_path, 640, 640);//生成缩略图
                            //$img_arr[$k] = $img_path;
                        }else{
                            //echo '图片上传失败<br/>';
                        }
                    }else{
                        //文件类型错误
                        echo '图片上传类型错误';
                    }
                }
            }
            return $saveData;
        }else{
            return $goods_desc;
        }
    }

    public function image_resize($f, $t, $tw, $th){
        //按指定大小生成缩略图，而且不变形，缩略图函数
        $temp = array(1=>'gif', 2=>'jpeg', 3=>'png',4=>'jpg');
        list($fw, $fh, $tmp) = getimagesize($f);
        if(!$temp[$tmp]){
            return false;
        }
        $tmp = $temp[$tmp];
        $infunc = "imagecreatefrom$tmp";
        $outfunc = "image$tmp";
 
        $fimg = $infunc($f);
        //使缩略后的图片不变形，并且限制在缩略图宽高范围内
        if($fw/$tw > $fh/$th){
            $th = $tw*($fh/$fw);
        }else{
            $tw = $th*($fw/$fh);
        }
        $timg = imagecreatetruecolor($tw, $th);
        imagecopyresampled($timg, $fimg, 0,0, 0,0, $tw,$th, $fw,$fh);
        if($outfunc($timg, $t)){
                return true;
        }else{
                return false;
        }
    }
    public function deleteDir($dir){
        if (!$handle = @opendir($dir)) {
             return false;
        }
        while (false !== ($file = readdir($handle))) {
            if ($file !== "." && $file !== "..") {//排除当前目录与父级目录
                $file = $dir . '/' . $file;
                if (is_dir($file)) {                     
                    $this->deleteDir($file);
                } else {
                    @unlink($file);
                }
            }
        }
        @rmdir($dir);
    }




}