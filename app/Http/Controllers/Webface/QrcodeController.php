<?php

/**
 * Class QrcodeController
 */
class QrcodeController extends Buddha_App_Action
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

//@creade_path  创建的多级目录
    public  function creade_path($path){
        //$path要创建的多级目录
        if (is_dir($path)){ //判断目录存在否，存在给出提示，不存在则创建目录
            $nus=2;//已存在
        }else{
            //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
            $res=mkdir( $path,0777,true);
            if ($res){
                $nus=1;//创建成功
            }else{
                $nus=0;//创建失败
            }
        }
        return $nus;
    }

    public function ourteam(){
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $UserassoObj = new Userasso();
        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $keyword =  Buddha_Http_Input::getParameter('keyword');
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):6;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','realname');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];




        $sql = $UserassoObj->getSqlFrontByLayerLimitNumberStr('',$user_id);
        $idarr = $UserassoObj->getFiledValues(array('user_id'),"1=1 {$sql}");
        $idSets = Buddha_Atom_Array::getIdInStr($idarr);

        $where = "isdel=0 AND id in({$idSets})  ";
        if(Buddha_Atom_String::isValidString($keyword)){
            $where .=" AND  ( username LIKE '%{$keyword}%' OR mobile LIKE '%{$keyword}%' ) ";
        }

        $sql = "select count(*) as total
                from {$this->prefix}user
                where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];

        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $sql = "SELECT id as user_id,logo,mobile,realname,nickname
                FROM {$this->prefix}user
                WHERE  {$where} {$limit}";


        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);




        foreach($list as $k=>$v){


            $logo = Buddha_Atom_String::getUserLogo($v['logo']);


            $list[$k]['nickname'] = $UserObj->getUserNickName($v);
            $list[$k]['logo'] = $logo;
        }

        $msg = '';
        if(strlen($keyword) and $pcount==0){
            $msg = '此会员不在您的团队中！';
        }

        if(strlen($keyword)==0 and $pcount==0){
            $msg = '你还没有团队，点击躺赚二维码，把自己的专属码分享出去组建自己的团队，开启您的躺赚之路!';
        }

        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;
        $jsondata['msg'] = $msg;
        $jsondata['list'] = $list;

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'我的团队');

    }

    public function sharecode(){
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $usertoken =  Buddha_Http_Input::getParameter('usertoken');


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','realname');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $UserObj->createQrcodeForShare($user_id,$Db_User['logo'],$Db_User['realname']);

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['shareimg'] = $host."storage/haibao/{$user_id}/qrcode_mould.jpg";

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'我的躺赚码');

    }

    public function friendcode(){

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','changestyle','reset'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $changestyle =  (int)Buddha_Http_Input::getParameter('changestyle');
        $reset =  (int)Buddha_Http_Input::getParameter('reset');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','realname');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $event = 'friendcode';
        $eventpage='add';
        $varid = $user_id;

        $qrcodefile = $event.'_'.$eventpage.'_'.$varid;
        $filename="storage/temp/{$event}/".$qrcodefile.'.png';
        $userlogo = $Db_User['logo'];


        $logolocation = PATH_ROOT .$userlogo;//准备好的logo图片

        if(!file_exists($logolocation) or !Buddha_Atom_String::isValidString($logo))
        {
            $userlogo = PATH_ROOT."resources/worldchat/portrait/default.png";
        }else{
            /**图片路径中多余的'/'***/
            $userlogo = PATH_ROOT.Buddha_Atom_Dir::getformatDbStorageDir($logo);
        }

       $logo = $userlogo;

        //从新生成二维码 再返二维码
        if($changestyle>0 or $reset>0){

            if(file_exists(PATH_ROOT.'/'.$filename))
            {
                unlink(PATH_ROOT.$filename);
            }
        }


        if(!file_exists(PATH_ROOT.'/'.$filename)){
            /**引入phpqrcode类库***/

            $arr  = array();
            $arr['param'] = array('friend_id'=>$user_id);
            $arr['type'] = 1;



            include PATH_ROOT . 'phpqrcode/phpqrcode.php';
            $value='https://u.bendishangjia.com/'.base64_encode(json_encode($arr));

            $errorCorrectionLevel = 'L';//容错级别
            $matrixPointSize = 13.8;//生成图片大小
            $temporary = PATH_ROOT .'storage/temporary';//临时文件位置
            $n = $this->creade_path($temporary);//创建文件夹

            if($n!=0)
            {
                $nlog=$temporary.'/nlog'.$qrcodefile.''.'.png';//原始二维码图的路径+名称(不带logo的)

                QRcode::png($value, $nlog, $errorCorrectionLevel, $matrixPointSize, 2);//生成二维码图片

                $QR = $nlog;//已经生成的原始二维码图(原始二维码图的路径+名称(不带logo的))
                if ($logo !== FALSE) {

                    $QR = imagecreatefromstring(file_get_contents($QR));

                    $logo = imagecreatefromstring(file_get_contents($logo));

                    $QR_width = imagesx($QR);//二维码图片宽度
                    $QR_height = imagesy($QR);//二维码图片高度
                    $logo_width = imagesx($logo);//logo图片宽度
                    $logo_height = imagesy($logo);//logo图片高度
                    $logo_qr_width = $QR_width / 5;
                    $scale = $logo_width/$logo_qr_width;
                    $logo_qr_height = $logo_height/$scale;
                    $from_width = ($QR_width - $logo_qr_width) / 2;
                    //重新组合图片并调整大小
                    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                        $logo_qr_height, $logo_width, $logo_height);
                }


                if($logo==FALSE)
                {
                    $oldfile =  $nlog;
                }else{
                    //输出图片
                    $ylog=$temporary.'/ylog'.$qrcodefile.''. '.png';//带Logo二维码的文件路径+名称

                    imagepng($QR, $ylog);//带Logo二维码的文件名

                    $oldfile= $ylog; //旧目录（即：带logo的二维码图片路径）
                }

                $newFile= PATH_ROOT ."storage/temp/{$event}"; //新目录

                $nus= $this->creade_path($newFile);
                if($nus!=0){
                    $filename="storage/temp/{$event}/".$qrcodefile.'.png';
                    $newFile_mv= PATH_ROOT.$filename; //新目录
                    rename($oldfile,$newFile_mv);
                    //  unlink($nlog);//删除原始二维码图（不带logo的二维码）
                  // echo $filename;

                    $savefile = PATH_ROOT.$filename;
                    //水印透明度
                    $alpha = 100;
                    //合并水印图片
                    $dst_im = imagecreatefromstring(file_get_contents(PATH_ROOT . "/style/images/friendcode_model.jpg"));

                    $qrcodeimg = $filename;
                    $src_im = imagecreatefromstring(file_get_contents(PATH_ROOT . $qrcodeimg));
                    $threelogo = imagecreatefromstring(file_get_contents($userlogo));




                    $chuli_src_im = imagecreatetruecolor(511, 511);
                    imagecopyresampled($chuli_src_im, $src_im, 0, 0, 0, 0, 511, 511, imagesx($src_im),imagesy($src_im));
                   // $chuli_src_im_three = imagecreatetruecolor(220, 220);
                    $chuli_src_im_three = imagecreate(220,220);//为设置颜色函数提供一个画布资源
                    //设定一些颜色
                    $white = imagecolorallocate($chuli_src_im_three,255,255,255);//返回由十进制整数设置为白色的标识符

                    imagecopyresampled($chuli_src_im_three, $threelogo, 0, 0, 0, 0, 220, 220, imagesx($threelogo),imagesy($threelogo));
                    imagecopymerge($dst_im,$chuli_src_im_three,22,10,0,0,220,220,100);
                    imagecopymerge($dst_im,$chuli_src_im,imagesx($dst_im)-590,imagesy($dst_im)-590,0,0,511,511,$alpha);
                    $ttfroot = PATH_ROOT . 'style/font/simsun.ttc';
                    //$font=imagecolorallocate($dst_im,41,163,238);
                    $font=imagecolorallocate($dst_im,0,0,0);
                    $str = $Db_User['realname'];
                    imagettftext($dst_im, 40, 0, 250, 80, $font, $ttfroot, $str);//使用自定义的字体
                    imagegif($dst_im, $savefile);
                    imagedestroy($dst_im);
                    imagedestroy($chuli_src_im);
                    imagedestroy($src_im);



                }
            }




        }




        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['codeimg'] = $host.$filename;

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'我的二维码');




    }



}