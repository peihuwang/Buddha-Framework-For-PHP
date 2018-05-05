<?php

/**
 * Class ChatfricircleController
 */
class ChatfricircleController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

        //权限
        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if ($webface_access_token == '') {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444002, 'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num = $ApptokenObj->getTokenNum($webface_access_token);
        if ($num == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444003, 'webface_access_token不正确请从新获取');
        }


    }

    public function changealbumcover(){

        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $UserbasicObj = new Userbasic();
        $JsonimageObj = new Jsonimage();

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $image_arr=Buddha_Http_Input::getParameter('image_arr');


        if(Buddha_Atom_String::isJson($image_arr)){
            $image_arr = json_decode($image_arr);
        }

        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        $MoreImage = array();
        $savePath="storage/userbasic/";
        if(!file_exists(PATH_ROOT.$savePath)){
            @mkdir(PATH_ROOT.$savePath, 0777);
        }
        if(Buddha_Atom_Array::isValidArray($image_arr)){
            foreach($image_arr as $k=>$v){
                $temp_img_arr = explode(',', $v);
                $temp_base64_string = $temp_img_arr[1];
                $output_file = "{$user_id}.jpg";
                $filePath =PATH_ROOT.$savePath.$output_file;
                Buddha_Atom_File::base64contentToImg($filePath,$temp_base64_string);
                Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
                $result_img = $savePath.''.$output_file;
                $MoreImage[] = "{$result_img}";
            }
        }

        if(!Buddha_Atom_Array::isValidArray($MoreImage)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '图片生成失败');

        }




        $albumcover = $MoreImage[0];
        $UserbasicObj->addOrUpdateAlbumCover($user_id,$albumcover);

        if(Buddha_Atom_String::isValidString($albumcover)){
            $albumcover = $host.$albumcover;
        }else{
            $albumcover='';
        }
        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['albumcover'] = $albumcover;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '更换相册封面');


    }

   public function circlepublish(){


       if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
       }

       $UserObj = new User();
       $ChatfricircleObj = new Chatfricircle();
       $ChatfrialbumObj = new Chatfrialbum();

       $usertoken =  Buddha_Http_Input::getParameter('usertoken');

       /*0图片或者视频 1:文字*/
       $type =  Buddha_Http_Input::getParameter('type');
       /*链接*/
       $link =  Buddha_Http_Input::getParameter('link');

       /*朋友圈内容*/
       $content =  Buddha_Http_Input::getParameter('content');
       /*0=公开（所有朋友可见）1=私密（仅自己可见）2=部分可见（选中的朋友可见） 3=不给谁看（选中的朋友不可见）*/
       $seetype =  (int)Buddha_Http_Input::getParameter('seetype');
       $year = Buddha_Atom_String::getYearStr();
       $month = Buddha_Atom_String::getMonthStr();
       $day = Buddha_Atom_String::getDayStr();
       /*经度*/
       $lat =  Buddha_Http_Input::getParameter('lat');
       /*纬度*/
       $lng =  Buddha_Http_Input::getParameter('lng');
       $addr =  Buddha_Http_Input::getParameter('addr');







       $image_arr=Buddha_Http_Input::getParameter('image_arr');
       $partseearr=Buddha_Http_Input::getParameter('partseearr');//部分好友可见会员列表 ||之间放会员内码id 例如  |1|2|3|
       $noseearr=Buddha_Http_Input::getParameter('noseearr');//不能看此文章的会员列表 ||之间放会员内码id 例如  |1|2|3|
       $noticearr=Buddha_Http_Input::getParameter('noticearr');//||之间放会员内码id 例如  |1|2|3|

       if($seetype==0 or $seetype==1){
           $partseearr=0;
           $noseearr=0;
       }
       if($seetype==2){
           $noseearr=0;
       }
       if($seetype==3){
           $partseearr=0;
       }


       if(Buddha_Atom_String::isJson($image_arr)){
           $image_arr = json_decode($image_arr);
       }



       $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
       $fieldsarray= array('id','usertoken');
       $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
       $user_id = $Db_User['id'];


       $noticearr = $ChatfricircleObj->getNoticeArr($seetype,$user_id,$noticearr);
       if($noticearr==-1){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '提醒谁看(此人不是您的好友)');
       }

       $partseearr = $ChatfricircleObj->getPartSeeArr($seetype,$user_id,$partseearr);
       if($partseearr==-1){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '部分可见(此人不是您的好友)');
       }


       $noseearr = $ChatfricircleObj->getNoSeeArr($seetype,$user_id,$noseearr);
       if($noseearr==-1){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '不给谁看(此人不是您的好友)');
       }


       $MoreImage = array();
       $savePath="storage/chatfricircle/";
       if(!file_exists(PATH_ROOT.$savePath)){
           @mkdir(PATH_ROOT.$savePath, 0777);
       }

       $dir = "{$year}{$month}";
       $savePath="storage/chatfricircle/{$dir}/";
       if(!file_exists(PATH_ROOT.$savePath)){
           @mkdir(PATH_ROOT.$savePath, 0777);
       }

       //进行文件存储
       if(Buddha_Atom_Array::isValidArray($image_arr)){
           foreach($image_arr as $k=>$v){


               $temp_img_arr = explode(',', $v);
               $whichtype = $temp_img_arr[0];

               $output_file ="{$user_id}u-".date('Ymdhi',time()). "-{$k}";
               if(Buddha_Atom_String::hasNeedleString($whichtype,'image')){
                   $output_file .= ".jpg";
               }


               $temp_base64_string = $temp_img_arr[1];

               $filePath =PATH_ROOT.$savePath.$output_file;
               Buddha_Atom_File::base64contentToImg($filePath,$temp_base64_string);
               Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
               $result_img = $savePath.''.$output_file;
               $MoreImage[] = "{$result_img}";
           }
       }

 /*      if(!Buddha_Atom_Array::isValidArray($MoreImage)){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '图片或者视频生成失败');
       }*/

       $data = array();
       $data['user_id'] = $user_id;
       $data['content'] = $content;
       $data['type'] = $type;
       $data['link'] = $link;
       $data['seetype'] = $seetype;
       $data['year'] = $year;
       $data['month'] = $month;
       $data['day'] = $day;
       $data['lat'] = $lat;
       $data['lng'] = $lng;
       $data['addr'] = $addr;
       $data['lat'] = $lat;
       $data['partseearr'] = $partseearr;
       $data['noseearr'] = $noseearr;
       $data['noticearr'] = $noticearr;
       $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
       $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

       $chatfricircle_id =  $ChatfricircleObj->add($data);

       if(Buddha_Atom_Array::isValidArray($MoreImage)){

           foreach($MoreImage as $k=>$v){

               if($k<=8){
                   $data = array();
                   $data['chatfricircle_id'] = $chatfricircle_id;
                   $data['user_id'] = $user_id;
                   $data['image'] = $v;

                   if(Buddha_Atom_String::hasNeedleString($v,'.jpg')){
                       $type = 0;
                   }else{
                       $type =1;
                   }
                   $data['type'] = $type;
                   $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                   $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                   $ChatfrialbumObj->add($data);
               }

           }



       }





       $jsondata = array();
       $jsondata['user_id'] = $user_id;
       $jsondata['usertoken'] = $usertoken;
       Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '朋友圈发表');
   }

    public function circlelist(){



        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $ChatfricircleObj = new Chatfricircle();
        $ChatfrialbumObj = new Chatfrialbum();
        $ChatfripraiseObj = new Chatfripraise();
        $ChatfriendObj = new Chatfriend();
        $Chatfricomment = new Chatfricomment();

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):6;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $friend_in_str = $ChatfriendObj->getFriendIdInStr($user_id); //2,3,4

        $getwhere =$ChatfricircleObj->getSqlWhereByUserIdStr('cc',$user_id);
        $where = " cc.buddhastatus=0 AND {$getwhere}  ";

        $sql = "select count(*) as total
                from {$this->prefix}chatfricircle as cc
                where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];

        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $sql = "SELECT cc.id as chatfricircle_id,user_id as friend_id,cc.content,cc.year,cc.month,cc.day,cc.addr,cc.createtime,cc.createtimestr,
                       cc.type,cc.link,
                       u.nickname,u.mobile,u.realname,u.logo
                FROM {$this->prefix}chatfricircle as cc
                LEFT JOIN {$this->prefix}user as u
                ON cc.user_id = u.id
                WHERE  {$where}   ORDER BY cc.createtime DESC {$limit} ";


        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach($list as $k=>$v){
            $chatfricircle_id = $v['chatfricircle_id'];
            $logo = $v['logo'];
            $list[$k]['logo'] = Buddha_Atom_String::getUserLogo($logo);
            $list[$k]['nickname'] = $UserObj->getUserNickName($v);
            $list[$k]['link'] = Buddha_Atom_String::getApiStr($v['link']);
            $list[$k]['addr'] = Buddha_Atom_String::getApiStr($v['addr']);
            $list[$k]['duration'] = Buddha_Atom_String::getDurationTimeStr($v['createtime']);
            $list[$k]['is_fraise'] =$ChatfripraiseObj->isHasValidRecord($chatfricircle_id,$user_id);


            $albumlist = $ChatfrialbumObj->getFiledValues(array('id as chatfrialbum_id','image','type'),"buddhastatus=0 AND chatfricircle_id='{$chatfricircle_id}' ");
            foreach($albumlist as $k1=>$v1){
                $image =  $v1['image'];
                $albumlist[$k1]['image'] = Buddha_Atom_String::getApiFileUrlStr($image);
            }
            $list[$k]['chatfrialbum'] = $albumlist;


            $where = " cf.buddhastatus=0 AND cf.user_id IN ({$friend_in_str}) AND cf.chatfricircle_id='{$chatfricircle_id}' OR cf.user_id='{$user_id}' ";
            $sql = "SELECT cf.id as chatfripraise_id,user_id as friend_id,cf.createtime,cf.createtimestr,
                       u.nickname,u.mobile,u.realname,u.logo
                FROM {$this->prefix}chatfripraise as cf
                LEFT JOIN {$this->prefix}user as u
                ON cf.user_id = u.id
                WHERE  {$where}  lIMIT 0,50 ";

            $praiselist = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            $nicknameall = '';
            foreach($praiselist as $k2=>$v2){
                $logo = $v2['logo'];
                $praiselist[$k2]['logo'] = Buddha_Atom_String::getUserLogo($logo);
                $praiselist[$k2]['nickname'] =$UserObj->getUserNickName($v2);
                $nicknameall.=  $praiselist[$k2]['nickname'];
                $nicknameall.=',';
            }

            $nicknameall = Buddha_Atom_String::toDeleteTailCharacter($nicknameall,1);
            $list[$k]['nicknameall'] = $nicknameall;
            $list[$k]['praiselist'] = $praiselist;


            $where = " cf.buddhastatus=0 AND cf.user_id IN ({$friend_in_str}) AND cf.chatfricircle_id='{$chatfricircle_id}' OR cf.user_id='{$user_id}' ";
            $sql = "SELECT cf.id as chatfricomment_id,user_id as friend_id,cf.createtime,cf.createtimestr,cf.content,
                       u.nickname,u.mobile,u.realname,u.logo
                FROM {$this->prefix}chatfricomment as cf
                LEFT JOIN {$this->prefix}user as u
                ON cf.user_id = u.id
                WHERE  {$where}  lIMIT 0,50 ";

            $commentlist = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            foreach($commentlist as $k3=>$v3){
                $logo = $v3['logo'];
                $commentlist[$k3]['logo'] = Buddha_Atom_String::getUserLogo($logo);
                $commentlist[$k3]['nickname'] =$UserObj->getUserNickName($v3);

            }
            $list[$k]['commentlist'] = $commentlist;

        }



        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;
        $jsondata['list'] = $list;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '朋友圈');

    }

    public function chatfripraise(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','chatfricircle_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $ChatfripraiseObj = new Chatfripraise();
        $ChatfricircleObj = new Chatfricircle();
        $ChatfriendObj = new Chatfriend();


        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $chatfricircle_id =  Buddha_Http_Input::getParameter('chatfricircle_id');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

       $ChatfripraiseObj->toFraiseOrCancelFraise($chatfricircle_id,$user_id);


       $friend_in_str = $ChatfriendObj->getFriendIdInStr($user_id); //2,3,4




        $where = " cf.buddhastatus=0 AND cf.user_id IN ({$friend_in_str}) OR cf.user_id='{$user_id}'  ";
        $sql = "SELECT cf.id as chatfripraise_id,user_id as friend_id,cf.createtime,cf.createtimestr,
                       u.nickname,u.mobile,u.realname,u.logo
                FROM {$this->prefix}chatfripraise as cf
                LEFT JOIN {$this->prefix}user as u
                ON cf.user_id = u.id
                WHERE  {$where} lIMIT 0,50 ";

        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $nicknameall = '';
        foreach($list as $k=>$v){
            $logo = $v['logo'];
            $list[$k]['logo'] = Buddha_Atom_String::getUserLogo($logo);
            $list[$k]['nickname'] =$UserObj->getUserNickName($v);
            $nicknameall.= $list[$k]['nickname'];
            $nicknameall.=',';
        }

        $nicknameall = Buddha_Atom_String::toDeleteTailCharacter($nicknameall,1);


        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['is_fraise'] = $ChatfripraiseObj->isHasValidRecord($chatfricircle_id,$user_id);
        $jsondata['nicknameall'] = $nicknameall;
        $jsondata['list'] = $list;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '点赞');


    }

    public function chatfricomment(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','chatfricircle_id','content','father_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $ChatfriendObj = new Chatfriend();
        $ChatfricommentObj = new Chatfricomment();


        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $chatfricircle_id =  Buddha_Http_Input::getParameter('chatfricircle_id');
        $content =  Buddha_Http_Input::getParameter('content');
        $father_id =  (int)Buddha_Http_Input::getParameter('father_id');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


       if(!$ChatfricommentObj->couldCommon($user_id,$father_id)){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '没有资格评论');
       }


        $data = array();
        $data['father_id'] = $father_id;
        $data['user_id'] = $user_id;
        $data['content'] = $content;
        $data['chatfricircle_id'] = $chatfricircle_id;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

        $ChatfricommentObj->add($data);


        $friend_in_str = $ChatfriendObj->getFriendIdInStr($user_id); //2,3,4




        $where = " cf.buddhastatus=0 AND cf.user_id IN ({$friend_in_str}) OR cf.user_id='{$user_id}'  ";
        $sql = "SELECT cf.id as chatfricomment_id,user_id as friend_id,cf.createtime,cf.createtimestr,cf.content,
                       u.nickname,u.mobile,u.realname,u.logo
                FROM {$this->prefix}chatfricomment as cf
                LEFT JOIN {$this->prefix}user as u
                ON cf.user_id = u.id
                WHERE  {$where} lIMIT 0,50 ";

        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $nicknameall = '';
        foreach($list as $k=>$v){
            $logo = $v['logo'];
            $list[$k]['logo'] = Buddha_Atom_String::getUserLogo($logo);
            $list[$k]['nickname'] =$UserObj->getUserNickName($v);

        }




        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['list'] = $list;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '评论');


    }
       public function chatfricommentdel(){
           if (Buddha_Http_Input::checkParameter(array('usertoken','chatfricomment_id'))) {
               Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
           }

           $UserObj = new User();
           $ChatfriendObj = new Chatfriend();
           $ChatfricommentObj = new Chatfricomment();


           $usertoken =  Buddha_Http_Input::getParameter('usertoken');
           $chatfricomment_id =  Buddha_Http_Input::getParameter('chatfricomment_id');


           $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
           $fieldsarray= array('id','usertoken');
           $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
           $user_id = $Db_User['id'];


           if(!$ChatfricommentObj->isOwerCommon($chatfricomment_id,$user_id)){
               Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 0, '不能删除别人的评论');
           }

           $ChatfricommentObj->delRecords("user_id='{$user_id}' AND id='{$chatfricomment_id}'  ");


           $friend_in_str = $ChatfriendObj->getFriendIdInStr($user_id); //2,3,4




           $where = " cf.buddhastatus=0 AND cf.user_id IN ({$friend_in_str}) OR cf.user_id='{$user_id}'  ";
           $sql = "SELECT cf.id as chatfricomment_id,user_id as friend_id,cf.createtime,cf.createtimestr,cf.content,
                       u.nickname,u.mobile,u.realname,u.logo
                FROM {$this->prefix}chatfricomment as cf
                LEFT JOIN {$this->prefix}user as u
                ON cf.user_id = u.id
                WHERE  {$where} lIMIT 0,50 ";

           $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

           $nicknameall = '';
           foreach($list as $k=>$v){
               $logo = $v['logo'];
               $list[$k]['logo'] = Buddha_Atom_String::getUserLogo($logo);
               $list[$k]['nickname'] =$UserObj->getUserNickName($v);

           }




           $jsondata = array();
           $jsondata['user_id'] = $user_id;
           $jsondata['usertoken'] = $usertoken;
           $jsondata['list'] = $list;

           Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '删除评论');
       }


    public function chatfrimyalum(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','friend_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $ChatfrialbumObj = new Chatfrialbum();


        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $friend_id =  Buddha_Http_Input::getParameter('friend_id');
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):6;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        $where = " cc.buddhastatus=0 AND cc.user_id='{$friend_id}'  ";

        $sql = "select count(*) as total
                from {$this->prefix}chatfricircle as cc
                where {$where}";

        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];

        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $orderby = "  ORDER BY cc.createtime DESC ";


        $sql = "SELECT cc.id as chatfricircle_id,cc.user_id as friend_id,cc.createtime,cc.createtimestr,cc.content,
                       cc.year,cc.month,cc.day,cc.addr,cc.type,cc.link,
                       u.nickname,u.mobile,u.realname,u.logo
                FROM {$this->prefix}chatfricircle as cc
                LEFT JOIN {$this->prefix}user as u
                ON cc.user_id = u.id

                WHERE  {$where} {$orderby} {$limit} ";


        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach($list as $k=>$v){
            $YmdDateStr = date('Y-m-d',$v['createtime']);
            $list[$k]['timedivision'] = Buddha_Atom_String::getTimeDivision($YmdDateStr);
            $list[$k]['is_showtimedivision'] = 1;


        }

        $timedivision=0;
        foreach($list as $k=>$v){

            if($timedivision!==$v['timedivision']){
                $list[$k]['is_showtimedivision'] = 1;
                $timedivision = $v['timedivision'];
            }else{
                $list[$k]['is_showtimedivision'] = 0;
            }
            $logo = $v['logo'];
            $list[$k]['logo'] = Buddha_Atom_String::getUserLogo($logo);
            $list[$k]['nickname'] =$UserObj->getUserNickName($v);
            $chatfricircle_id = $v['chatfricircle_id'];
            $albumlist = $ChatfrialbumObj->getFiledValues(array('id as chatfrialbum_id','image','type'),"buddhastatus=0 AND chatfricircle_id='{$chatfricircle_id}' limit 0,4 ");
            foreach($albumlist as $k1=>$v1){
                $image =  $v1['image'];
                $albumlist[$k1]['image'] = Buddha_Atom_String::getApiFileUrlStr($image);
            }
            $list[$k]['chatfrialbum'] = $albumlist;
            $albumcount = count($albumlist);
            $list[$k]['chatfrialbumcount'] = $albumcount;
            $albumword='';
            if($albumcount>=3){
                $albumword="共{$albumcount}张" ;
            }
            $list[$k]['chatfrialbumword'] =$albumword;


        }




        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;
        $jsondata['list'] = $list;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '朋友圈自己的相册');
    }


    public function chatfrimyalumdetail(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','chatfricircle_id','friend_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $ChatfrialbumObj = new Chatfrialbum();
        $ChatfripraiseObj = new Chatfripraise();
        $ChatfricommentObj = new Chatfricomment();


        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $friend_id =  Buddha_Http_Input::getParameter('friend_id');
        $chatfricircle_id =  Buddha_Http_Input::getParameter('chatfricircle_id');

        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):6;
        $pagesize=1;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!isset($_REQUEST['page'])){

            $count = $ChatfrialbumObj->countRecords("chatfricircle_id='{$chatfricircle_id}' ");
            if($count==0){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '无相册');
            }
            $Db_Chatfrialbum = $ChatfrialbumObj->getSingleFiledValues(array('id'),"chatfricircle_id='{$chatfricircle_id}' ORDER BY ID ASC ");


            $page = $ChatfrialbumObj->countRecords("id<='{$Db_Chatfrialbum['id']}' ");

        }





        $where = " cfa.buddhastatus=0 AND cfa.user_id='{$friend_id}'  ";

        $sql = "select count(*) as total
                from {$this->prefix}chatfrialbum as cfa
                where {$where}";

        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];

        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $orderby = "  ORDER BY cfa.id ASC ";


        $sql = "SELECT cfa.id as chatfrialbum_id,cfa.user_id as friend_id,cfa.createtime,cfa.createtimestr,cfa.image,cfa.type,
                       cfa.chatfricircle_id,cf.content,
                       u.nickname,u.mobile,u.realname,u.logo
                FROM {$this->prefix}chatfrialbum as cfa
                LEFT JOIN {$this->prefix}user as u
                ON cfa.user_id = u.id
                LEFT JOIN {$this->prefix}chatfricircle as cf
                ON cfa.chatfricircle_id = cf.id

                WHERE  {$where} {$orderby} {$limit} ";


        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);









            $image= Buddha_Atom_String::getApiFileUrlStr($list[0]['image']);
            $logo = Buddha_Atom_String::getUserLogo($list[0]['logo']);
            $nickname = $UserObj->getUserNickName($list[0]);
            $title1= Buddha_Atom_String::getGreetTimeDivision($list[0]['createtime']);
            $title2 = $ChatfrialbumObj->getTitle2($list[0]['chatfricircle_id'],$list[0]['chatfrialbum_id']);
            $countFraise = $ChatfripraiseObj->countFraise($list[0]['chatfricircle_id'],$list[0]['user_id']);
            $countCommnent = $ChatfricommentObj->countCommnent($list[0]['chatfricircle_id'],$list[0]['user_id']);





        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;

        $jsondata['image'] = $image;
        $jsondata['logo'] = $logo;
        $jsondata['nickname'] = $nickname;
        $jsondata['title1'] = $title1;
        $jsondata['title2'] = $title2;
        $jsondata['countFraise'] = $countFraise;
        $jsondata['countCommnent'] = $countCommnent;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '相册详情');
    }

    public function chatfrimyalumdel(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','chatfrialbum_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $ChatfrialbumObj = new Chatfrialbum();

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $chatfrialbum_id =  Buddha_Http_Input::getParameter('chatfrialbum_id');




        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$ChatfrialbumObj->isOwnAlbum($user_id,$chatfrialbum_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '相册不是本人的相册');
        }

        $ChatfrialbumObj->deleteAlbum($user_id,$chatfrialbum_id);

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '相册删除');
    }


    public function circlemydetail(){



        if (Buddha_Http_Input::checkParameter(array('usertoken','chatfricircle_id','friend_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $ChatfricircleObj = new Chatfricircle();
        $ChatfrialbumObj = new Chatfrialbum();
        $ChatfripraiseObj = new Chatfripraise();
        $ChatfriendObj = new Chatfriend();
        $Chatfricomment = new Chatfricomment();

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $chatfricircle_id =  Buddha_Http_Input::getParameter('chatfricircle_id');
        $friend_id =  Buddha_Http_Input::getParameter('friend_id');
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):6;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];




        $where = " cc.buddhastatus=0 AND cc.user_id='{$friend_id}' AND cc.id='{$chatfricircle_id}'  ";



        $sql = "SELECT cc.id as chatfricircle_id,user_id as friend_id,cc.content,cc.year,cc.month,cc.day,cc.addr,cc.createtime,cc.createtimestr,
                       cc.type,cc.link,
                       u.nickname,u.mobile,u.realname,u.logo
                FROM {$this->prefix}chatfricircle as cc
                LEFT JOIN {$this->prefix}user as u
                ON cc.user_id = u.id
                WHERE  {$where}  ";


        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach($list as $k=>$v){
            $chatfricircle_id = $v['chatfricircle_id'];
            $logo = $v['logo'];
            $list[$k]['logo'] = Buddha_Atom_String::getUserLogo($logo);
            $list[$k]['nickname'] = $UserObj->getUserNickName($v);
            $list[$k]['link'] = Buddha_Atom_String::getApiStr($v['link']);
            $list[$k]['addr'] = Buddha_Atom_String::getApiStr($v['addr']);
            $list[$k]['duration'] = Buddha_Atom_String::getDurationTimeStr($v['createtime']);
            $list[$k]['is_fraise'] =$ChatfripraiseObj->isHasValidRecord($chatfricircle_id,$user_id);


            $albumlist = $ChatfrialbumObj->getFiledValues(array('id as chatfrialbum_id','image','type'),"buddhastatus=0 AND chatfricircle_id='{$chatfricircle_id}' ");
            foreach($albumlist as $k1=>$v1){
                $image =  $v1['image'];
                $albumlist[$k1]['image'] = Buddha_Atom_String::getApiFileUrlStr($image);
            }
            $list[$k]['chatfrialbum'] = $albumlist;


            $where = " cf.buddhastatus=0 AND  cf.chatfricircle_id='{$chatfricircle_id}' AND cf.user_id='{$friend_id}' ";
            $sql = "SELECT cf.id as chatfripraise_id,user_id as friend_id,cf.createtime,cf.createtimestr,
                       u.nickname,u.mobile,u.realname,u.logo
                FROM {$this->prefix}chatfripraise as cf
                LEFT JOIN {$this->prefix}user as u
                ON cf.user_id = u.id
                WHERE  {$where}  lIMIT 0,100 ";

            $praiselist = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            $nicknameall = '';
            foreach($praiselist as $k2=>$v2){
                $logo = $v2['logo'];
                $praiselist[$k2]['logo'] = Buddha_Atom_String::getUserLogo($logo);
                $praiselist[$k2]['nickname'] =$UserObj->getUserNickName($v2);
                $nicknameall.=  $praiselist[$k2]['nickname'];
                $nicknameall.=',';
            }

            $nicknameall = Buddha_Atom_String::toDeleteTailCharacter($nicknameall,1);
            $list[$k]['nicknameall'] = $nicknameall;
            $list[$k]['praiselist'] = $praiselist;


            $where = " cf.buddhastatus=0 AND  cf.chatfricircle_id='{$chatfricircle_id}' AND cf.user_id='{$friend_id}' ";
            $sql = "SELECT cf.id as chatfricomment_id,user_id as friend_id,cf.createtime,cf.createtimestr,cf.content,
                       u.nickname,u.mobile,u.realname,u.logo
                FROM {$this->prefix}chatfricomment as cf
                LEFT JOIN {$this->prefix}user as u
                ON cf.user_id = u.id
                WHERE  {$where}  lIMIT 0,100 ";

            $commentlist = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            foreach($commentlist as $k3=>$v3){
                $logo = $v3['logo'];
                $commentlist[$k3]['logo'] = Buddha_Atom_String::getUserLogo($logo);
                $commentlist[$k3]['nickname'] =$UserObj->getUserNickName($v3);

            }
            $list[$k]['commentlist'] = $commentlist;

        }


        $jsondata = $list[0];
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '朋友圈');

    }


    public function circledel(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','chatfricircle_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $ChatfricircleObj = new Chatfricircle();

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $chatfricircle_id =  Buddha_Http_Input::getParameter('chatfricircle_id');




        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$ChatfricircleObj->isOwnCircle($user_id,$chatfricircle_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '相册不是本人的相册');
        }

        $ChatfricircleObj->deleteCircle($user_id,$chatfricircle_id);

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '相册详情删除');
    }


}