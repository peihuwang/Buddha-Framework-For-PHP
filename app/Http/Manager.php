<?php
class Manager extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table ='member';

    }

    public  function cache_add($catalog_dir,$file,$shopcat_array){
        if(is_dir($catalog_dir)){//目录存在
            if(file_exists($file)){//文件存在
                if(fopen($file,'w+')){
                    $res=  file_put_contents($file,serialize($shopcat_array));//写入缓存//返回字节数
                }
            }else{
                fopen($file, "w");
            }
        }else{//目录不存在
            mkdir(iconv("UTF-8", "GBK", $catalog_dir),0777,true);
        }
        return $res;
    }
    public function cache_read($file){
        $handle=fopen($file,'r');
        $cacheArray=unserialize(fread($handle,filesize ($file)));
        return $cacheArray;
    }
}