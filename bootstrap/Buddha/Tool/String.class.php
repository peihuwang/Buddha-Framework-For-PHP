<?php

class Buddha_Tool_String{
    protected static $_instance;

    /**
     * @param $options
     * @return Buddha_Tool_String
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

    /**
     * @param $str
     * @return mixed
     */
   public static  function delhtml($str){   //清除HTML标签
        $st=-1; //开始
        $et=-1; //结束
        $stmp=array();
        $stmp[]="&nbsp;";
        $len=strlen($str);
        for($i=0;$i<$len;$i++){
            $ss=substr($str,$i,1);
            if(ord($ss)==60){ //ord("<")==60
                $st=$i;
            }
            if(ord($ss)==62){ //ord(">")==62
                $et=$i;
                if($st!=-1){
                    $stmp[]=substr($str,$st,$et-$st+1);
                }
            }
        }
        $str=str_replace($stmp,"",$str);
        return $str;
    }



    /**
     * @param string $data 待转换的字符串
     * @return string
     */
    public static  function characet($data){
        if( !empty($data) ){
            $fileType = mb_detect_encoding($data , array('UTF-8','GBK','LATIN1','BIG5')) ;
            if( $fileType != 'UTF-8'){
                $data = mb_convert_encoding($data ,'utf-8' , $fileType);
            }
        }
        return $data;
    }
    /**
     * 字符串半角和全角间相互转换
     * @param string $str 待转换的字符串
     * @param string  $type TODBC:转换为全角；TOSBC:转换为半角
     * @return string 返回转换后的字符串
     */
    public static function convertStrType($str, $type='TOSBC')
    {
        $dbc = array(
            '０' , '１' , '２' , '３' , '４' ,
            '５' , '６' , '７' , '８' , '９' ,
            'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,
            'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' ,
            'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,
            'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' ,
            'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' ,
            'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' ,
            'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' ,
            'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' ,
            'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,
            'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' ,
            'ｙ' , 'ｚ' , '－' , '　' , '：' ,
            '．' , '，' , '／' , '％' , '＃' ,
            '！' , '＠' , '＆' , '（' , '）' ,
            '＜' , '＞' , '＂' , '＇' , '？' ,
            '［' , '］' , '｛' , '｝' , '＼' ,
            '｜' , '＋' , '＝' , '＿' , '＾' ,
            '￥' , '￣' , '｀'

        );
        $sbc = array( //半角
            '0', '1', '2', '3', '4',
            '5', '6', '7', '8', '9',
            'A', 'B', 'C', 'D', 'E',
            'F', 'G', 'H', 'I', 'J',
            'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y',
            'Z', 'a', 'b', 'c', 'd',
            'e', 'f', 'g', 'h', 'i',
            'j', 'k', 'l', 'm', 'n',
            'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x',
            'y', 'z', '-', ' ', ':',
            '.', ',', '/', '%', ' #',
            '!', '@', '&', '(', ')',
            '<', '>', '"', '\'','?',
            '[', ']', '{', '}', '\\',
            '|', '+', '=', '_', '^',
            '￥','~', '`'

        );

        if($type == 'TODBC'){
            return str_replace( $sbc, $dbc, $str ); //半角到全角
        }elseif($type == 'TOSBC'){
            return str_replace( $dbc, $sbc, $str ); //全角到半角
        }else{
            return $str;
        }
    }

    /**
     * @param int $len
     * @return string
     */
    public  static function getRand($len=4){
        $num="";
        for($i=0;$i<$len;$i++){
            $num .= rand(0,9);
        }
        return $num;

   }





}