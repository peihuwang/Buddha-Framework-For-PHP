<?php

function &getLang($key = '')
{
    if (_valid_key($key) == false) {
        return $key;
    }
    $vkey = $key ? strtokey("{$key}", '$GLOBALS[\'__HSKSIKE__\']') : '$GLOBALS[\'__HSKSIKE__\']';
    $tmp = eval('if(isset(' . $vkey . '))return ' . $vkey . ';else{ return $key; }');
    return $tmp;
}

/**
 * 验证key的有效性
 *
 * @author Hyber
 * @param string $key
 * @return bool
 */
function _valid_key($key)
{
    if (strpos($key, ' ') !== false) {
        return false;
    }
    return true;
}

/**
 *    将default.abc类的字符串转为$default['abc']
 *
 * @author    Garbin
 * @param     string $str
 * @return    string
 */
function strtokey($str, $owner = '')
{
    if (!$str) {
        return '';
    }
    if ($owner) {
        return $owner . '[\'' . str_replace('.', '\'][\'', $str) . '\']';
    } else {
        $parts = explode('.', $str);
        $owner = '$' . $parts[0];
        unset($parts[0]);
        return strtokey(implode('.', $parts), $owner);
    }
}

/* 比较函数，实现支付方式排序 */
function cmp_payment($a, $b)
{
    if ($b == 'alipay') {
        return 1;
    } elseif ($b == 'tenpay2' && $a != 'alipay') {
        return 1;
    } elseif ($b == 'tenpay' && $a != 'alipay' && $a != 'tenpay2') {
        return 1;
    } else {
        return -1;
    }
}


class Payment extends Buddha_App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = strtolower(__CLASS__);

    }


    public function getSetPayment()
    {

        $Db_Payment = $this->getFiledValues(array('payment_id', 'payment_code'), " 1=1 ");

        $payment = array();
        if (count($Db_Payment)) {
            foreach ($Db_Payment as $k => $v) {
                $payment[$v['payment_id']] = $v['payment_code'];
            }
        }


        return $payment;
    }


    /**
     * 获取内置支付方式
     *
     * @author    Garbin
     * @param     array $withe_list 白名单
     * @return    array
     */
    public function get_builtin($white_list = null)
    {
        static $payments = null;
        if ($payments === null) {
            $payment_dir = PATH_ROOT . 'vendor/payments';
            $dir = dir($payment_dir);
            $payments = array();
            while (false !== ($entry = $dir->read())) {
                /* 隐藏文件，当前目录，上一级，排除 */
                if ($entry{0} == '.') {
                    continue;
                }

                if (is_array($white_list) && !in_array($entry, $white_list)) {
                    continue;
                }

                /* 获取支付方式信息 */
                $payments [$entry] = $this->get_builtin_info($entry);
            }
        }


        if (is_array($payments)) {
            uksort($payments, "cmp_payment");
        }

        return $payments;
    }

    /**
     * 获取内置支付方式的配置信息
     *
     * @author    Garbin
     * @param     string $code
     * @return    array
     */
    public function  get_builtin_info($code)
    {
        $this->loadLang($this->lang_file('payment/' . $code));
        // include PATH_ROOT . '/data/payment/' . $code . '.lang.php';
        $payment_path = PATH_ROOT . 'vendor/payments/' . $code . '/payment.info.php';
        return include($payment_path);
    }

    /**
     * 获取支付方式白名单
     *
     * @author    Garbin
     * @return    array
     */
    public function get_white_list()
    {
        $file = PATH_ROOT . 'vendor/data/payments.inc.php';
        if (!is_file($file)) {
            return array();
        }

        return include($file);
    }


    public function lang_file($file)
    {
        return PATH_ROOT . 'vendor/data/' . $file . '.lang.php';
    }

    /**
     * 加载指定的语言项至全局语言数据中
     *
     * @author    Garbin
     * @param    none
     * @return    void
     */
    public function loadLang($lang_file)
    {
        static $loaded = array();
        $old_lang = $new_lang = array();
        $file_md5 = md5($lang_file);
        if (!isset ($loaded [$file_md5])) {
            $new_lang = include($lang_file);
            $loaded [$file_md5] = $lang_file;
        } else {
            return;
        }
        $old_lang = &$GLOBALS ['__HSKSIKE__'];
        if (is_array($old_lang)) {
            $new_lang = array_merge($old_lang, $new_lang);
        }

        $GLOBALS ['__HSKSIKE__'] = $new_lang;
    }


}