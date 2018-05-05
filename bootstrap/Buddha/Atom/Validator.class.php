<?php

/**
 * Class Buddha_Atom_Validator
 */
class Buddha_Atom_Validator{
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

    /**
     * @param $input
     * @param $rules
     * @return int|string
     *
     *
     *
   example:
    //验证
    $rules = array(
    '用户' => 'required|min:5',
    '密码' => 'required|min:3',
    );

    $input = array(
    '用户' => $admin_user,
    '密码' => $admin_pw
    );

     */
    public static function getErrorMsg($input,$rules)
    {
        //初始化工厂对象
        $factory = new Buddha_Validator_Factory(new Buddha_Validator_Translator);


        $validator = $factory->make($input, $rules);

        //判断验证是否通过
        if ($validator->passes()) {
            //echo 'pass';
            //通过
            return 0;
        } else {
            $error = $validator->messages();
            $error = json_decode($error, true);
            $errorstr = '';
            foreach ($error as $k => $v) {
                $errorstr .= $v[0];
                $errorstr .= ',';
            }
            return $errorstr = substr($errorstr, 0, strlen($errorstr) - 1);

        }
    }



}