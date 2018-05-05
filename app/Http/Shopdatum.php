<?php
class Shopdatum extends  Buddha_App_Model
{
    public function __construct(){
        parent::__construct();
        $this->table = strtolower(__CLASS__);

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
    public function base64_upload_img($base64_img,$goods_id){//身份证照、营业执照
        $up_dir = "storage/Shopdatum/{$goods_id}/";//文件上传路径
        if(!file_exists($up_dir))
        @mkdir($up_dir,0777,true);
        if(preg_match('/^(data:image\/(\w+);base64,)/',$base64_img,$result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $new_file = $up_dir.date('YmdHis_').mt_rand(100000,999999).'.'.$type;
                if(file_put_contents($new_file,base64_decode(str_replace($result[1],'',$base64_img)))){//把一个字符串写入文件中
                    $img_path = str_replace('../../..','',$new_file);
                    //echo $img_path,'<br/>';
                    $this->image_resize($img_path,$img_path, 640, 640);//生成缩略图
                    //$img_arr[$k] = $img_path;
                }

                return $new_file;
            }else{
                //文件类型错误
                echo '图片上传类型错误';
            }
        }
    }
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








}