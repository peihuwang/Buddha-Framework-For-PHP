<?php

class User extends Buddha_App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = strtolower(__CLASS__);

    }

    /**
     * @param $user_id
     * @return string
     * 获取店铺拥有者用户默认展示的号码
     */
    public function getUserDefaultphoneByUserid($shop_user_id)
    {
        $UserObj = new User();
        $Userdefaultphone = '';
        $filed = array('tel', 'mobile', 'isdefaultphone');

        $Db_User = $UserObj->getSingleFiledValues($filed, "id='{$shop_user_id}'");

        if ($Db_User['isdefaultphone'] == 1)//默认显示手机
        {
            if (Buddha_Atom_String::isValidString($Db_User['mobile'])) {
                $Userdefaultphone = $Db_User['mobile'];
            } else {
                $Userdefaultphone = $Db_User['tel'];
            }
        } else if ($Db_User['isdefaultphone'] == 2)//默认显示座机
        {
            if (Buddha_Atom_String::isValidString($Db_User['tel'])) {
                $Userdefaultphone = $Db_User['tel'];
            } else {
                $Userdefaultphone = '';
            }
        }
        return $Userdefaultphone;
    }


    /**
     * @param $uid
     * @param $logo
     * @param $name
     * 创建分享二维码
     */
    public function createQrcodeForShare($uid, $logo, $name)
    {
        if (!Buddha_Atom_String::isValidString($logo)) {
            $logo = 'style/images/index_sq1.jpg';
        }
        $savefile = PATH_ROOT . "storage/haibao/{$uid}/qrcode_mould.jpg";
        @mkdir(PATH_ROOT . "storage/haibao/{$uid}");
        @chmod(PATH_ROOT . "storage/haibao/{$uid}", 0755);
        if (!file_exists($savefile)) {
            //水印透明度
            $alpha = 100;
            //合并水印图片
            $dst_im = imagecreatefromstring(file_get_contents(PATH_ROOT . "/style/images/qrcode_mould.jpg"));
            $CommonObj = new Common();
            $qrcodeimg = $CommonObj->getQRCode('user', 'register', $uid);
            $src_im = imagecreatefromstring(file_get_contents(PATH_ROOT . $qrcodeimg));
            if (stripos($logo, "qlogo")) {
                $threelogo = imagecreatefromstring(file_get_contents($logo));
            } else {
                $threelogo = imagecreatefromstring(file_get_contents(PATH_ROOT . $logo));
            }
            $chuli_src_im = imagecreatetruecolor(550, 550);
            imagecopyresampled($chuli_src_im, $src_im, 0, 0, 0, 0, 550, 550, imagesx($src_im), imagesy($src_im));
            $chuli_src_im_three = imagecreatetruecolor(180, 180);
            imagecopyresampled($chuli_src_im_three, $threelogo, 0, 0, 0, 0, 180, 180, imagesx($threelogo), imagesy($threelogo));
            imagecopymerge($dst_im, $chuli_src_im_three, 180, 60, 0, 0, 180, 180, 100);
            imagecopymerge($dst_im, $chuli_src_im, imagesx($dst_im) - 750, imagesy($dst_im) - 1075, 0, 0, 550, 550, $alpha);
            $ttfroot = PATH_ROOT . 'style/font/simsun.ttc';
            //$font=imagecolorallocate($dst_im,41,163,238);
            $font = imagecolorallocate($dst_im, 0, 0, 0);
            $str = '"' . $name . '"';
            imagettftext($dst_im, 40, 0, 520, 130, $font, $ttfroot, $str);//使用自定义的字体
            imagejpeg($dst_im, $savefile);
            imagedestroy($dst_im);
            imagedestroy($chuli_src_im);
            imagedestroy($src_im);
            $haibaourl = PATH_ROOT . "storage/haibao/{$uid}/qrcode_mould.jpg";
        }
    }


    /**
     * 返回用户昵称
     * @param $userarr
     * @return string
     */
    public function getUserNickName($userarr)
    {

        $returnnickname = '';
        $nickname = $userarr['nickname'];
        $realname = $userarr['realname'];
        $mobile = $userarr['mobile'];

        if (Buddha_Atom_String::isValidString($nickname)) {
            $returnnickname = $nickname;
        }
        if (!Buddha_Atom_String::isValidString($returnnickname)) {
            $returnnickname = $realname;
        }

        if (!Buddha_Atom_String::isValidString($returnnickname)) {
            $returnnickname = $mobile;
        }

        return $returnnickname;


    }

    /**
     * 判断某个人是不是今天注册的会员
     * @param $user_id
     * @return int
     * @author wph 2017-11-07
     */
    public function isTodayMember($user_id)
    {
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $num = $this->countRecords("id='{$user_id}' AND onlineregtime>{$beginToday} ");

        if ($num) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 判断某个人能不能参与一分营销 如果返回1就可以参与,如果返回0就不能参与.
     * @param $user_id
     * @return int
     * @author wph 2017-12-07
     */
    public function isCouldHeartTicket($user_id)
    {

        if ($this->isTodayMember($user_id)) {

            $HeartplusObj = new Heartplus();
            $num = $HeartplusObj->countRecords("user_id='{$user_id}' ");
            if ($num == 0) {
                return 1;
            } else {
                return 0;
            }


        } else {


            return 0;
        }

    }


    /**
     *例如 http://www.veryceo.com/index.php?a=register&c=account&origin_id=6545
     */
    public function synxOrigin($origin_id)
    {

        if (Buddha_Atom_String::isValidString($origin_id) and $this->isValidUserId($origin_id)) {

            Buddha_Atom_Cookie::setCookieValueByKey('origin_id', $origin_id, 1);

        }


    }

    public function getOriginId()
    {

        return (int)Buddha_Atom_Cookie::getCookieValueByKey('origin_id');

    }


    /**
     * 获取用户的父亲Id
     * @param $user_id
     * @return int
     */
    public function getFatherId($user_id)
    {


        if ($user_id == 0) {

            return 0;
        }

        if ($this->isValidUserId($user_id)) {

            $Db_User = $this->getSingleFiledValues(array('father_id'), " id = '{$user_id}' AND isdel=0 ");
            $father_id = $Db_User['father_id'];

        } else {
            $father_id = 0;
        }
        return $father_id;
    }

    /**
     * 判断用户内码id是不是有效 1=有效 0=无效
     * @param $user_id
     * @return int
     */
    public function isValidUserId($user_id)
    {
        $num = $this->countRecords("id='{$user_id}' ");
        if ($num) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 判断提供的userIdArr是否正确
     * @param $userIdArr
     * @return int
     */
    public function isValidUserIdArr($userIdArr)
    {

        if (!Buddha_Atom_Array::isValidArray($userIdArr)) {

            return 0;
        }

        $num = count($userIdArr);
        /**
         * 查userIdArr在数据库里存在的记录数目
         */

        $ids = implode(',', $userIdArr);
        $db_num = $this->countRecords("id IN ($ids)");

        if ($db_num == $num) {

            return 1;
        } else {
            return 0;
        }

    }


    public function   setUserWorldChatTokenIfNull($friend_id)
    {

        $host = Buddha::$buddha_array['host'];
        $Db_User = $this->getSingleFiledValues(array('logo', 'chattoken', 'realname', 'mobile'), "id='{$friend_id}'");
        $chattoken = $Db_User['chattoken'];
        $realname = $Db_User['realname'];
        $mobile = $Db_User['mobile'];


        if (Buddha_Atom_String::isValidString($realname)) {
            $nickname = $realname;
        }


        if (!Buddha_Atom_String::isValidString($nickname)) {
            $nickname = $mobile;
        }


        $logo = $Db_User['logo'];
        if (Buddha_Atom_String::isValidString($logo)) {
            $logo = $host . $logo;
        } else {
            $logo = $host . "resources/worldchat/portrait/default.png";
        }

        if (!Buddha_Atom_String::isValidString($chattoken)) {
            $chattoken = Buddha_Thirdpart_Message::getInstance()->getToken($friend_id, $nickname, $logo);

        }

        return $chattoken;


    }


    /**如果有即时通讯token就返回token 没有则返回0
     * @param $user_id
     * @return int
     */
    public function getUserHasWorldChatTokenStr($user_id)
    {


        $chattoken_arr = $this->getSingleFiledValues(array('chattoken'), "id='{$user_id}' ");
        $chattoken = $chattoken_arr['chattoken'];
        if (Buddha_Atom_String::isValidString($chattoken)) {
            return $chattoken;
        } else {
            return 0;
        }

    }

    /**
     * 设置用户即时通讯的token
     * @param $user_id
     * @param $token
     */
    public function setUserWorldChatToken($user_id, $token)
    {

        if (Buddha_Atom_String::isValidString($token)) {
            $this->edit(array('chattoken' => $token), $user_id);
        }
    }


    /**
     * @param $mobile
     * @return string
     * 默认密码
     * $mark=0  表示用 123456  作为默认密码
     * $mark=1  表示用手机后六位  作为默认密码
     */

    public function defaultpassword($mark = 1, $mobile = '')
    {
        $mark = (int)$mark;
        if ($mark == 0) {
            $password = '123456';
        } elseif ($mark == 1) {
            $password = substr($mobile, -6);
        }
        return $password; //
    }


    /**
     *  判断用户名是否存在此手机号 如果存在返回1 如果不存在返回0
     * @param $mobile
     * @return int
     */
    public function isExistMobileFromUser($mobile)
    {
        $num = $this->countRecords("mobile='{$mobile}' ");
        if ($num) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 获取用户的信息
     * @param $usertoken
     */
    public function getUserInformation($usertoken)
    {
        $sql = "select id as user_id,groupid,logo,username,mobile,realname,level1,level2,level3,address,tel,gender from {$this->prefix}user  WHERE usertoken='{$usertoken}' AND state='1' AND isdel='0'";
        $Db_userInfo = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $Db_userInfo;
    }


    /**
     * @param   $Db_Shop
     * @param   $usertoken
     * @return  string
     * @author  csh
     */
    public function DefaultUserLogo()
    {

        /*用户默认头像*/
        $number = rand(1, 5);
        $img = "style/images/userlogo/{$number}.png";
        return $img;
    }


    public function del($id)
    {
        return $this->db->update($this->table, array(
            'isdel' => 1
        ), array(
            "id" => $id
        ));
    }

    public function usertype()
    {
        $usertype = array('1' => '商家会员', '2' => '代理商', '3' => '合伙人', '4' => '普通会员');
        return $usertype;
    }


    public function existnickname($param_username = '')
    {

        if ($param_username) {
            $username = $param_username;
        } else {
            $json = Buddha_Http_Input::getParameter('json');
            $json_arr = Buddha_Atom_Array::jsontoArray($json);
            $username = $json_arr['username'];
        }
        $UserObj = new User();
        $num = $UserObj->getSingleFiledValues(array('id'), "isdel=0 and username='{$username}'");
        if ($param_username) {
            if ($num == 0) {
                return 1;
            } else {
                return 0;
            }
        }
        $data = array();
        if ($num == 0) {
            $data['isok'] = 'true';
        } else {
            $data['isok'] = 'false';
        }
        Buddha_Http_Output::makeJson($data);
    }

    /**
     * 判断数据库里有没有此手机号码
     * @param $mobile
     * @return int
     */
    public function isHasMobile($mobile)
    {
        $num = $this->countRecords("isdel=0 and mobile='{$mobile}'");
        if ($num) {
            return 1;
        } else {
            return 0;
        }
    }


    public function existmobile($param_mobile = '')
    {
        if ($param_mobile) {
            $Mobile = $param_mobile;
        } else {
            $json = Buddha_Http_Input::getParameter('json');
            $json_arr = Buddha_Atom_Array::jsontoArray($json);
            $Mobile = $json_arr['mobile'];
        }

        $UserObj = new User();
        $num = $UserObj->countRecords("isdel=0 and mobile='{$Mobile}'");
        if ($param_mobile) {
            if ($num == 0) {
                return 1;
            } else {
                return 0;
            }
        }
        $data = array();
        if ($num == 0) {
            $data['isok'] = 'true';
        } else {
            $data['isok'] = 'false';
        }
        Buddha_Http_Output::makeJson($data);
    }

    /*      @ groupid_val  判断用户的身份
     */

    public function groupid_val()
    {
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if ($_SESSION['groupid'] == '') {
            $groupid = $UserInfo['groupid'];
        } else {
            $groupid = $_SESSION['groupid'];
        }
        return $groupid;
    }

    //根据用户本身角色而 判断控制器；
    public function user_role()
    {
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if ($UserInfo['groupid'] == 1) {
            $aa = 'business';
        } elseif ($UserInfo['groupid'] == 2) {
            $aa = 'agent';
        } elseif ($UserInfo['groupid'] == 3) {
            $aa = 'partner';
        } elseif ($UserInfo['groupid'] == 4) {
            $aa = 'user';
        }
        return $aa;
    }

    //判断用户是否登录
    //$msg未登录跳转提示信息
    function is_sign($msg = '请登录后再操作!')
    {
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if (empty($uid)) {
            Buddha_Http_Head::redirectofmobile($msg, 'index.php?a=login&c=account', 2);
            exit;
        }
    }


    public function existUserName($username)
    {

        $num = $this->countRecords("isdel=0 and username='{$username}' ");
        return $num;
    }

    public function deleteFileOfImage($user_id, $delpicrow)
    {

        $Db_UserImage = $this->getSingleFiledValues($delpicrow, " isdel=0 and id='{$user_id}' ");

        foreach ($Db_UserImage as $k => $v) {
            if ($v) {
                @unlink(PATH_ROOT . $v);
            }

        }
    }

    public function checkUserToken($usertoken, $service, $code, $msg)
    {
        if ($usertoken == '' or strlen($usertoken) == 0) {
            $num = 0;
        } else {
            $num = $this->countRecords(" isdel=0 and usertoken='{$usertoken}'");
        }
        if ($num == 0)
            Buddha_Http_Output::makeWebfaceJson(null, $service, $code, $msg);


    }

    public function hasMobile($mobile)
    {
        $usernum = $this->countRecords("isdel=0 and mobile='{$mobile}' ");
        return $usernum;
    }

    public function birthUserToken($str)
    {
        return md5(date('Y', time()) . ':' . $str);
    }

    /**
     * 更新第三方用户信息
     * @param $nickname
     * @param $logo
     * @param $gender
     * @param $user_id
     */
    public function updateThirdPartUserInfo($nickname, $logo, $gender, $user_id)
    {
        $nicknum = $this->countRecords("isdel=0 and (nickname='' or nickname=0) and id='{$user_id}' ");
        if ($nicknum) {
            $this->edit(array('nickname' => $nickname), $user_id);
        }

        $logonum = $this->countRecords("isdel=0 and (logo='' or logo=0) and id='{$user_id}' ");
        if ($logonum) {
            $this->edit(array('logo' => $logo), $user_id);
        }

        $gendernum = $this->countRecords("isdel=0 and (gender='' or gender=0) and id='{$user_id}' ");
        if ($gendernum) {
            $this->edit(array('gender' => $gender), $user_id);
        }

    }


    /**
     * 判断用户是否有普通会员的权限
     * @param $user_id
     * @return int
     */
    public function isHasUserPrivilege($user_id)
    {

        $Db_User = $this->getSingleFiledValues(array('id', 'groupid', 'to_group_id'), " id='{$user_id}' ");
        $validarr = $this->getValidRankArr($Db_User['groupid'], $Db_User['to_group_id']);
        if (Buddha_Atom_Array::isKeyExists('4', $validarr)) {
            return 1;
        } else {
            return 0;
        }

    }


    public function getValidRankArr($Db_groupid, $Db_to_group_id)
    {//获得有效的排名数组
        $return_arr = array();
        $all_group_id_str = $Db_groupid . ',' . $Db_to_group_id;
        $rankarr = explode(',', $all_group_id_str);
        $init_arr = array();
        foreach ($rankarr as $k => $v) {
            $v = (int)$v;
            if ($v and $this->isValidGroupId($v))
                $init_arr[$v] = $v;
        }
        if (Buddha_Atom_Array::isValidArray($init_arr)) {
            return $init_arr;

        } else {
            $return_arr[4] = 4;
            return $return_arr;
        }


    }

    /**
     * 判断用户是否有商家的权限
     * @param $user_id
     * @return int
     * @author wph 2017-09-13
     */
    public function isHasMerchantPrivilege($user_id)
    {

        $Db_User = $this->getSingleFiledValues(array('id', 'groupid', 'to_group_id'), " id='{$user_id}' ");
        $validarr = $this->getValidRankArr($Db_User['groupid'], $Db_User['to_group_id']);
        if (Buddha_Atom_Array::isKeyExists('1', $validarr)) {
            return 1;
        } else {
            return 0;
        }


    }

    /**
     * 判断用户是否有代理商的权限
     * @param $user_id
     * @return int
     * @author wph 2017-09-13
     */
    public function isHasAgentPrivilege($user_id)
    {
        $Db_User = $this->getSingleFiledValues(array('id', 'groupid', 'to_group_id'), " id='{$user_id}' ");
        $validarr = $this->getValidRankArr($Db_User['groupid'], $Db_User['to_group_id']);
        if (Buddha_Atom_Array::isKeyExists('2', $validarr)) {
            return 1;
        } else {
            return 0;
        }

    }

    /**
     * 判断用户是否有合伙人的权限
     * @param $user_id
     * @return int
     * @author wph 2017-09-13
     */
    public function isHasPartnerPrivilege($user_id)
    {

        $Db_User = $this->getSingleFiledValues(array('id', 'groupid', 'to_group_id'), " id='{$user_id}' ");
        $validarr = $this->getValidRankArr($Db_User['groupid'], $Db_User['to_group_id']);
        if (Buddha_Atom_Array::isKeyExists('3', $validarr)) {
            return 1;
        } else {
            return 0;
        }


    }

    /**
     * 判断groupid是否有效
     * @param $groupid
     * @return int
     * @author sys
     */
    public function isValidGroupId($groupid)
    {
        $validarr = array();
        $validarr[2] = 2;//代理商
        $validarr[1] = 1;//商家
        $validarr[3] = 3;//合伙人
        $validarr[4] = 4;//普通会员

        $groupid = (int)$groupid;
        if ($groupid) {
            if (Buddha_Atom_Array::isKeyExists($groupid, $validarr)) {
                return 1;
            } else {
                return 0;
            }

        } else {
            return 0;
        }
    }


}