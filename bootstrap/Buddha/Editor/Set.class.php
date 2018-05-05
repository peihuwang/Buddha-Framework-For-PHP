<?php

class Buddha_Editor_Set{

    protected $smarty;
    protected static $_instance;
    public static function getInstance($options=NULL)
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
        $this->smarty = Smarty::getInstance(
            Buddha::getSmartyConfig()
        );
    }

    public static function rteSafe($strText){
        $strText = str_replace('<', '&lt;', $strText);
        $strText = str_replace('>', '&gt;', $strText);
        return $strText;
    }


    public   function setEditor($var,$smarty_editor='editor',$smarty_editorjs='editorjs',$smarty_editorjstxt='editorjstxt') {
        $_HSKENV['HSK_URL'] = Buddha::$buddha_array['host'];

        $editor = array();
        $editorjs = '<script language="javascript" src="' . $_HSKENV['HSK_URL'] . 'vendor/kindeditor/kindeditor-min.js"></script>
				 <script language="javascript" src="' . $_HSKENV['HSK_URL'] . 'vendor/kindeditor/lang/zh_CN.js"></script>';
        $editorjstxt = "<script>KindEditor.ready(function(K) ";
        $editorjstxt.="{";
        foreach ($var as $v) {
            $editorjstxt .= "editor = K.create('textarea[name={$v['id']}]', { 
            filterMode: false,allowFileManager : true, afterCreate : function() { this.sync(); }, afterBlur:function(){ this.sync(); } });";
        }
        $editorjstxt .= "});</script>";
        foreach ($var as $v) {
            $editor[$v['id']] = '<textarea style="width:'.$v['width'].'%;height:' . $v['height'] . 'px;" id="' . $v['id'] . '" name="' . $v['id'] . '">' . Buddha_Editor_Set::rteSafe($v['content']). '</textarea>';

        }

        $this->smarty ->assign(''.$smarty_editorjs,$editorjs);
        $this->smarty ->assign(''.$smarty_editorjstxt,$editorjstxt);
        $this->smarty ->assign ( ''.$smarty_editor, $editor );

        return $editor;
    }


}