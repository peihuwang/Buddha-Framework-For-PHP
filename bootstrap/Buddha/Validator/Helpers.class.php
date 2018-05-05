<?php
class Buddha_Validator_Helpers extends Buddha_Base_Component
{
    protected static $_instance;
    /**
     * 实例化
     *
     * @static
     * @access	public
     * @return	object 返回对象
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

    /**
     * Return array specific item.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function array_get($array, $key, $default = null)
    {
        if (!Buddha_Validator_Helpers::array_accessible($array)) {
            return Buddha_Validator_Helpers::value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (Buddha_Validator_Helpers::array_exists($array, $key)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (Buddha_Validator_Helpers::array_accessible($array) && Buddha_Validator_Helpers::array_exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return Buddha_Validator_Helpers::value($default);
            }
        }

        return $array;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    public static function array_dot($array, $prepend = '')
    {
        $results = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, Buddha_Validator_Helpers::array_dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    /**
     * Check input is array accessable.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function array_accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Check array key exists.
     *
     * @param array  $array
     * @param string $key
     *
     * @return bool
     */
public static function array_exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Convert a string to snake case.
     *
     * @param string $string
     * @param string $delimiter
     *
     * @return string
     */
    public static function snake_case($string, $delimiter = '_')
    {
        $replace = '$1'.$delimiter.'$2';

        return ctype_lower($string) ? $string : strtolower(preg_replace('/(.)([A-Z])/', $replace, $string));
    }


   public static function studly_case($string)
    {
        $string = ucwords(str_replace(array('-', '_'), ' ', $string));

        return str_replace(' ', '', $string);
    }

    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}