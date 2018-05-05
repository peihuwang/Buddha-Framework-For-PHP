<?php
class Goodsimages extends  Buddha_App_Model{
    public function __construct(){
        parent::__construct();
        $this->table = strtolower(__CLASS__);
    }
    public function delGelleryimage($goods_id){
        $goodimg= $this->getFiledValues('',"goods_id='{$goods_id}'");
        if(is_array($goodimg) and count($goodimg)){
            foreach($goodimg as $k=>$v){
                $this->del($v['id']);
                @unlink(PATH_ROOT . $v ['goods_thumb'] );
                @unlink(PATH_ROOT . $v ['goods_img']);
                @unlink(PATH_ROOT . $v ['goods_large']);
                @unlink(PATH_ROOT . $v ['sourcepic'] );
                rmdir(PATH_ROOT . 'storage/goods/' . $goods_id);
            }
        }
    }

    public function pcaddimage($dataImg = array(),$goods_id,$actionval='goods'){
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

                $sourcepic = str_replace("storage/{$actionval}/{$goods_id}/",'',$Image);
                $data['goods_id'] = $goods_id;
                $data['goods_thumb'] = "storage/{$actionval}/{$goods_id}/S_" . $sourcepic;
                $data['goods_img'] = "storage/{$actionval}/{$goods_id}/M_" . $sourcepic;
                $data['goods_large'] = "storage/{$actionval}/{$goods_id}/L_" . $sourcepic;

                @unlink(PATH_ROOT."storage/{$actionval}/{$goods_id}/" . $sourcepic);
                $this->add($data);
            }

        }
    }
    public function goods_base64_upload($image,$goods_id,$tablename=''){//商品图片上传
        $base64_img = $image;
        $up_dir = PATH_ROOT . "storage/goods/{$goods_id}/";//文件上传路径
        if(!file_exists($up_dir)){
            @mkdir($up_dir,0777,true);
        }
        foreach ($base64_img as $k => $v){
            if(preg_match('/^(data:image\/(\w+);base64,)/',$v,$result)){
                $type = $result[2];
                if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                    $new_file = $up_dir.date('YmdHis_').mt_rand(100000,999999).'.'.$type;
                    if(file_put_contents($new_file,base64_decode(str_replace($result[1],'',$v)))){//把一个字符串写入文件中
                        $img_path = str_replace('../../..','',$new_file);
                        //$saveData=str_replace('data:','', $saveData);
                        $this->image_resize($img_path,$img_path, 640, 640);//生成缩略图
                        $img = substr($img_path,stripos($img_path,'/s')+1);
                        if($k == 0){
                            $data['isdefault'] = 1;
                            $imagepath = $img;
                        }
                        $data['goods_id'] = $goods_id;
                        $data['goods_thumb'] = $img;
                        $data['goods_img'] = $img;
                        $data['goods_large'] = $img;
                        $this->add($data);
                    }else{
                        //echo '图片上传失败<br/>';
                    }
                }else{
                    //文件类型错误
                    echo '图片上传类型错误';
                }
            }
        }
        return $imagepath;
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
    public function getGoodsImage($goods_id)
    {
       return $this->getFiledValues(array('id','goods_thumb'),"goods_id='{$goods_id}'");
    }


}