<?php
/**
 * 框架路由类
 *
 * @author tinystar
 */
class Buddha_Run_Urils {
    private function __construct() {

    }

    public static function getClasses($pre_path = '/') {
        $classes_action = array();
        if(TPL_DIR!='NONE'){
            $classes_action =  Buddha_Tool_File::listFile($pre_path.'app/Http/Controllers/'.ucfirst(TPL_DIR).'/',
                $pre_path.'app/Http/Controllers/'.ucfirst(TPL_DIR),
                array('php')
            );
        }


        $classes_model = Buddha_Tool_File::listFile($pre_path.'app/Http/',
            $pre_path.'app/Http',
            array('php')
        );




        $classes = array_merge($classes_action, $classes_model);

        return $classes;
    }


}