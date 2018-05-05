<?php



class Companypay extends  Buddha_App_Model{ //微信企业 支付到个人
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }
    /**
     *  array转xml
     */
    public function arrayToXml($arr){
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<".$key.">".$val."</".$key.">";
            } else
                $xml .= "<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml .= "</xml>";
        return $xml;
    }
    //使用证书，以post方式提交xml到对应的接口url

    /**
     *   作用：使用证书，以post方式提交xml到对应的接口url
     */
    function curl_post_ssl($url, $vars, $second=30){
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

        //以下两种方式需选择一种
        /******* 此处必须为文件服务器根目录绝对路径 不可使用变量代替*********/
        curl_setopt($ch,CURLOPT_SSLCERT,PATH_ROOT."topay/wxpay/cert/apiclient_cert.pem");
        curl_setopt($ch,CURLOPT_SSLKEY,PATH_ROOT."topay/wxpay/cert/apiclient_key.pem");

        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);

        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }

    //企业向个人付款
    public function payToUser($openid,$desc,$amount){
        //微信付款到个人的接口
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

        /*$params["mch_appid"]        = 'wxfc875d9388d83b78';   //公众账号appid
        $params["mchid"]            = '1413294902';   //商户号 微信支付平台账号
        $params["nonce_str"]        = 'bendishangjia'.mt_rand(100000,999999);   //随机字符串
        $params["partner_trade_no"] = mt_rand(10000000,99999999);         //商户订单号
        $params["amount"]           = $amount;          //金额
        $params["desc"]             = $desc;            //企业付款描述
        $params["openid"]           = $openid;          //用户openid
        $params["check_name"]       = 'NO_CHECK';       //不检验用户姓名
        $params['spbill_create_ip'] = $_SERVER["REMOTE_ADDR"];   //获取IP

                //生成签名
        $str = 'amount='.$params["amount"].'&check_name='.$params["check_name"].'&desc='.$params["desc"].'&mch_appid='.$params["mch_appid"].'&mchid='.$params["mchid"].'&nonce_str='.$params["nonce_str"].'&openid='.$params["openid"].'&partner_trade_no='.$params["partner_trade_no"].'&spbill_create_ip='.$params['spbill_create_ip'].'&key=nohackernohackernohacker12345888';
        //md5加密 转换成大写

        $sign = strtoupper(md5($str));*/

        $sign = '';
        $wx_payment_data['wxpay_mchid_key'] = 'nohackernohackernohacker12345888';
        $rnd_no = time().rand(10000,99999);  
        $trade_no =time().rand(10000,99999);
        $ip = $_SERVER["REMOTE_ADDR"];
        $mchid = '1413294902';
        $mch_appid = 'wxfc875d9388d83b78';
        $user_name = 'aa';
        $tmpArr = array(
            'mch_appid' => $mch_appid,
            'mchid' => $mchid,
            'nonce_str' => $rnd_no,
            'partner_trade_no' => $trade_no,
            'openid' => $openid,
            'check_name' => 'NO_CHECK',
            're_user_name' => $user_name,
            'amount' => $amount,
            'desc' => $desc,
            'spbill_create_ip' => $ip);
        ksort($tmpArr);
        $tmpStr = '';
        foreach($tmpArr as $k=>$v){
            $tmpStr .= '&'.$k.'='.$v;
        }
        $tmpStr = substr($tmpStr,1);
        $tmpStr = $tmpStr.'&key='.$wx_payment_data['wxpay_mchid_key'];
        $tmpStr = md5($tmpStr);
        $sign = $tmpStr;




        $params["sign"] = $sign;//签名

        $xml = $this->arrayToXml($params);

    $xml_str = "<xml>
        <mch_appid>%s</mch_appid>
        <mchid>%s</mchid>
        <nonce_str>%s</nonce_str>
        <partner_trade_no>%s</partner_trade_no>
        <openid>%s</openid>
        <check_name>%s</check_name>
        <re_user_name>%s</re_user_name>
        <amount>%s</amount>
        <desc>%s</desc>
        <spbill_create_ip>%s</spbill_create_ip>
        <sign>%s</sign>
        </xml>";    
    $xml = sprintf($xml_str, $mch_appid, $mchid, $rnd_no, $trade_no, $openid, 'NO_CHECK', $user_name, $amount, $desc, $ip, $sign); 


        //print_r($xml);
        return $this->curl_post_ssl($url, $xml);
    }

    public function msectime() {
       list($msec, $sec) = explode(' ', microtime());
       $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
       return $msectime;
    }

}