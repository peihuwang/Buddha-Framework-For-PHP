<?php

/**
 * Class IndexController
 */
class IndexController extends Buddha_App_Action{

	public function __construct(){
		parent::__construct();
		$this->classname=__CLASS__;
		$this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
	}

	/**
	 * 获取当前页面完整URL地址
	 */
	public function hsk_wx_get_url() {
	    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
	    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
	}
	/**
	 * php转发post函数
	 */
	public function https_request($url){
		$curl = curl_init();  //初始化一个cURL会话
		//设置请求选项, 包括具体的url
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  //禁用后cURL将终止从服务端进行验证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl,CURLOPT_HEADER,0); //??
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); //??
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);		
		$response = curl_exec($curl);  
		curl_close($curl);
		
		$jsoninfo = json_decode($response,true); 
		return $jsoninfo;
	}

	public function index()
    {
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
            $CommonObj = new Common();
            $rearrs = $CommonObj->getWeChatUserInformation();
            if($rearrs['subscribe'] !== 1 && $rearrs){
                $judge = 1;
                $this->smarty->assign("judge",$judge);
            }
        }
		$uid = Buddha_Http_Cookie::getCookie('uid');
		if($uid){
			$UserObj = new User();
			$UserInfo = $UserObj->getSingleFiledValues('',"id={$uid}");
			if($UserInfo['groupid'] == 4){
				$url = '/user/index.php?a=add&c=demand';
			}elseif($UserInfo['groupid'] == 1){
				$url = '/business/index.php?a=add&c=demand';
			}
		}else{
			$url = '/index.php?a=login&c=account';
		}
		$is_mobile = Buddha_Terminal_Parse::isMobile();
		if(!$is_mobile){
			Buddha_Http_Head::jump('/pc/');
		}

		$RegionObj=new Region();
		$ShopObj=new Shop();
		$UserObj=new User();
		$WxuserObj = new Wxuser();
		$locdata = $RegionObj->getLocationDataFromCookie();
        $storetype=$ShopObj->getstoretypeindex();
        //代理商电话
        $referral=$UserObj->getSingleFiledValues(array('tel'),"isdel=0 and groupid='2' {$locdata['sql']}");



        $currentdate=time();//当前时间戳
        $SupplyObj = new Supply();
        $num =  $SupplyObj->countRecords (" is_promote=1 AND promote_end_date<{$currentdate}" );
        if($num){
            $Promotion = $SupplyObj->getFiledValues(''," is_promote=1 AND promote_end_date<{$currentdate}");
            foreach ($Promotion as $k => $v) {
                if($v['promote_end_date']<$currentdate){
                    $data['is_promote'] = 0;
                    $data['promote_price'] = 0.00;
                    $SupplyObj->updateRecords($data,"id='{$v['id']}'");
                }
            }
        }





        $this->smarty->assign('url',$url);

        $this->smarty->assign('referral',$referral);
        $this->smarty->assign('storetype',$storetype);
        $this->smarty->assign('locdata',$locdata);
		if(!$openid && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){//微信登录
			$WechatconfigObj = new Wechatconfig();
			$wechatcon = $WechatconfigObj->getSingleFiledValues(array('appsecret','appid','accesstoken'),"type='wechat' and son='token'");
			$appid = $wechatcon['appid'];
			$appsecret = $wechatcon['appsecret'];
			$openid = $_COOKIE['openid'];
		    if (!isset($_GET['code'])){
		        $backurl = $this->hsk_wx_get_url();
		        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($backurl)."&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
		        Header("Location: $url");
		    }else{
		        //获取code码，以获取openid
		        $code = $_GET['code'];
		        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
		        $re = file_get_contents($url);
		        $rearr = json_decode($re,true);
		        $unionid = $rearr['unionid'];
		        $counts = $WxuserObj->countRecords("unionid='{$unionid}'");
		        if(!$counts){
		        	$access_token = $rearr['access_token'];
		        	$openid = $rearr['openid'];
		        	/*// 判断是否已经关注公众号
					$subscribe_msg = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid;
					$subscribe = $this->https_request($subscribe_msg);
					$zyxx = $subscribe['subscribe'];
					if($zyxx !== 1){
						header("Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzIxMDY2ODM2NQ%3D%3D#wechat_redirect");
						exit;
					}*/
					//获取微信用户详细信息
		        	$userinfo_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN"; 
					$user_info = $this->https_request($userinfo_url);
					if(!$user_info['errcode']){
						$data['openid'] = $user_info['openid'];
						$data['nickname'] = $user_info['nickname'];
						$data['sex'] = $user_info['sex'];
						$data['province'] = $user_info['province'];
						$data['city'] = $user_info['city'];
						$data['head_pic'] = $user_info['headimgurl'];
						$data['unionid'] = $user_info['unionid'];
						$data['reg_time'] = time();
						$data['oauth'] = 'weixin';
						$re = $WxuserObj->add($data);//将数据写入表
					}
		        }
		        //print_r($data);
		        $setopenid = $rearr['openid'];
		        $unionid = $rearr['unionid'];
		        //$_COOKIE['unionid']=$unionid;
		        Buddha_Http_Cookie::setCookie('unionid', $unionid,1);
		        //print_r($_COOKIE);
		    }
		}

        ////////分享
        $WechatconfigObj  = new Wechatconfig();
        $sharearr = array(
            'share_title'=>'本地商家综合展示中心,供求信息发布中心',
            'share_desc'=>'商家/个人:免费发布各类产品,需求,招聘,租赁,促销，活动,简介,地址,导航,名片,传单等功能。',
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>'style/images/index_sq.png',
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        ////////分享

        $CommonObj = new Common();
        $indexnav = $CommonObj->IndexNav();

//        print_r($indexnav);
        $type_arr = array();

        foreach ($indexnav['type'] as $k=>$v)
        {
            foreach ($v as  $kk=>$vv)
            {
                $type_arr[] = $vv;
            }
        }


        foreach ($type_arr as $k=>$v)
        {
            if($v['is_show']==0)
            {
                unset($type_arr[$k]);
            }
        }


        $number = 5;

        $indexnav['type'] = array_chunk($type_arr,$number);

        $this->smarty->assign('indexnav',$indexnav);

		$TPL_URL = __FUNCTION__;
		$this->smarty->display($TPL_URL . '.html');
		
	}
}