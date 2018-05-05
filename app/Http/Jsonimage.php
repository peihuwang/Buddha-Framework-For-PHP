<?php
class Jsonimage extends  Buddha_App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = strtolower(__CLASS__);

    }

    /**
     * 返回图片传输是否正确
     */
    public function errorDieImageFromUpload($image_arr)
    {

        if($image_arr== 0 or $image_arr=='' or count($image_arr)==0){
            return;
        }

        /*判断图片是不是格式正确 应该图片传数组*/
        if (!Buddha_Atom_Array::isValidArray($image_arr)) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444008, '图片或者文件没安数据形式发来');
        }
        /* 遍历图片数组 确保每个图片格式都正确*/
        foreach ($image_arr as $k => $v) {
            $temp_img_arr = explode(',', $v);
             $temp_base64_string = $temp_img_arr[1];

            if (!Buddha_Atom_File::checkStringIsBase64($temp_base64_string)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000011, '不是base64格式');
            }

            if (!Buddha_Atom_File::checkBase64Img($v)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000012, '不是base64格式的图片');
            }


        }


    }








}