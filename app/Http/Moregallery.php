<?php
class Moregallery extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }



    /**
     * @param  $table_name           来源于哪一张相册名称的添加
     * @param  $table_id             来源于哪一张相册ID的添加
     * @param  $user_id              添加相册人的用户ID
     * @param int $b_display           来源于手机海慧寺Pc（2手机；1Pc）
     * @param string $webfield
     * @return array
     * 编辑时：查询相册
     */
    public function getEditGoodsImage($table_name,$table_id,$user_id=0,$b_display=2,$webfield='file')
    {
        $Db_Moregallery = array();
        if (!Buddha_Atom_String::isValidString($table_name)
            or !Buddha_Atom_String::isValidString($table_id)
            or !Buddha_Atom_String::isValidString($table_name))
        {
            return $Db_Moregallery;
        }

        $filedarr =  array('id');

        if($b_display==2)
        {
            array_push($filedarr,'goods_thumb as img');

        }elseif($b_display==1){

            array_push($filedarr,'goods_img as img');

        }

        $where = " goods_id='{$table_id}' AND tablename='{$table_name}' AND webfield='{$webfield}' AND (user_id='{$user_id}' or user_id=0)";

        $Db_Moregallery = $this->getFiledValues($filedarr,$where);


        if(!Buddha_Atom_Array::isValidArray($Db_Moregallery))
        {
            return array();
        }

        $CommonObj = new Common();

        foreach ($Db_Moregallery as $k=>$v)
        {

            $Db_Moregallery[$k]['img'] =$CommonObj->handleImgSlashByImgurl($v['img']);

        }

        return $Db_Moregallery;
    }








    /**
     * @param  $table_name           来源于哪一张相册名称的添加
     * @param  $table_id             来源于哪一张相册ID的添加
     * @param  $user_id              添加相册人的用户ID
     * @return int|void
     * 往Moregallery相册中  添加图片
     *  特别说明 setFirstGalleryImgToSupply 这个方法需要每一个都要创建
     * 删除该Id下所有图片
     */

    public function Moregallerydel($table_name,$table_id,$user_id=0,$deltype)
    {
        if (!Buddha_Atom_String::isValidString($table_name))
        {
            return 0;
        }

        if (!Buddha_Atom_String::isValidString($table_id)) {
            return 0;
        }

        $MoregalleryObj = new Moregallery();
        $CommonObj = new Common();

        $Moregallery_where = "tablename='{$table_name}' AND goods_id='{$table_id}'";

        if(!Buddha_Atom_String::isValidString($deltype))
        {
            $Moregallery_where .=  " AND (user_id='{$user_id}' OR user_id=0)";
        }else{

            $MemberObj = new Member();

            if(!$MemberObj->isHasMemberPrivilege($user_id))
            {
                return 0;
            }
        }


        $Db_Moregallery = $MoregalleryObj->getFiledValues(array('id', 'goods_thumb', 'goods_img', 'goods_large', 'sourcepic'), $Moregallery_where);


        if(Buddha_Atom_Array::isValidArray($Db_Moregallery))
        {
            $idstr = '';

            foreach ($Db_Moregallery as $k => $v)
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

            $Moregallery_where .= " AND id IN ({$idstr})";

            $Db_Moregallery_Num =  $this->db->delRecords('moregallery', $Moregallery_where);

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
     * @param  $photoalbu_id         相册Id
     * @param  $user_id              添加相册人的用户ID
     * @return int|void
     * 往Moregallery相册中  添加图片
     *  特别说明 setFirstGalleryImgToSupply 这个方法需要每一个都要创建
     * 删除相册里面的单张图片
     */

    public function MoregallerySingledel($table_name,$photoalbu_id,$user_id=0)
    {
        if (!Buddha_Atom_String::isValidString($table_name))
        {
            return 0;
        }

        if (!Buddha_Atom_String::isValidString($photoalbu_id)) {
            return 0;
        }

        $MoregalleryObj = new Moregallery();
        $CommonObj = new Common();

        $Moregallery_where = "tablename='{$table_name}' AND id='{$photoalbu_id}'";

        $Moregallery_where .=  " AND (user_id='{$user_id}' OR user_id=0)";

        $Db_Moregallery = $MoregalleryObj->getSingleFiledValues(array('id', 'goods_thumb', 'goods_img', 'goods_large', 'sourcepic'), $Moregallery_where);


        if(Buddha_Atom_Array::isValidArray($Db_Moregallery))
        {

            if (Buddha_Atom_String::isValidString($Db_Moregallery['goods_thumb']))
            {
                @unlink(PATH_ROOT . $Db_Moregallery['goods_thumb']);
            }

            if (Buddha_Atom_String::isValidString($Db_Moregallery['goods_img']))
            {
                @unlink(PATH_ROOT . $Db_Moregallery['goods_img']);
            }

            if (Buddha_Atom_String::isValidString($Db_Moregallery['goods_large']))
            {
                @unlink(PATH_ROOT . $Db_Moregallery['goods_large']);
            }

            if (Buddha_Atom_String::isValidString($Db_Moregallery['sourcepic']))
            {
                @unlink(PATH_ROOT . $Db_Moregallery['sourcepic']);
            }

            $Db_Moregallery_Num =  $this->db->delRecords('moregallery', $Moregallery_where);
        }

        return $Db_Moregallery_Num;

    }










    /**
     * @param $img                  要上传的相册数组
     * @param $table_name           来源于哪一张相册名称的添加
     * @param $table_id             来源于哪一张相册ID的添加
     * @param $shop_id              属于哪一个店铺下的店铺ID
     * @param $user_id              添加相册人的用户ID
     * @param string $webfield      该图片的代表字段默认为 file
     * @return int|void
     * 往Moregallery相册中  添加图片
     *  特别说明 setFirstGalleryImgToSupply 这个方法需要每一个都要创建
     */
    public function Moregalleryadd($img,$table_name,$table_id,$shop_id,$user_id,$webfield='file')
    {

        if(!Buddha_Atom_Array::isValidArray($img))
        {
            return 0;
        }

        if(!Buddha_Atom_String::isValidString($table_id))
        {
            return 0;
        }

        if(!Buddha_Atom_String::isValidString($shop_id))
        {
            return 0;
        }

        $MoregalleryObj = new Moregallery();

        $CommonObj = new Common();
        $Db_Common = $CommonObj->audittable();

        foreach ($Db_Common as $k=>$v)
        {
            if($v['name']==$table_name)
            {
                $Table = ucfirst($v['name']);
                $TableObj = New $Table;
            }
        }


//        if($table_name=='heartpro')
//        {
//           $TableObj = $HeartproObj = new Heartpro();
//        }elseif($table_name=='activity')
//        {
//            $TableObj = $ActivityObj = new Activity();
//        }elseif($table_name=='demand')
//        {
//            $TableObj = $DemandObj = new Demand();
//        }elseif($table_name=='lease')
//        {
//            $TableObj = $LeaseObj = new Lease();
//        }elseif($table_name=='recruit')
//        {
//            $TableObj = $RecruitObj = new Recruit();
//
//        }elseif($table_name=='singleinformation')
//        {
//            $TableObj = $SingleinformationObj = new Singleinformation();
//        }



        /*封面照相册*/
        if(Buddha_Atom_Array::isValidArray($img))
        {

            $savePath = "storage/{$table_name}/{$table_id}/";


            if(!file_exists(PATH_ROOT.$savePath)){
                mkdir(PATH_ROOT.$savePath, 0777);
            }

            foreach($img as $k=>$v)
            {
                $temp_img_arr = explode(',', $v);
                $temp_base64_string = $temp_img_arr[1];
                $output_file = date('Ymdhis',time()). "-{$k}.jpg";
                $filePath = PATH_ROOT.$savePath.$output_file;

                Buddha_Atom_File::base64contentToImg($filePath,$temp_base64_string);
                Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
                $result_img = $savePath.''.$output_file;
                $MoreImage[] = "{$result_img}";
            }

            if(Buddha_Atom_Array::isValidArray($MoreImage))
            {
                $Db_Moregallery_id = $MoregalleryObj->addImageArrToMoregallery($MoreImage,$table_id,$savePath,$shop_id,$webfield,$table_name,$user_id);

                /*把封面照设为默认展示图片并把相应的图片路径更新到 Table 表中*/
                $TableObj->setFirstGalleryImgToSupply($table_id,$table_name,$webfield);
            }
        }
        return $Db_Moregallery_id;
    }


    /**
     * @param $table_id
     * @param int $b_display
     * @return array
     * 通过 tableid 获取Gallery相册
     */
    public function getMoregalleryByTableid($table_id,$table_name,$filed='file',$b_display=2)
    {
        $b_display = (int)$b_display;

        $filedarr = array();
        $Db_Moregallery = array();
        if($b_display==2)
        {
            array_push($filedarr,'goods_img as img');
        }elseif($b_display==1)
        {
            array_push($filedarr,'goods_large as img');
        }
        if(!Buddha_Atom_Array::isValidArray($filedarr))
        {
            $Db_Moregallery = array();
        }else{
            $Db_Moregallery = $this->getFiledValues($filedarr,"goods_id='{$table_id}' AND tablename='{$table_name}' AND webfield='{$filed}'");
            if(!Buddha_Atom_Array::isValidArray($Db_Moregallery)){
                $Db_Moregallery = array();
            }
        }

        return $Db_Moregallery;
    }



    /**
     * 判断 moregallery 相册是否有此记录
     * @param $album_id
     * @return int
     */
    public function isHasRecord($moregallery_id)
    {
        $num = $this->countRecords("id='{$moregallery_id}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }



    public function addImageArrToMoregallery($MoreImage,$id,$savePath,$shop_id,$uploadfield,$table_name,$user_id=0)
    {

        $id = (int)$id;
        $shop_id = (int)$shop_id;
        if(Buddha_Atom_Array::isValidArray($MoreImage) and $id>0)
        {
            foreach($MoreImage as $k=>$v)
            {
                $small_img = $medium_img = $large_img = '';
                $source_file_location = PATH_ROOT.$v;
                $source_filename  = str_replace($savePath, '', $v);

                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 320, 320, 'S_');
                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 640, 640, 'M_');
                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 1200, 640, 'L_');

                $small_img = $savePath."/S_" . $source_filename;
                $medium_img = $savePath."/M_" . $source_filename;
                $large_img = $savePath."/L_" . $source_filename;

                $small_img = str_replace("//","/",$small_img);
                $medium_img = str_replace("//","/",$medium_img);
                $large_img= str_replace("//","/",$large_img);

                $data = array();
                $data['goods_id'] = $id;
                /*来源于哪一张表*/
                $data['tablename'] = $table_name;
                /*小图*/
                $data['goods_thumb'] = $small_img;
                /*中图*/
                $data['goods_img'] = $medium_img;
                /*大图*/
                $data['goods_large'] = $large_img;

                $data['shop_id'] = $shop_id;
                /*来源于哪一个字段你的上传*/
                $data['webfield'] = $uploadfield;

                $data['sort'] = $k;
                $data['user_id'] = $user_id;

                $this->db->addRecords ($data, 'moregallery' );
                @unlink($source_file_location);
            }
        }
    }



    /**
     * @param $moregallery_id
     * @param $table_name
     * @param $user_id
     * @return int
     *  删除moregallery中的图片
     *
     */


    public function isDeleteImageFileOk($moregallery_id,$table_name,$user_id)
    {
        $MysqlplusObj = new Mysqlplus();
        if (!$MysqlplusObj->isValidTable($table_name))
        {
            return 0;
        }

        if (strlen($table_name) < 2 or $table_name == 'moregallery')
        {
            if (!$this->isHasRecord($moregallery_id))
            {
                return 0;
            }
            /*进行选择表中的记录*/
            $Db_Moregallery = $this->getSingleFiledValues('', "id='{$moregallery_id}' ");

            /*如果Moregallery 相册中有没有用户ID：如果有则判断*/
            if ($Db_Moregallery['user_id']) {
                /*判断数据的主人对不对 如果不对返0 对返1*/
                $db_user_id = $Db_Moregallery['user_id'];
                if ($db_user_id > 0 and $db_user_id != $user_id)
                {
                    return 0;
                }

                /*如果没有，则需要查询*/
            } else {
                $sql = "SELECT count（*）AS total  
                        FORM  {$this->prefix}{$table_name}  as t 
                        LEFT JOIN  moregallery m
                        ON t.id = m.goods_id
                        WHERE m.id='$moregallery_id' AND m.tablename='{$table_name}' AND t.user_id='$user_id'";

                $DB_Table_count = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

                /*判断数据的主人对不对 如果不对返0 对返1*/

                if ($DB_Table_count[0]['total'] == 0) {
                    return 0;
                }
            }


            if (Buddha_Atom_String::isValidString($Db_Moregallery['goods_thumb'])) {
                @unlink(PATH_ROOT . $Db_Moregallery['goods_thumb']);
            }
            if (Buddha_Atom_String::isValidString($Db_Moregallery['goods_img'])) {
                @unlink(PATH_ROOT . $Db_Moregallery['goods_img']);
            }
            if (Buddha_Atom_String::isValidString($Db_Moregallery['goods_large'])) {
                @unlink(PATH_ROOT . $Db_Moregallery['goods_large']);
            }

            if (Buddha_Atom_String::isValidString($Db_Moregallery['sourcepic'])) {
                @unlink(PATH_ROOT . $Db_Moregallery['sourcepic']);
            }

            if(!file_exists(PATH_ROOT.$Db_Moregallery['goods_thumb'])){

                $Db_Moregallery=$this->db->getSingleFiledValues(array('goods_id'),'moregallery'," id='{$moregallery_id}' AND tablename='{$table_name}' AND isdefault=1");
                $data["{$table_name}_thumb"]='';
                $data["{$table_name}_img"]='';
                $data["{$table_name}_large"]='';
                $data['sourcepic']='';
                $Db_Table=$this->db->edit ($data,$table_name," id='{$Db_Moregallery['goods_id']}' ");
                $this->db->delRecords ( 'moregallery', "id='{goods_thumb}' " );
                if($Db_Table){
                    return 1;
                }else{
                    return 0;
                }
            }else{
                return 0;
            }

        }
    }






    public function base64contentToImg($filePath,$base64_string){
        Buddha_Atom_Secury::setFileSecury($filePath);
        $ifp = fopen($filePath, "wb");
        fwrite($ifp, base64_decode($base64_string));
        fclose($ifp);

    }

    public function getImageInfo($filePath){
        return $imginfo = @getimagesize($filePath);//GetImageSize  取得图片的长宽。
    }

    public  function resolveImageForRotate($filePath,$base64_string=NULL){
        $imginfo =  $this->getImageInfo($filePath);
//        if($base64_string!=NULL){
//            $this->base64contentToImg($filePath,$base64_string);
//            $image = imagecreatefromstring($base64_string);
//        }else{
//            $image = imagecreatefromgd($filePath);
//        }
        var_dump($imginfo);
        $image= $base64_string;
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


/*  @addimage  手机上传多张图片
 * @ $goods_id  添加成功返回的ID
 * @$tablename  表名
 * */
    public function addimage($dataImg = array(), $goods_id,$tablename){
//        var_dump($dataImg);
        foreach($dataImg as $k=>$v){
            if (base64_encode(base64_decode($v))) {
                $imgurl = explode(',', $v);
                @mkdir(PATH_ROOT . "storage/".$tablename."/".$goods_id ."/"); // 如果不存在则创建
                $savePath = 'storage/'.$tablename.'/' . $goods_id . '/';
                if (!file_exists($savePath)) {  //  file_exists 检测文件目录是否存在
                    @mkdir($savePath, 0777);
                }
                $base64_string = $v;
                $output_file = date('ymdhis',time()) . rand(10000, 99999) . '.jpg';
                $filePath = PATH_ROOT . $savePath . $output_file;

                $this->resolveImageForRotate($filePath,$base64_string);
                Buddha_Tool_File::thumbImageSameWidth($filePath, 320, 320, 'S_');
                Buddha_Tool_File::thumbImageSameWidth($filePath, 640, 640, 'M_');
                Buddha_Tool_File::thumbImageSameWidth($filePath, 1200, 1200, 'L_');
                $data=array();
                $data['tablename '] = $tablename;
                $data['goods_thumb'] = $savePath . 'S_' . $output_file;
                $data['goods_img'] = $savePath . 'M_' . $output_file;
                $data['goods_large'] = $savePath . 'L_' . $output_file;
                $data['sourcepic'] = $savePath . $output_file;
                $data['goods_id'] =$goods_id;
            }
            $this->add($data);
        }
    }
//$shopidarr   如果为广告时的对应ID
//$webfield 添加进来对应页面的字段名称：主要针对一个页面有多个相册或有多个单图
//$moregallery_id_arr  如果为更改时的ID数组
    public function pcaddimage($webfield,$dataImg = array(),$goods_id,$table_name='supply',$user_id=0,$shopidarr=array(),$moregallery_id_arr = array())
    {
        $where = "goods_id='{$goods_id}' AND tablename='{$table_name}' AND webfield='{$webfield}' AND user_id='{$user_id}'";
        $MoregalleryObj = new Moregallery();
        $Db_Moregallery_num = $MoregalleryObj->countRecords($where);

        $Db_Moregallery_imgkey = $MoregalleryObj->getSingleFiledValues(array('imgkey'),$where." ORDER BY id DESC LIMIT 1 ");


        if(Buddha_Atom_String::isValidString($Db_Moregallery_imgkey['imgkey']))
        {

            $imgkey = $Db_Moregallery_imgkey['imgkey'];
        }else{

            $imgkey = $Db_Moregallery_num;
        }


        $Db_Moregallery_sort = $MoregalleryObj->getSingleFiledValues(array('sort'),$where." ORDER BY id DESC LIMIT 1 ");

        if(Buddha_Atom_String::isValidString($Db_Moregallery_sort['sort']))
        {
            $sort = $Db_Moregallery_imgkey['sort'];
        }else{
            $sort = $Db_Moregallery_num;
        }

        $strarr=array();
        $str=array();
        if(!empty($shopidarr)){
            foreach($shopidarr as $k=>$v){//重组原因：因为在修改时对应的的开始位置并非是0开始：因为之前的位置已经占用
                $str['kk']=$k;
                $str['vv']=$v;
                $strarr[]=$str;
            }
        }

        //先根据ID查询该条记录是否存在（存在即为编辑）
        foreach($dataImg as $k=>$v){
            if($v){
                $data = array();
                //存在图片
                $Image= $v;
               // $this->resolveImageForRotate(PATH_ROOT.$Image,NULL);
                if($Image){
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 1200, 1200, 'L_');
                }

                $filename='storage/'.$table_name.'/'.$goods_id.'/';
                $sourcepic = str_replace($filename,'',$Image);
                $data['goods_thumb'] ='/'.$filename."S_".$sourcepic;
                $data['goods_img'] = '/'.$filename."M_".$sourcepic;
                $data['goods_large'] = '/'.$filename."L_".$sourcepic;
                $data['sourcepic'] = '/'.$filename.$sourcepic;
                $data['goods_id'] =$goods_id;
                $data['imgkey']= $strarr[$k]['kk'];
                $data['tablename'] =$table_name;
                $data['user_id'] =$user_id;
                $data['imgkey'] = $imgkey+1;
                $data['sort'] = $sort+1;

                if(!empty($shopidarr)) {
                    if (count($shopidarr)) {
                        $data['shop_id'] = $strarr[$k]['vv'];
                    }
                }
            }

            if(empty($moregallery_id_arr)){
                $data['webfield'] =$webfield;
                $insert_moregallery_id[]= $this->add($data);
            }else{
                $insert_moregallery_id[]= $this->edit($data,$moregallery_id_arr[$k]['id']);
            }
        }
        return $insert_moregallery_id;

    }



    public function getGoodsImage($goods_id,$tablename='',$webfield='',$b_display=2)
    {
        $where="goods_id='{$goods_id}'";

        if(Buddha_Atom_String::isValidString($webfield)){
            $where.=" AND webfield='{$webfield}'";
        }

        if(Buddha_Atom_String::isValidString($tablename)){
            $where.=" AND tablename='{$tablename}'";
        }
        $filed=array('id');
        if($b_display==2)
        {
            array_push($filed,'goods_thumb');
        }elseif($b_display==1)
        {
            array_push($filed,'goods_img as goods_thumb');
        }

       return $this->getFiledValues($filed,$where);
    }

    public function delGelleryimage($id){
       $goodimg= $this->getFiledValues('',"id='{$id}'");
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

    /* @image_resize  单图压缩
     * @ $f 域名
     * @ $t 域名后的路径
     * @ $tw  宽
     * @ $th高
     **/
    public function image_resize($f, $t, $tw, $th){
        // 按指定大小生成缩略图，而且不变形，缩略图函数
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

    public  function del_image($id,$gimages){
        $MoregalleryObj=new Moregallery();
        $thumimg=array();
        if(!$id){
            $thumimg['isok']='false';
            $thumimg['data']='参数错误';
        }
        $mid=$gimages['id'];
        if($gimages and $gimages['isdefault']==0){
            $MoregalleryObj->del($mid);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }else{
           $aa= $MoregalleryObj->del($mid);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }
        return $thumimg;
    }

    public function base_upload($goods_desc,$goods_id,$tablename='',$field=''){//富文本框中的图片上传
       $num= preg_match_all('/src=[\'\"]data:?([^\'\"]*)[\'\"]?/i',$goods_desc,$arr);//首先正则匹配出文章所有图片
        if($num>0){
            if(count($arr)){
                $base64_img = $arr[1];
                if($tablename==''){
                    $up_dir = PATH_ROOT . "storage/quill/{$goods_id}/";//文件上传路径
                }else{

                    if($field){
                        $up_dir = PATH_ROOT . "storage/quill/{$tablename}/{$field}/{$goods_id}/";//文件上传路径
                    }else{
                        $up_dir = PATH_ROOT . "storage/quill/{$tablename}/{$goods_id}/";//文件上传路径
                    }
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
            }
        }else{
            return $goods_desc;
        }
    }












}