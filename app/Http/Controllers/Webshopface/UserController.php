<?php


class UserController extends Buddha_App_Action
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

    /**
     * 代理商添加合伙人
     */
    public function agentaddpartner()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken','mobile','realname','agentrate','password',
              'level1','level2','level3',
            ))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $RegionObj = new Region();

        $usertoken=Buddha_Http_Input::getParameter('usertoken');
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $realname=Buddha_Http_Input::getParameter('realname');
        $agentrate=Buddha_Http_Input::getParameter('agentrate');
        $password=Buddha_Http_Input::getParameter('password');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');
        if($password=="" || !$password){
            $password=substr($mobile,5);
        }
        $groupid=3;
        $source=2;


        if(!is_numeric($agentrate)){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],50000001,'分成比例不是数值类型');
        }

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $Db_User = $UserObj->getSingleFiledValues(array('id','usertoken')," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasAgentPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000020,'没有代理商用户权限，你还未申请代理商角色');
        }

        if($UserObj->isExistMobileFromUser($mobile)){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000009,'输入的手机号已经被占用');
        }

         if(!$RegionObj->isProvince($level1)) {
             Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4000001,'省的地区内码id不对');
         }


        if(!$RegionObj->isCity($level2)) {
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4000002,'市的地区内码id不对');
        }

        if(!$RegionObj->isArea($level3)) {
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4000003,'区的地区内码id不对');
        }

        $data=array();
        $data['referral_id']=$user_id;
        $data['username']=$mobile;
        $data['realname']=$realname;
        $data['groupid']=$groupid;
        $data['to_group_id']= '';
        $data['mobile']=$mobile;
        $data['password']=Buddha_Tool_Password::md5($password);
        $data['codes']=$password;
        $data['state']=1;
        $data['onlineregtime']=Buddha::$buddha_array['buddha_timestamp'];
        $data['partnerrate']=$agentrate;
        $data['source']=$source;
        $data['level0']=1;
        $data['level1']=$level1;
        $data['level2']=$level2;
        $data['level3']=$level3;
        $user_id = $UserObj->add($data);
        $jsondata = array();
        $jsondata['add_user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['user_id'] = $user_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'代理商添加合伙人');


    }


    //用户中心
    public function center()
    {
        $host = Buddha::$buddha_array['host'];//https安全连接
        $UserObj = new User();
        $NewsObj = new News();
        if(Buddha_Http_Input::checkParameter(array('usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $groupid = (int)Buddha_Http_Input::getParameter('groupid');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','email',
            'level1','level2','level3','address',
            'username'
        );

        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $Db_groupid = $Db_User['groupid'];
        $Db_to_group_id = $Db_User['to_group_id'];
        $Db_newsnum = $NewsObj->countRecords("u_id='{$user_id}' and sure=1");

        $center_all_arr = $UserObj->getRankByGroupId($groupid,$Db_groupid,$Db_to_group_id,$Db_User);
        $head = array();
        $head['news_count'] = $Db_newsnum;
        $head['my_message'] = array('name'=>'我的消息','logo'=>$host.'apiuser/menuplus/wodexiaoxi.png','Services'=>"user.viewprofile");
        $jsondata = array();
        $jsondata['head'] = $head;
        $jsondata['body'] = $center_all_arr;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'会员中心');

    }


    /**
     *  个人中心信息更新
    */
    public function updateprofile()
    {
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        $UserbasicObj = new Userbasic();
        $RegionObj = new Region();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        if(Buddha_Http_Input::checkParameter(array('usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],211999,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo',
            'nickname','realname','mobile','email',
            'level1','level2','level3','address',
            'username'

        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $UserbasicObj->lazyaddUserbaisc($user_id);

        $savePath = PATH_ROOT.'storage/apiuser/';
        if(!file_exists($savePath)){
            mkdir($savePath, 0777);
        }
        $savePath = PATH_ROOT.'storage/apiuser/logo/';
        if(!file_exists($savePath)){
            @mkdir($savePath, 0777);
        }
        $savePath =PATH_ROOT.'storage/apiuser/idcard/';
        if(!file_exists($savePath)){
            @mkdir($savePath, 0777);
        }

        $jsondata= array();
        $jsondata['user_id'] =$Db_User['id'];
        $jsondata['usertoken']=$Db_User['usertoken'];


        /**** 更新保存服务热线 *****/
        $tel = Buddha_Http_Input::getParameter('tel');
        if(strlen($tel)){
            $UserObj->edit(array('tel'=>$tel),$user_id);
            $jsondata['tel']=$tel;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存服务热线');
        }

        /**** 更新保存服务热线 *****/
        $gender = Buddha_Http_Input::getParameter('gender');
        if(strlen($gender)){
            $gender = (int)$gender;
            $UserObj->edit(array('gender'=>$gender),$user_id);
            $jsondata['gender']=$gender;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'更新性别');
        }

        /**** 更新详细地址 *****/
        $address = Buddha_Http_Input::getParameter('address');
        if(strlen($address)){
            $UserObj->edit(array('address'=>$address),$user_id);
            $jsondata['address']=$address;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存详细地址');
        }


        /**** 更新头像 *****/
        $logo=Buddha_Http_Input::getParameter('logo');

        if(strlen($logo)){
            $savePath ='storage/apiuser/logo/';

            if(!Buddha_Atom_File::checkBase64Img($logo)){
                $Image= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . $savePath,
                    array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('logo')
                    ->getOneReturnArray();
                $filePath =PATH_ROOT.$Image;



                // $logo = Buddha_Atom_File::imgToBase64($filePath);

                $image_info = getimagesize($filePath);
                $image_data = fread(fopen($filePath, 'r'), filesize($filePath));
                $logo = 'data:' . $image_info['mime'] . ';base64,' . base64_encode($image_data);

                unlink($filePath);
            }


            $imgurl= explode(',',$logo);
            $base64_string = $imgurl[1];


            if(!Buddha_Atom_File::checkStringIsBase64($base64_string))
            {
                Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],211951,
                    '头像不是base64格式');
            }

            if(!Buddha_Atom_File::checkBase64Img($logo))
            {
                Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],211952,
                    '头像不是base64格式图片');
            }


            $output_file = $user_id.'_u_'.date('Ymdhis',time()). '.jpg';

            $filePath =PATH_ROOT.$savePath.$output_file;


            Buddha_Atom_File::base64contentToImg($filePath,$base64_string);
            Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
            Buddha_Tool_File::thumbImage( $filePath, 320, 320, 'S_' );
            $logo = $savePath.'S_'.$output_file;
            $source = $filePath;
            @unlink($source);
            $UserObj->deleteFileOfImage($user_id,array('logo'));
            $UserObj->updateRecords(array('logo'=>$logo),"id='{$user_id}' ");


            $jsondata['logo']=$host.$logo;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存头像成功');

        }
        /**** 更新昵称 *****/
        $nickname=Buddha_Http_Input::getParameter('nickname');
        if(strlen($nickname)){
            $UserObj->edit(array('nickname'=>$nickname),$user_id);
            $jsondata['nickname']=$nickname;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存昵称');
        }

        /**** 更新姓名 *****/
        $realname=Buddha_Http_Input::getParameter('realname');
        if(strlen($realname)){

            if($UserbasicObj->checkIdNumber($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],211955,
                    '身份证审核通过不能再更改姓名');
            }

            $UserObj->edit(array('realname'=>$realname),$user_id);
            $jsondata['realname']=$realname;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存姓名');
        }


        /**** 更新公司名 *****/
        $company=Buddha_Http_Input::getParameter('company');
        if(strlen($company)){
            $UserbasicObj->updateRecords(array('company'=>$company),$user_id);
            $jsondata['company']=$company;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存公司名');
        }


        /**** 更新职位 *****/
        $jobposition=Buddha_Http_Input::getParameter('jobposition');
        if(strlen($jobposition)){
            $UserbasicObj->updateRecords(array('jobposition'=>$jobposition),$user_id);
            $jsondata['jobposition']=$jobposition;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存职位');
        }

        /**** 更新个人主页 *****/
        $website=strtolower(Buddha_Http_Input::getParameter('website'));
        if(strlen($website)){
            $UserbasicObj->updateRecords(array('website'=>$website),$user_id);

            if(strpos($website, 'http') !== false){
                $jsondata['website']=$website;
            }else{
                $jsondata['website']='http://'.$Db_User['logo'];
            }

            $jsondata['website']=$website;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存个人主页');
        }

        /**** 更新用户名 *****/
        $realname=Buddha_Http_Input::getParameter('username');
        if(strlen($realname)){


/*            if($UserObj->existUserName($username)){
                Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000010,
                    '此用户名存在');
            }else{
                $UserObj->updateRecords(array('username'=>$username)," isdel=0 and id='{$user_id}' and username!='{$username}' ");
                $jsondata['username']=$username;
                Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存用户名');

            }*/

           // $UserObj->updateRecords(array('realname'=>$realname)," isdel=0 and id='{$user_id}' and username!='{$username}' ");
            $UserObj->updateRecords(array('realname'=>$realname)," isdel=0 and id='{$user_id}'  ");
            $jsondata['realname']=$realname;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存姓名');

        }

        /**** 更新身份证号 *****/
        $idnumber=Buddha_Http_Input::getParameter('idnumber');
        if(strlen($idnumber)){
            $UserbasicObj->updateRecords(array('idnumber'=>$idnumber),$user_id);
            $jsondata['idnumber']=$idnumber;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存身份证号');
        }


        /**** 更新身份证正面 *****/
        $idcardfaceimg=Buddha_Http_Input::getParameter('idcardfaceimg');

        if(strlen($idcardfaceimg))
        {
            $imgurl= explode(',',$idcardfaceimg);
            $base64_string = $imgurl[1];

            if(!Buddha_Atom_File::checkStringIsBase64($base64_string)){
                Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000011,
                    '头像不是base64格式');
            }

            if(!Buddha_Atom_File::checkBase64Img($idcardfaceimg)){
                Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000012,
                    '头像不是base64格式图片');
            }

            $output_file = $user_id.'_face_'.date('Ymdhis',time()). '.jpg';
            $savePath ='storage/user/idcard/';
            $filePath =PATH_ROOT.$savePath.$output_file;
            Buddha_Atom_File::base64contentToImg($filePath,$base64_string);
            Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
            Buddha_Tool_File::thumbImage( $filePath, 320, 320, 'S_' );
            $idcardfaceimg = $savePath.'S_'.$output_file;
            $source = $filePath;
            @unlink($source);
            $UserbasicObj->deleteFileOfImage($user_id,array('idcardfaceimg'));
            $UserbasicObj->updateUserbasic(array('idcardfaceimg'=>$idcardfaceimg),$user_id);

            $jsondata['idcardfaceimg']=$host.$idcardfaceimg;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存身份证正面');
        }

        /**** 更新身份证反面 *****/
        $idcardbackimg=Buddha_Http_Input::getParameter('idcardbackimg');

        if(strlen($idcardbackimg))
        {
            $imgurl= explode(',',$idcardbackimg);
            $base64_string = $imgurl[1];

            if(!Buddha_Atom_File::checkStringIsBase64($base64_string)){
                Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000011,
                    '头像不是base64格式');
            }

            if(!Buddha_Atom_File::checkBase64Img($idcardbackimg)){
                Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000012,
                    '头像不是base64格式图片');
            }

            $output_file = $user_id.'_back_'.date('Ymdhis',time()). '.jpg';
            $savePath ='storage/user/idcard/';
            $filePath =PATH_ROOT.$savePath.$output_file;
            Buddha_Atom_File::base64contentToImg($filePath,$base64_string);
            Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
            Buddha_Tool_File::thumbImage( $filePath, 320, 320, 'S_' );
            $idcardbackimg = $savePath.'S_'.$output_file;
            $source = $filePath;
            @unlink($source);
            $UserbasicObj->deleteFileOfImage($user_id,array('idcardbackimg'));
            $UserbasicObj->updateUserbasic(array('idcardbackimg'=>$idcardbackimg),$user_id);


            $jsondata['idcardbackimg']=$host.$idcardbackimg;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'保存身份证反面');
        }

        /***更新所在地***/
        $regionid=Buddha_Http_Input::getParameter('regionid');
        if(strlen($regionid) and $regionid>0)
        {
            $area=$RegionObj->getAllArrayAddressByLever($regionid);

            $areastr = '';
            $savearea='';
            $row = array();
            $row['level0']=1;
            foreach($area as $k=>$v){
                if($k>0){
                    $areastr.=$v['name'].'>';
                    $str ="level".$k;
                    $row[$str]=$v['id'];
                }
            }

            if($areastr!='')
                $savearea=Buddha_Atom_String::toDeleteTailCharacter($areastr);

            $UserObj->updateRecords($row," isdel=0 and id='{$user_id}'  ");

            $jsondata['area']=$savearea;
            $jsondata['level0']=$row['level0'];
            $jsondata['level1']=$row['level1'];;
            $jsondata['level2']=$row['level2'];;
            $jsondata['level3']=$row['level3'];;
            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'区域更新');

        }

    }


    /**
     * 会员绑定
     */
    public function thirdpartbind()
    {
        $UseroauthObj = new Useroauth();
        $VerifyObj = new Verify();
        $UserObj = new User();
        $mobile = Buddha_Http_Input::getParameter('mobile');
        $mobilecode = Buddha_Http_Input::getParameter('mobilecode');

        $oauth_id = Buddha_Http_Input::getParameter('oauth_id');
        $oauth_name = Buddha_Http_Input::getParameter('oauth_name');
        $oauth_access_token = Buddha_Http_Input::getParameter('oauth_access_token');
        $oauth_unionid = Buddha_Http_Input::getParameter('oauth_unionid');


        if (Buddha_Http_Input::checkParameter(array('oauth_id', 'oauth_name', 'oauth_access_token'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        /*        $thirdpass = $UseroauthObj->checkThirdPart($oauth_id,$oauth_name,$oauth_access_token);
                if($thirdpass==0){
                    $jsondata = array();
                    if($oauth_name=='wechat'){
                        $third_name ="微信官网";
                        $third_url = "https://api.weixin.qq.com/sns/auth?access_token={$oauth_access_token}&openid={$oauth_id}";
                        $jsondata['thrird_name']=$third_name;
                        $jsondata['thrird_checkurl']=$third_url;

                    }


                    Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],4444005,'未通过检验授权凭证');
                }*/


        $num = $UseroauthObj->countRecords(" isdel=0 and oauth_id='{$oauth_id}' and oauth_name='{$oauth_name}'  ");

        $data = array();
        if ($num == 0) {
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $data['ip'] = Buddha_Explorer_Network::getIp();
            $data['oauth_expires'] = Buddha::$buddha_array['buddha_timestamp'] + 86400;

            $data['oauth_id'] = $oauth_id;
            $data['oauth_name'] = $oauth_name;
            $data['oauth_access_token'] = $oauth_access_token;
            $data['oauth_unionid'] = $oauth_unionid;
            $third_id = $UseroauthObj->add($data);
            $third_user_id = 0;
            $third_is_bind = 0;

            //微信判断unionid 在系统有没有关联 如果有 此信息进行相同的管理 并且设置$third_is_bind=1
            $third_is_bind = $UseroauthObj->checkIsBindByUnionid($oauth_id, $oauth_name, $oauth_access_token);

            $third_operator = 'add';

        } else {
            $Db_Useroauth = $UseroauthObj->getSingleFiledValues(array('id', 'is_bind', 'user_id'), " isdel=0 and  oauth_id='{$oauth_id}' and oauth_name='{$oauth_name}'  ");
            $third_id = $Db_Useroauth['id'];
            $third_user_id = $Db_Useroauth['user_id'];
            $third_operator = 'update';
            $data['oauth_access_token'] = $oauth_access_token;
            $third_is_bind = $Db_Useroauth['is_bind'];
            $data['ip'] = Buddha_Explorer_Network::getIp();
            $data['oauth_expires'] = Buddha::$buddha_array['buddha_timestamp'] + 86400;
            $UseroauthObj->edit($data, $third_id);
        }

        if (strlen($mobile) == 11 and strlen($mobilecode) > 3 and $third_is_bind == 0) {
            $jsondata = array();
            $jsondata['third_id'] = $third_id;
            $jsondata['third_operator'] = $third_operator;
            //  $jsondata['third_user_id'] =$third_user_id;
            //   $jsondata['third_is_bind'] =$third_is_bind;

            $jsondata['oauth_id'] = $oauth_id;
            $jsondata['oauth_name'] = $oauth_name;
            $jsondata['oauth_access_token'] = $oauth_access_token;

            if (!$VerifyObj->hasMobileCode($mobile, $mobilecode)) {
                Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 20000006, '输入的手机验证码不正确');
            }

            //进行会员绑定
            //判断有无会员 没有 则进行 注册  有则进行查找 并且进行关联 再实现登录 返回usertoken
            $num = $UserObj->countRecords("isdel=0 and mobile='{$mobile}' ");
            $data = array();
            if ($num == 0) {
                $data['mobile'] = $mobile;
                $data['mobile_ide'] = 1;
                $data['groupid'] = 4;
                $password = $mobilecode . $mobilecode;
                $data['password'] = Buddha_Tool_Password::md5($password);
                $data['codes'] = $password;
                $data['state'] = 1;
                $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                $user_id = $UserObj->add($data);
                $usertoken = $UserObj->birthUserToken($user_id);
                $UserObj->edit(array('usertoken' => $usertoken), $user_id);

            } else {

                $Db_User = $UserObj->getSingleFiledValues(array('id', 'usertoken'), "isdel=0 and mobile='{$mobile}' ");
                $user_id = $Db_User['id'];
                $usertoken = $Db_User['usertoken'];
                if (strlen($usertoken) < 1) {
                    $usertoken = $UserObj->birthUserToken($user_id);
                    $UserObj->edit(array('usertoken' => $usertoken), $user_id);
                }
            }
            $data = array();
            $data['is_bind'] = 1;
            $data['user_id'] = $user_id;
            $third_is_bind = 1;
            $third_user_id = $user_id;
            $data['bindtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['bindtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $UseroauthObj->edit($data, $third_id);
            $VerifyObj->hadPass($mobile, $mobilecode);
        }


        $jsondata = array();
        if ($third_is_bind == 1) {
            $jsondata['third_user_id'] = $third_user_id;
            $jsondata['third_is_bind'] = $third_is_bind;

            $Db_User = $UserObj->getSingleFiledValues(" isdel=0 and id='{$third_user_id}' ");
            $usertoken = $Db_User['usertoken'];
            if (strlen($usertoken) < 1 and isset($Db_User['id'])) {
                $usertoken = $UserObj->birthUserToken($third_user_id);
                $UserObj->edit(array('usertoken' => $usertoken), $third_user_id);
            }
            $jsondata['usertoken'] = $usertoken;
        } else {
            $jsondata['third_is_bind'] = 0;
            $jsondata['usertoken'] = 0;

        }
        $jsondata['third_id'] = $third_id;
        $jsondata['third_operator'] = $third_operator;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '会员绑定');
    }

    /**
     * 密码重设
     */
    public function resetPassword()
    {
        $UserObj = new User();
        $VerifyObj = new Verify();
        $mobile = Buddha_Http_Input::getParameter('mobile');
        $mobilecode = Buddha_Http_Input::getParameter('mobilecode');
        $password = Buddha_Http_Input::getParameter('password');
        $repassword = Buddha_Http_Input::getParameter('repassword');

        if (Buddha_Http_Input::checkParameter(array('mobile', 'mobilecode', 'password', 'repassword'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }
        if ($password == '' or $repassword == '') {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000003, '用户注册信息密码为空');
        }
        if (strlen($password) < 6) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000004, '用户注册信息密码小于4位');
        }
        if ($password != $repassword) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000005, '两次输入的密码不一致');
        }

        if (strlen($mobile) < 11) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000006, '输入的手机号不正确');
        }
        if (strlen($mobilecode) < 4) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000007, '输入的手机验证码位数不正确');
        }

        if (!$VerifyObj->hasMobileCode($mobile, $mobilecode)) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000008, '输入的手机验证码不正确');
        }

        $VerifyObj->hadPass($mobile, $mobilecode);
        $data = array();
        $data['password'] = Buddha_Tool_Password::md5($password);
        $data['codes'] = $password;
        $UserObj->updateRecords($data, "isdel=0 and mobile='{$mobile}' and mobile_ide=1  ");
        $jsondata = array();

        $Db_User = $UserObj->getSingleFiledValues(array('id', 'usertoken'), "isdel=0 and mobile='{$mobile}' and mobile_ide=1  ");
        $jsondata['user_id'] = $Db_User['id'];
        $jsondata['usertoken'] = $Db_User['usertoken'];
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '密码重设');

    }

    /**
     * 会员登录-用密码登录 \ 手机验证码登录
     */
    public function login()
    {
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        $VerifyObj = new Verify();
        $mobile = Buddha_Http_Input::getParameter('mobile');
        $mobilecode = Buddha_Http_Input::getParameter('mobilecode');
        $password = Buddha_Http_Input::getParameter('password');
        $Db_User = array();
        if ((strlen($password) + strlen($mobilecode)) < 1) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        if (Buddha_Http_Input::checkParameter(array('mobile'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $user_num = $UserObj->countRecords("isdel=0 and (mobile='{$mobile}' or username='{$mobile}') ");
        if ($user_num == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000001, '该手机号的会员不存在');
        }


        $fields = array('id', 'usertoken', 'mobile', 'realname', 'logo','groupid');
        if (strlen($password)) {
            $encode_password = Buddha_Tool_Password::md5($password);


            $Db_User = $UserObj->getSingleFiledValues($fields, "isdel=0 and (mobile='{$mobile}' or username='{$mobile}') and password='{$encode_password}' ");


            $logo = $Db_User['logo'];


            $user_num = $UserObj->countRecords("isdel=0 and (mobile='{$mobile}' or username='{$mobile}') and password='{$encode_password}' ");
            if ($user_num == 0) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000002, '账号或密码错误');
            }

            if (strlen($logo) < 2) {
                $jsondata['logo'] = "";
            } else {
                $jsondata['logo'] = $host . $logo;
            }


            $usertoken = $UserObj->birthUserToken($Db_User['id']);
            $UserObj->edit(array('usertoken' => $usertoken), $Db_User['id']);


            $user_id = $Db_User['id'];
            $chattoken = $UserObj->setUserWorldChatTokenIfNull($user_id);
            $jsondata['user_id'] = $Db_User['id'];
            $jsondata['chattoken'] = $chattoken;


            $jsondata['usertoken'] = $usertoken;
            $jsondata['mobile']=$Db_User['mobile'];
            $jsondata['realname']=$Db_User['realname'];
            $jsondata['groupid']=$Db_User['groupid'];

            Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '会员登录-用密码登录');
        }


        if(strlen($mobilecode)){
            if(strlen($mobilecode)<4){
                Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000007,'输入的手机验证码位数不正确');
            }

            if(!$VerifyObj->hasMobileCode($mobile,$mobilecode)){
                Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000008,'输入的手机验证码不正确');
            }
            $Db_User = $UserObj->getSingleFiledValues($fields,"isdel=0 and mobile='{$mobile}'");

            $usertoken = $UserObj->birthUserToken($Db_User['id']);
            $UserObj->edit(array('usertoken'=>$usertoken),$Db_User['id']);
            $jsondata= array();
            if(strlen($Db_User['logo'])<=1){
                $jsondata['logo']=$host.'storage/usrelogo/'.rand(1,7).'.jpg';
            }else{
                $jsondata['logo']=$host.$Db_User['logo'];
            }


            $user_id = $Db_User['id'];
            $chattoken = $UserObj->setUserWorldChatTokenIfNull($user_id);
            $jsondata['user_id'] = $Db_User['id'];
            $jsondata['chattoken'] = $chattoken;


            $jsondata['usertoken']=$usertoken;
            $jsondata['mobile']=$Db_User['mobile'];
            $jsondata['realname']=$Db_User['realname'];
            $jsondata['groupid']=$Db_User['groupid'];

            Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'会员登录-手机验证码登录');

        }

    }

    /**
     * 用户注册
     */
    public function register()
    {
        $UserObj = new User();
        $VerifyObj = new Verify();
        $SupershopconfObj = new Supershopconf();
        $UserassoObj = new Userasso();

        if (Buddha_Http_Input::checkParameter(array('mobile', 'mobilecode', 'password', 'repassword', 'webface_access_token','appKey','appSecret'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webshopface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }

        $mobile = Buddha_Http_Input::getParameter('mobile');
        $mobilecode = Buddha_Http_Input::getParameter('mobilecode');
        $password = Buddha_Http_Input::getParameter('password');
        $repassword = Buddha_Http_Input::getParameter('repassword');
        $groupid = Buddha_Http_Input::getParameter('usertype');

        $appKey = Buddha_Http_Input::getParameter('appKey');
        $appSecret = Buddha_Http_Input::getParameter('appSecret');
        $shop_id = $SupershopconfObj->getShopIdByAppKeyAndAppSecretInt($appKey,$appSecret);
        $father_id = $SupershopconfObj->getUserIdByAppKeyAndAppSecretInt($appKey,$appSecret);

        $mobile_arr = array(
            '15928133400',
            '13547873249',
            '15757306123',
            '11111111111'
        );
        $mobiles = implode(',',$mobile_arr);
        if(stripos($mobiles,$mobile)<0){
            if ($password == '' or $repassword == '') {
                Buddha_Http_Output::makeWebfaceJson(null, '/webshopface/?Services=' . $_REQUEST['Services'], 20000003, '用户注册信息密码为空');
            }
            if (strlen($password) < 6) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webshopface/?Services=' . $_REQUEST['Services'], 20000004, '用户注册信息密码小于4位');
            }
            if ($password != $repassword and strlen($repassword) > 0) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webshopface/?Services=' . $_REQUEST['Services'], 20000005, '两次输入的密码不一致');
            }

            if (strlen($mobile) < 11) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webshopface/?Services=' . $_REQUEST['Services'], 20000006, '输入的手机号不正确');
            }
            if (strlen($mobilecode) < 4) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webshopface/?Services=' . $_REQUEST['Services'], 20000007, '输入的手机验证码位数不正确');
            }
            if (!$VerifyObj->hasMobileCode($mobile, $mobilecode)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webshopface/?Services=' . $_REQUEST['Services'], 20000008, '输入的手机验证码不正确');
            }
        }

        if ($UserObj->hasMobile($mobile)) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webshopface/?Services=' . $_REQUEST['Services'], 20000009, '输入的手机号已经被占用');
        }


        $data = array();
        $data['username'] = $mobile;
        $data['mobile'] = $mobile;
        $data['mobile_ide'] = 1;
        $data['password'] = Buddha_Tool_Password::md5($password);
        $data['codes'] = $password;

        $data['father_id'] = $father_id;

        $data['groupid'] = $groupid;
        if ($groupid == 1) {
            $data['to_group_id'] = '4' . ',' . $groupid;
        }
        $data['state'] = 1;
        $data['onlineregtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $user_id = $UserObj->add($data);

        $UserassoObj->addOrUpdateUserAsso($user_id,$father_id);

        $usertoken = $UserObj->birthUserToken($user_id);
        $UserObj->edit(array('usertoken' => $usertoken), $user_id);

        $VerifyObj->hadPass($mobile, $mobilecode);

        $jsondata = array();

        $chattoken = $UserObj->setUserWorldChatTokenIfNull($user_id);
        $jsondata['user_id'] = $user_id;
        $jsondata['chattoken'] = $chattoken;


        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webshopface/?Services=' . $_REQUEST['Services'], 0, '用户注册');
    }


    /**
     * 用户退出
     */
    public function logout()
    {
        $UserObj = new User();

        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }

        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $Db_User = $UserObj->getSingleFiledValues(array('id', 'usertoken'), " isdel=0 and usertoken='{$usertoken}' ");
        $jsondata = array();
        $jsondata['user_id'] = $Db_User['id'];
        $jsondata['usertoken'] = $Db_User['usertoken'];

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '用户退出');

    }

    /**
     * 注销用户，测试接口使用，完了屏蔽
     */
    public function cancellation()
    {
        $UserObj = new User();
        $VerifyObj = new Verify();
        if (Buddha_Http_Input::checkParameter(array( 'mobile','mobilecode'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $mobile = Buddha_Http_Input::getParameter('mobile');
        $mobilecode = (int)Buddha_Http_Input::getParameter('mobilecode');
        if (strlen($mobile) < 11) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000006, '输入的手机号不正确');
        }
        /*if (strlen($mobilecode) < 4) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000007, '输入的手机验证码位数不正确');
        }*/

        /*if (!$VerifyObj->hasMobileCode($mobile, $mobilecode)) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000008, '输入的手机验证码不正确');
        }*/
        $mobile_arr = array(
            '15928133400',
            '13547873249',
            '15757306123',
            '11111111111',
            '15882306413'
        );
        $mobiles = implode(',',$mobile_arr);
        //$Db_User = $UserObj->getSingleFiledValues('',"mobile in {$mobile}");
        //$id = $Db_User['id'];
        $this->db->delRecords ( 'user', "mobile in ({$mobiles})" );
        $jsondata = array();
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '用户注销');

    }


    /**
     * 会员信息
     */
    public function viewprofile(){
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        $RegionObj = new Region();

        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        if(Buddha_Http_Input::checkParameter(array('usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $Db_userInfo = $UserObj->getUserInformation($usertoken);

        $jsondata = array();
        $jsondata = $Db_userInfo[0];
        if(Buddha_Atom_String::isValidString($Db_userInfo[0]['logo'])){
            if(strpos($Db_userInfo[0]['logo'], 'http') !== false){
                $jsondata['logo']=$Db_userInfo[0]['logo'];
            }else{
                $jsondata['logo']=$host.$Db_userInfo[0]['logo'];
            }
        }else{
            $jsondata['logo'] =  $host."style/images/userlogo/".rand(1,5).".png";
        }
        if(!Buddha_Atom_String::isValidString($Db_userInfo[0]['realname'])){
            $jsondata['realname'] = '';
        }
        if(!Buddha_Atom_String::isValidString($Db_userInfo[0]['mobile'])){
            $jsondata['mobile'] = '';
        }
        if(!Buddha_Atom_String::isValidString($Db_userInfo[0]['address'])){
            $jsondata['address'] = '';
        }
        if($jsondata['level3']!=0) {
            $area = $RegionObj->getAllArrayAddressByLever($Db_userInfo[0]['level3']);
            $areastr = '';
            foreach ($area as $k => $v) {
                if($k>0)
                    $areastr .= $v['name'] . '>';
            }
            $areastr = Buddha_Atom_String::toDeleteTailCharacter($areastr);
            $jsondata['area']=$areastr;
        }else{
            $jsondata['area']='';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'会员信息');


    }


    /**
     *  会员管理： 合伙人会员管理
     */

    public function partnerviewmembermore(){

        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj=new Common();
        $UserObj=new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasPartnerPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商角色！');
        }

        $api_keyword = Buddha_Http_Input::getParameter('$api_keyword')?Buddha_Http_Input::getParameter('$api_keyword'):'';


        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);


        $where=" (isdel=0 or isdel=4) and referral_id='{$user_id}' ";


        if(Buddha_Atom_String::isValidString($api_keyword)){

            $where.= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword,array('mobile','realname'));

        }

        $fileds = ' id AS user_id, mobile, onlineregtime, state, realname,logo AS img ';

        $orderby = " ORDER BY onlineregtime DESC ";

        $sql =" SELECT  {$fileds}  
                FROM {$this->prefix}user   
                WHERE {$where} 
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $Db_User= $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata['list'] =array();
        $Db_User['page'] = '';
        $Db_User['pagesize'] = '';
        $Db_User['totalrecord'] = '';
        $Db_User['totalpage'] ='';

        if(Buddha_Atom_Array::isValidArray($Db_User)){

            foreach($Db_User as $k=>$v ){
                if(Buddha_Atom_String::isValidString($v['img'])){

                    $Db_User[$k]['api_img'] = $host. $v['img'];

                }else{

                    $img=$UserObj->DefaultUserLogo();
                    $Db_User[$k]['api_img'] = $host. $img;

                }

                if($v['state']==0){

                    $Db_User[$k]['api_state'] = '未激活';

                }elseif($v['state']==1){

                    $Db_User[$k]['api_state'] = '已激活';

                }elseif($v['state']==4){

                    $Db_User[$k]['api_state'] = '已注销';

                }

                $Db_User[$k]['api_onlineregtime']=$CommonObj->getDateStrOfTime($v['onlineregtime']);
                unset( $Db_User[$k]['img']);
                unset( $Db_User[$k]['onlineregtime']);
                unset( $Db_User[$k]['state']);
            }

            $tablewhere=$this->prefix.'user';

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $Db_User['page'] = $temp_Common['page'];
            $Db_User['pagesize'] = $temp_Common['pagesize'];
            $Db_User['totalrecord'] = $temp_Common['totalrecord'];
            $Db_User['totalpage'] = $temp_Common['totalpage'];

            $jsondata['list'] = $Db_User;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'合伙人会员管理');

    }



    /**
     *  会员管理： 代理商会员管理列表
     */

    public function agentviewmembermore(){

        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','api_number'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj=new Common();
        $UserObj=new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');


        $RegionObj=new Region();

        $api_number = (int)Buddha_Http_Input::getParameter('api_number')?Buddha_Http_Input::getParameter('api_number'):0;
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }
        $locdata=$RegionObj->getApiLocationByNumberArr($api_number);


        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasAgentPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商角色！');
        }

        $api_keyword = Buddha_Http_Input::getParameter('$api_keyword')?Buddha_Http_Input::getParameter('$api_keyword'):'';

        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $where = " isdel=0 AND (groupid='1' OR groupid='4') AND level3='{$Db_User['level3']}' ";

        if(Buddha_Atom_String::isValidString($api_keyword)){

            $where.= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword,array('mobile','realname'));

        }



        $orderby = " ORDER BY onlineregtime DESC ";

        $sql =" SELECT  id AS user_id, mobile, state, groupid 
                FROM {$this->prefix}user   
                WHERE {$where} 
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $Db_User= $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata['list'] =array();
        $Db_User['page'] = '';
        $Db_User['pagesize'] = '';
        $Db_User['totalrecord'] = '';
        $Db_User['totalpage'] ='';

        if(Buddha_Atom_Array::isValidArray($Db_User)){

            foreach($Db_User as $k=>$v ){

                if($v['groupid']==1){

                    $Db_User[$k]['api_group'] = '商家';

                }elseif($v['groupid']==3){

                    $Db_User[$k]['api_group'] = '合伙人';

                }elseif($v['groupid']==4){

                    $Db_User[$k]['api_group'] = '普通会员';

                }

                $Db_User[$k]['api_onlineregtime']=$CommonObj->getDateStrOfTime($v['onlineregtime']);
                unset( $Db_User[$k]['img']);
                unset( $Db_User[$k]['onlineregtime']);
                unset( $Db_User[$k]['state']);
            }

            $tablewhere=$this->prefix.'user';

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $Db_User['page'] = $temp_Common['page'];
            $Db_User['pagesize'] = $temp_Common['pagesize'];
            $Db_User['totalrecord'] = $temp_Common['totalrecord'];
            $Db_User['totalpage'] = $temp_Common['totalpage'];

            $jsondata['list'] = $Db_User;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'代理商会员管理');

    }

    /**
     *  会员管理： 代理商会员管理详情
     */

    public function agentviewmemberview()
    {

        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','user_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj=new Common();
        $UserObj=new User();
        $RegionObj=new Region();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $userid = Buddha_Http_Input::getParameter('user_id')?Buddha_Http_Input::getParameter('user_id'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');


        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasAgentPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商角色！');
        }

        $api_keyword = Buddha_Http_Input::getParameter('$api_keyword')?Buddha_Http_Input::getParameter('$api_keyword'):'';

        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $where = " isdel=0 AND id='{$userid}' ";

        if(Buddha_Atom_String::isValidString($api_keyword)){
            $where.= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword,array('mobile','realname'));
        }


        $orderby = " ORDER BY onlineregtime DESC ";

        $sql =" SELECT  id AS user_id, mobile, onlineregtime, state, realname,logo AS img, level1, level2, level3, address ,gender 
                FROM {$this->prefix}user   
                WHERE {$where} 
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $Db_User_arr= $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata['list'] =array();

        if(Buddha_Atom_Array::isValidArray($Db_User_arr)){

            $Db_User=$Db_User_arr[0];
            if(Buddha_Atom_String::isValidString($Db_User['img'])){

                $Db_User['api_img'] = $host. $Db_User['img'];

            }else{

                $img=$UserObj->DefaultUserLogo();
                $Db_User['api_img'] = $host. $img;

            }

            if($Db_User['gender']==0){

                $Db_User['api_gender'] = '保密';

            }elseif($Db_User['gender']==1){

                $Db_User['api_gender'] = '男';

            }elseif($Db_User['gender']==2){

                $Db_User['api_gender'] = '女';

            }


            if($Db_User['state']==0){

                $Db_User['api_state'] = '未激活';

            }elseif($Db_User['state']==1){

                $Db_User['api_state'] = '已激活';

            }elseif($Db_User['state']==4){

                $Db_User['api_state'] = '已注销';

            }

            $Db_User['api_onlineregtime']=$CommonObj->getDateStrOfTime($Db_User['onlineregtime']);
            $Db_User['api_adddress']=$RegionObj->getAllDetailOfUserAdrressByRegionIdStr ($Db_User);

            unset( $Db_User['img']);
            unset( $Db_User['onlineregtime']);
            unset( $Db_User['state']);
            unset( $Db_User['address']);
            unset( $Db_User['level1']);
            unset( $Db_User['level2']);
            unset( $Db_User['level3']);
            unset( $Db_User['gender']);

            $jsondata['list'] = $Db_User;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'代理商会员详情');
    }



}