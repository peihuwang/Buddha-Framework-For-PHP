<?php
class Useroauth extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    public function  checkIsBindByUnionid($oauth_id,$oauth_name,$oauth_access_token){
        $third_is_bind=0;
        //返回关联信息 默认$third_is_bind=0
        if($oauth_name=='wechat' and $oauth_id and $oauth_access_token){
            $UserObj = new User();
            $checkurl = 'https://api.weixin.qq.com/sns/userinfo?access_token={$oauth_access_token}&openid={$oauth_id}&lang=zh_CN';
            $result = Buddha_Explorer_Curl::http_get($checkurl);
            if($result){
                $json = json_decode($result,true);
                if(!$json or !isset($json['unionid'])){
                    return 0;
                }else{
                    $unionid=$json['unionid'];
                    $nickname=$json['nickname'];
                    $sex=$json['sex'];
                    $headimgurl=$json['headimgurl'];

                    $UseroauthObj = new Useroauth();



                    $num = $UseroauthObj->countRecords(" oauth_unionid='{$unionid}' and  oauth_name='{$oauth_name}' and is_bind=1  ");
                    if($num){
                        $Db_Bind_Useroauth = $UseroauthObj->getSingleFiledValues(''," oauth_unionid='{$unionid}' and  oauth_name='{$oauth_name}' and is_bind=1  ");
                        $user_id = $Db_Bind_Useroauth['user_id'];
                        $pid = $Db_Bind_Useroauth['id'];
                        $data = array();
                        $data['is_bind']=1;
                        $data['user_id']=$user_id;
                        $data['unionid']=$unionid;
                        $data['pid']=$pid;
                        $data['bindtime']=Buddha::$buddha_array['buddha_timestamp'];
                        $data['bindtimestr']=Buddha::$buddha_array['buddha_timestr'];


                        $UseroauthObj->updateRecords($data," oauth_id='{$oauth_id}' and  oauth_name='{$oauth_name}' and is_bind=0 ");

                        //判断会员头像有无 没有进行更新
                        $UserObj->updateThirdPartUserInfo($nickname,$headimgurl,$sex,$user_id);

                        return 1;
                    }else{
                        $num = $UseroauthObj->countRecords(" oauth_id='{$oauth_id}'  and  oauth_name='{$oauth_name}' and (unionid=0 or unionid='')  ");
                        if($num)
                        $UseroauthObj->updateRecords(array('unionid'=>$unionid)," oauth_id='{$oauth_id}'  and  oauth_name='{$oauth_name}' and is_bind=0 ");

                        return 0;
                    }


                }


            }else{
                return 0;
            }
        }

        return $third_is_bind;

    }

    public function checkThirdPart($oauth_id,$oauth_name,$oauth_access_token){
        if($oauth_name=='wechat' and $oauth_id and $oauth_access_token){
            $checkurl = 'https://api.weixin.qq.com/sns/auth?access_token={$oauth_access_token}&openid={$oauth_id}';
            $result = Buddha_Explorer_Curl::http_get($checkurl);
            if($result){
                $json = json_decode($result,true);
                if (!$json || $json['errcode']!=0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return 0;
                }else{
                    return 1;
                }


            }else{
                return 0;
            }

        }

        return 1;

    }
}