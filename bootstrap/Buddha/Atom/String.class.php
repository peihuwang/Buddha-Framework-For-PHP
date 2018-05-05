<?php

/**
 * Class Buddha_Atom_String
 */
class Buddha_Atom_String{
    protected static $_instance;

    /**
     * @param $options
     * @return Buddha_Atom_String
     */
    public static function getInstance($options)
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
    public function __construct(){

    }

    public static function getGreetTimeDivision($timestamp){

    $h=date('G',$timestamp);
    $s = date(":i",$timestamp);
    $Ymd = date("Y年m月d日",$timestamp);
    if ($h<11) {
        return $Ymd.' '.'上午'.$h.$s;
    }
    elseif ($h<13) {

        return $Ymd.' '.'中午'.$h.$s;
    }
    elseif ($h<17) {
        $h = $h-12;
        return $Ymd.' '.'下午'.$h.$s;
    }
    else {
        $h = $h-12;
        return  $Ymd.' '.'晚上'.$h.$s;

    }
}
    /**
     * 返回像微信一样的时间间隔
     * @param $YmdDateStr
     * @return string
     * @author wph 2017-12-18
     */
    public static function getTimeDivision($YmdDateStr){

        if($YmdDateStr==date("Y-m-d")){
              return '今天';
        }

        if($YmdDateStr==date("Y-m-d",strtotime("-1 day"))){
            return '昨天';
        }

        $timestamp = strtotime($YmdDateStr);
        return date('d',$timestamp).'日'.date('m',$timestamp)."月";


    }


    /**
     * 返回文件路径
     * @param $str
     * @return string
     * @author wph 2017-12-15
     */
    public static function getApiFileUrlStr($str){

        $host = Buddha::$buddha_array['host'];

        if(Buddha_Atom_String::isValidString($str)){

            if(!Buddha_Atom_String::hasNeedleString($str,'http')){
                $str = $host.Buddha_Atom_Dir::getformatDbStorageDir($str);
            }


        }else{
            $str = '';
        }

        return $str;

    }
    /**
     * 返回时间间隔类似微信的几分 几小时 或者小天 几年前
     * @param $timestamp
     * @return string
     * @author wph 2017-12-15
     */
    public static function getDurationTimeStr($timestamp){
        $nowtime = Buddha::$buddha_array['buddha_timestamp'];
        $durationstr = '';
        if(!Buddha_Atom_String::isValidString($timestamp) OR strlen($timestamp)!=10){
            $timestamp = $nowtime+2;
        }

        $durationtime = $nowtime-$timestamp;

        if($durationtime>=0 AND $durationtime<60){
            $second = $durationtime;
            $durationstr="{$second}秒钟前";
        }
        elseif($durationtime>=60 AND $durationtime<3600){
            $minute= round($durationtime/60);
            $durationstr="{$minute}分钟前";
        }
        elseif($durationtime>=3600 AND $durationtime<86400){
            $hour= round($durationtime/3600);
            $durationstr="{$hour}小时前";
        }
        elseif($durationtime>=86400 AND $durationtime<2592000){
            $day= round($durationtime/86400);
            if($day==1){
                $durationstr="昨天";
            }else{
                $day= $day -1;
                $durationstr="{$day}天前";
            }

        }
        elseif($durationtime>=2592000 AND $durationtime<31526000){
            $month= round($durationtime/2592000);
            $durationstr="{$month}月前";
        }
        else{
            $year= round($durationtime/31526000);
            $durationstr="{$year}年前";
        }

          return  $durationstr;



    }

    /**
     * 返回接口正常的字符串
     * @param $str
     * @return string
     * @author wph 201-12-15
     */
    public static function getApiStr($str){

        if(Buddha_Atom_String::isValidString($str)){
            return $str;
        }else{
            return '';
        }

    }

    /**
     * 判断字符串是不是json
     * @param $str
     * @return bool
     */
    public static function isJson($str){

        if(is_array($str)){
            return 0;
        }

       $flag= is_null(json_decode($str));
       return !$flag;

    }

    /**
     * 返回用户的logo头像
     * @param $db_logo
     * @return mixed|string
     * @author wph 2017-12-12
     */
    public static function getUserLogo($db_logo,$setlogo=''){
        $logo = $db_logo;
        $host = Buddha::$buddha_array['host'];

        if(Buddha_Atom_String::isValidString($logo)){

            if(!Buddha_Atom_String::hasNeedleString($logo,'http')){

                $logo = $host.$logo;
            }else{
                $logo = Buddha_Atom_String::getAfterReplaceStr($logo,'http','https');
            }


        }else{
            if(Buddha_Atom_String::isValidString($setlogo)){
                $logo = $host.$setlogo;
            }else{
                $logo = $host."resources/worldchat/portrait/default.png";
            }

        }

        return $logo;
    }


    /**
     * 根据时间戳转变成相应的年
     * @param string $timestamp
     * @return bool|string
     * @author wph 2017-12-12
     */
    public static function getYearStr($timestamp=''){

        if(Buddha_Atom_String::isValidString($timestamp)){
            $nowtime = $timestamp;
        }else{
            $nowtime = Buddha::$buddha_array['buddha_timestamp'];
        }
        return date('Y',$nowtime);
    }

    /**
     * 根据时间戳转变成相应的月
     * @param string $timestamp
     * @return bool|string
     * @author wph 2017-12-12
     */
    public static function getMonthStr($timestamp=''){

        if(Buddha_Atom_String::isValidString($timestamp)){
            $nowtime = $timestamp;
        }else{
            $nowtime = Buddha::$buddha_array['buddha_timestamp'];
        }
        return date('m',$nowtime);
    }

