<?php
class Chatfrialbum extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * 判断这个相册是不是本人的
     * @param $user_id
     * @param $chatfrialbum_id
     * @return int
     * @author wph 2017-12-18
     */
    public function isOwnAlbum($user_id,$chatfrialbum_id){

        $num = $this->countRecords("user_id='{$user_id}' AND id='{$chatfrialbum_id}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * 删除相册
     * @param $user_id
     * @param $chatfrialbum_id
     * @author wph 2017-12-18
     */
    public function deleteAlbum($user_id,$chatfrialbum_id){

        if($this->isOwnAlbum($user_id,$chatfrialbum_id)){

            $num =  $this->countRecords("user_id='{$user_id}' AND id='{$chatfrialbum_id}' ");
            //num==1要删除相册同时删除文章
            if($num>=1){
                $Db_Chatfrialbum = $this->getSingleFiledValues(array('chatfricircle_id','image'),"user_id='{$user_id}' AND id='{$chatfrialbum_id}' ");
                $chatfricircle_id = $Db_Chatfrialbum['chatfricircle_id'];
                $image = $Db_Chatfrialbum['image'];

                unlink(PATH_ROOT.$image);
                if(!file_exists(PATH_ROOT.$image)){

                    $this->delRecords("user_id='{$user_id}' AND id='{$chatfrialbum_id}' ");

                    if($num==1){
                        $ChatfricircleObj = new Chatfricircle();
                        $ChatfricircleObj->delRecords("id='{$chatfricircle_id}' AND user_id='{$user_id}' ")   ;
                    }

                }

            }




        }

    }


    /**
     * 返回APP第二行的标题
     * @param $chatfricircle_id
     * @param $chatfrialbum_id
     * @return string
     * @author wph 2017-12-18
     */
    public function getTitle2($chatfricircle_id,$chatfrialbum_id){
        $chatfricircle_id = (int)$chatfricircle_id;
        $chatfrialbum_id= (int)$chatfrialbum_id;
        $total = $this->countRecords(" chatfricircle_id='{$chatfricircle_id}'  ");
        if($total==0){
            return "1/1";
        }else{

            $num = $this->countRecords("id<='{$chatfrialbum_id}' AND chatfricircle_id='{$chatfricircle_id}'  ");
            return "{$num}/{$total}";
        }

    }




}