<?php

namespace EzFilter\Classes;

class Loader{

    private $_absolute_path;
    private static $_instance = NULL;

    private $loaded=array();


    public function __construct($file_loc) {
        $this->_absolute_path = dirname($file_loc);
    }


    public static function getInstance($file_loc=NULL) {
        if(!isset(self::$_instance)) {
            self::$_instance = new Loader($file_loc);
        }
        return self::$_instance;
    }

    public function load ( $sClassName ){
        $name_space_part = explode('\\',$sClassName);
        if($name_space_part[0] === 'EzFilter'){
            array_shift($name_space_part);
            $sClassName = implode ( DIRECTORY_SEPARATOR, $name_space_part );
            $file = $this->_absolute_path . DIRECTORY_SEPARATOR.$sClassName . '.php';
            require_once( $file );
        }
    }

    public function library($library){
        if(!array_key_exists($library,$this->loaded)){
            $class_name=__NAMESPACE__."\\{$library}";
            if(is_callable("$class_name::getInstance")){
                $this->loaded[$library] = $class_name::getInstance();
            }else{
                $this->loaded[$library] = new $class_name();
            }
        }
        return $this->loaded[$library];
    }

    public function view($view,$data=array(),$return=FALSE){
        extract($data,EXTR_SKIP);
        $view_file=$this->_absolute_path."/Views/{$view}.php";
        if(!file_exists($view_file)){
            display_error('Request File Could not be found : '.$view_file);
            return FALSE;
        }
        if($return){
            ob_start();
        }
        include $view_file;
        if($return){
            return ob_get_clean();
        }

        return TRUE;
    }
}