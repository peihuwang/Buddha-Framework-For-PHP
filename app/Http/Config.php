<?php
class Config extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    function getConfig(){
        $configs_arr = $this->db->getFiledValues('',$this->prefix.'config', "ckey like 'hsk_%' ");
        $config=array();
        foreach($configs_arr as $k=>$v){
            $config[$v['ckey']]=$v['cvalue'];

        }
        return $config;
    }

    public function updateConfig($configs,$cdesc){
        if (is_array ( $configs )) {
            foreach ( $configs as $key => $value ) {

                $this->db->countRecords($this->table, "ckey='{$key}'")
                    ?$this->db->updateRecords(array('cvalue'=>$value,
                    'cdesc'=>$cdesc[$key]

                ), $this->table, "ckey = '{$key}'") :
                    $this->db->addRecords(array('ckey'=>$key,'cvalue'=>$value,
                        'cdesc'=>$cdesc[$key]
                    ), $this->table);

            }
        }
    }

    public function cacheConfigs(){

        /*******↓↓↓↓↓↓↓↓********修改区*****↓↓↓↓↓↓↓↓**********/
        $content = '';
        $config_arr = $this->db->getFiledValues('',$this->table, "ckey like 'hsk_%' ");
        foreach($config_arr as $k=>$v){
            // $content .= '$'.$v['ckey']." = '".addcslashes($v['cvalue'], '\'\\')."';//{$v['cdesc']}  \r\n";
            $content .= '$cache[\'config\'][\''.$v['ckey']."'] = '".addcslashes($v['cvalue'], '\'\\')."';//{$v['cdesc']}\r\n";
        }
        /*********↑↑↑↑↑↑↑↑*****修改区*****↑↑↑↑↑↑↑*************/
        // $hsk_smsAccount = 'huaju1061';//短信发送账号
        // $cache['configs']['hsk_smsAccount'] = 'huaju1061';//短信发送账号

        Buddha_Tool_File::writeCache('config', $content);
       // $hsk_smsAccount = 'huaju1061';//短信发送账号
       // $cache['configs']['hsk_smsAccount'] = 'huaju1061';//短信发送账号

        Buddha_Tool_File::writeCache('config', $content);
    }
}