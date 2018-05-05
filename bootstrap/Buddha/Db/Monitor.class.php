<?php

class Buddha_Db_Monitor{
    protected $db;
    protected $smarty;
    protected $prefix;
    protected $classname;
    protected $c;
    protected static $_instance;

    /**
     * @param null $options
     * @return Buddha_Db_Monitor
     */
    public static function getInstance($options=NULL)
    {
        if (self::$_instance === null) {
            $createObj=  new self();
            if (is_array($options))
            {
                foreach ($options as $option => $value)
                {
                    $createObj->$option = $value;
                }
            }
            self::$_instance =$createObj;
        }
        return self::$_instance;
    }

    /**
     * Buddha_Db_Monitor constructor.
     */
    public function __construct(){
        $this->db = Buddha_Driver_Db::getInstance(
            Buddha::getDatabaseConfig()
        );
        $this->prefix = $this->db->getPrefix();
        $this->smarty = Smarty::getInstance(
            Buddha::getSmartyConfig()
        );
        $this->classname = __CLASS__;
    }
    public function userPrivilege(){
        if(Buddha_Http_Cookie::getCookie('uid')){
         $uid = Buddha_Http_Cookie::getCookie('uid');
          if($uid){
              $UserInfo = $this->db->getSingleFiledValues('','user',"id='{$uid}' AND isdel=0 ");
              $realname = $UserInfo['realname'];
              if($realname==''){
                  $realname = $UserInfo['username'];
              }
              $UserInfo['pyrealname'] =  Buddha_Convert_Chinesetoletter::encode($realname, 'all');
              unset($UserInfo['password']);
              unset($UserInfo['codes']);
              $this->smarty->assign('UserInfo',$UserInfo);
              return array("{$uid}"=>$UserInfo);
          }

        }elseif(Buddha_Http_Cookie::getCookie('unionid')){
          $unionid = Buddha_Http_Cookie::getCookie('unionid');
          if($unionid){
            $UserInfo =  $this->db->getSingleFiledValues('','wxuser',"unionid='{$unionid}'");
            //print_r($UserInfo);
            $this->smarty->assign('UserInfo',$UserInfo);
            return array("{$UserInfo['wx_id']}"=>$UserInfo);
          }
        }else{
            Buddha_Http_Head::redirectofmobile('请登录','../index.php?a=login&c=account');
        }

    }

    /**
     * @param $services
     */
    public  function memberPrivilege($services){

            if (Buddha_Http_Cookie::getCookie('buddha_adminsid')) {
            list($hsk_adminsid, $hsk_adminuser, $hsk_adminpw, $hsk_admintime) = explode("\t",  Buddha_Tool_Password::cookieDecode(Buddha_Http_Cookie::getCookie('buddha_adminsid') , Buddha::$buddha_array['cookie_hash']));
             $uid = $hsk_adminsid;
            if($uid==1)//总管理员有全部权限
                $owner_pri= 1;
            else{
                //查询数据库看是否有权限

                $owner_pri= $this->db->countRecords('member',"id='{$uid}' and pri like '%{$services},%'");

            }
            if($owner_pri==0){
                Buddha_Http_Head::redirect('没有权限非法操作','index.php?a=index&c=index');
            }
        }else{
               Buddha_Http_Head::redirect('请登录','index.php?a=login&c=index');
        }






    }

    /**
 * @param $services
 * @param string $other
 * @return mixed
 */
    public  function logWrite($services,$other='operateuse::operatedesc::operateolddesc')
    {

        if (Buddha_Http_Cookie::getCookie('buddha_adminsid')) {
            list($hsk_adminsid, $hsk_adminuser, $hsk_adminpw, $hsk_admintime) = explode("\t",  Buddha_Tool_Password::cookieDecode(Buddha_Http_Cookie::getCookie('buddha_adminsid') , Buddha::$buddha_array['cookie_hash']));
            $uid = $hsk_adminsid;

            if ($uid) {


                $other = explode('::', $other);

                $member = $this->db->getSingleFiledValues('', 'member', "id ={$uid}");
                $data = array();
                $data['uid'] = $member['id'];;
                $data['username'] = $member['username'];
                $data['logdate'] = time();
                $data['ip'] = Buddha_Explorer_Network::getIp();
                $data['services'] = $services;

                $data['operateuse'] = $other[0];
                $data['operatedesc'] = $other[1];
                $data['operateolddesc'] = $other[2];

                return $this->db->addRecords($data, 'log');

            }


        }
    }


}