    /**
     * 根据时间戳转变成相应的日
     * @param string $timestamp
     * @return bool|string
     * @author wph 2017-12-12
     */
    public static function getDayStr($timestamp=''){

        if(Buddha_Atom_String::isValidString($timestamp)){
            $nowtime = $timestamp;
        }else{
            $nowtime = Buddha::$buddha_array['buddha_timestamp'];
        }
        return date('d',$nowtime);
    }

    /**
     * 把源字符串中的某个字符串进行替换
     * @param $sourcestr
     * @param $substr
     * @param $replacestr
     * @return mixed
     * @author wph 2017-12-12
     */
    public static function getAfterReplaceStr($sourcestr,$substr,$replacestr){

        return  str_replace($substr, $replacestr,$sourcestr);

    }
    /**
     * @param $str
     * @return string
     */
    public static function getFirstCharter($str){
        if(empty($str)){return '';}
        $fchar=ord($str{0});
        if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
        $s1=iconv('UTF-8','gb2312',$str);
        $s2=iconv('gb2312','UTF-8',$s1);
        $s=$s2==$str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319&&$asc<=-20284) return 'A';
        if($asc>=-20283&&$asc<=-19776) return 'B';
        if($asc>=-19775&&$asc<=-19219) return 'C';
        if($asc>=-19218&&$asc<=-18711) return 'D';
        if($asc>=-18710&&$asc<=-18527) return 'E';
        if($asc>=-18526&&$asc<=-18240) return 'F';
        if($asc>=-18239&&$asc<=-17923) return 'G';
        if($asc>=-17922&&$asc<=-17418) return 'H';
        if($asc>=-17417&&$asc<=-16475) return 'J';
        if($asc>=-16474&&$asc<=-16213) return 'K';
        if($asc>=-16212&&$asc<=-15641) return 'L';
        if($asc>=-15640&&$asc<=-15166) return 'M';
        if($asc>=-15165&&$asc<=-14923) return 'N';
        if($asc>=-14922&&$asc<=-14915) return 'O';
        if($asc>=-14914&&$asc<=-14631) return 'P';
        if($asc>=-14630&&$asc<=-14150) return 'Q';
        if($asc>=-14149&&$asc<=-14091) return 'R';
        if($asc>=-14090&&$asc<=-13319) return 'S';
        if($asc>=-13318&&$asc<=-12839) return 'T';
        if($asc>=-12838&&$asc<=-12557) return 'W';
        if($asc>=-12556&&$asc<=-11848) return 'X';
        if($asc>=-11847&&$asc<=-11056) return 'Y';
        if($asc>=-11055&&$asc<=-10247) return 'Z';
        return 'ZZ';
    }

    /**
     * @param $str
     * @param int $deletelen
     * @return string
     * 删除尾巴字符
     */
    public static function  toDeleteTailCharacter($str,$deletelen=1)
    {
        $str = trim($str);
        if(strlen($str)>$deletelen)
        {
            $str = substr($str,0,strlen($str)-$deletelen);
        }

        return $str;
    }


    public static function getRandom($total)
    {
        $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = "";
        for($i=0;$i<$total;$i++)
        {
            $key .= $str{mt_rand(0,32)};    //生成php随机数
        }
        return $key;
    }

    /**
     * @param int $len
     * @return string
     */
    public  static function getRandNumber($len=4){
        $num="";
        for($i=0;$i<$len;$i++){
            $num .= rand(0,9);
        }
        return $num;

    }

    /**
     * @param $sourcestr
     * @param $needlestr
     * @return int
     */
    public  static function hasNeedleString($sourcestr,$needlestr){
        if(strlen($sourcestr) == 0 or strlen($needlestr) == 0){
            return 0;
        }
        if(strpos($sourcestr,$needlestr) !== false) {
          //  echo '包含';
            return 1;
        }else {
            return 0;
        }

    }

    /**
     * @param $str
     * @return int
     */
    public static function isValidString($str)
    {

        if(is_null($str) ){return 0;}
        if(strlen($str)==1 and $str==0){return 0;}
        if(strlen($str)==1 and $str==''){return 0;}
        if($str=='0'){return 0;}
        if($str==null){return 0;}
        if($str==NULL){return 0;}
        if(empty($str)){return 0;}
        return 1;

    }


    /**
     * 得到Api正确的返回字符串
     * @param $str
     * @return string
     */
    public static function getApiValidStr($str){

        if(Buddha_Atom_String::isValidString($str)){
              return $str;
        }else{
            return '';
        }
    }

    /**
     * 替换编辑器里的图片地址为http协议的图片地址
     * @param $content
     * @return mixed
     */
    public static function getApiContentFromReplaceEditorContent($content){
        $host = Buddha::$buddha_array['host'];
        $str = "/storage";

        $SiteConfig = Buddha::getSiteConfig();

        $str0 =  "http://{$SiteConfig['www']}/storage";
        $str1 = "/vendor";
        $str2 =  "http://{$SiteConfig['www']}/vendor";
        if(stripos($content,$str0)){
            $content = str_replace($str0,$host."storage",$content);
        }elseif(stripos($content,$str)){
            $content = str_replace($str,$host."storage",$content);
        }elseif(stripos($content,$str2)){
            $content = str_replace($str2,$host."vendor",$content);
        }elseif(stripos($content,$str1)){
            $content = str_replace($str1,$host."vendor",$content);
        }
        return $content;
    }

}