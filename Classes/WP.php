<?php

namespace EzFilter\Classes;

class WP
{
    private $_settings=NULL;
    private $_db="";
    private $_pagenow="";
    private $_typenow="";
    private static $_instance=NULL;
    private $_remove_default_filter=array();
    private $_filter_types=array(
                                'month' => 'Month'
                                ,'category' => 'Category Filter'
                                ,'author' => 'Author'
                                ,'date_range' => 'Date Range'
                                ,'taxonomy' => 'Taxonomy'
                        );

    private $_plugin_domain='';
    private $_plugin_file='';

    function __construct ( &$wpdb, $pagenow, $typenow = "", $plugin_file = "", $plugin_domain = "plugin_option" ){
        $this->_db = &$wpdb;
        $this->_pagenow = $pagenow;
        $this->_typenow = $typenow === ""?'post':$typenow;;
        $this->_plugin_domain = $plugin_domain;
        $this->_plugin_file = $plugin_file;
    }

    public static function getInstance (&$db = NULL, $pagenow = "", $typenow = "", $plugin_file = "", $plugin_domain = "plugin_option" ) {
        if(!isset(self::$_instance)) {
            self::$_instance = new WP ( $db, $pagenow, $typenow, $plugin_file, $plugin_domain );
        }
        return self::$_instance;
    }

    public function __get($name)
    {
        $value=$this->get($name);

        if($value !== NULL){
            return $value;
        }

        $verb=strtolower(substr($name, 0, 3));
        if($verb=="get"){
            $var_name=strtolower(substr($name, 3));
            $value=$this->get($var_name);
        }

        if($value === NULL){
            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __get(): ' . $name .
                    ' in ' . $trace[0]['file'] .
                    ' on line ' . $trace[0]['line'],
                E_USER_NOTICE);
        }
        return NULL;
    }

    private function get($name){
        $name=strtolower($name);
        $var_name="_{$name}";
        if (isset($this->$var_name)) {
            return $this->$var_name;
        }
        return NULL;
    }

    public function getSettings($type=""){
        if(!$this->_settings){
           $this->_settings=get_option($this->_plugin_domain);
        }
        if($type!=""){
            return isset($this->_settings[$type])?$this->_settings[$type]:array();
        }
        return $this->_settings;
    }

    public function isFilterEnabled(){
        $post_types = $this->getSettings('post_types');
        return in_array($this->_typenow,$post_types);
    }

    public function isQueryConversionNeeded(){
        $settings =  $this->getSettings($this->_typenow);

        if(isset($settings['style']) && $this->hasSelectiveTextInput($settings['style'])){
            return true;
        }

        if(!($this->_pagenow == 'edit.php' && isset($_GET['meta_key']) &&
            $_GET['meta_key']!='' && isset($_GET['meta_value']) && $_GET['meta_value']!='')){
            return false;
        }
        return in_array('taxonomy',$settings['config']) && isset($settings['taxonomy']) && $settings['taxonomy'] == 'combo';
    }

    public function isWhereParsingNeeded(){
        if(!($this->_pagenow == 'edit.php' && isset( $_GET['date_s'] ) && isset( $_GET['date_e'] ))){
            return false;
        }
        $settings =  $this->getSettings($this->_typenow);
        return in_array('date_range',$settings['config']);
    }

    public function removeDefaultFilter($type=null){
        $post_type = $type !== null ? $type : $this->_typenow;

        if(isset($this->_remove_default_filter[$post_type])){
            return $this->_remove_default_filter[$post_type];
        }

        $post_type_settings=$this->getSettings($post_type);
        $post_type_settings = isset( $post_type_settings['config'] )?$post_type_settings['config'] : array();

        $default_filter = array('month','category');
        $intersect = array_intersect($post_type_settings,$default_filter);
        $this->_remove_default_filter[$post_type] = empty($intersect) ? $default_filter : array_diff($default_filter,$intersect);
        return $this->_remove_default_filter[$post_type];
    }

    public function hasSelectiveTextInput($style=array()){
        foreach($style as $tax_slug => $input){
            if( $input == 'text' && isset( $_GET[$tax_slug] ) && $_GET[$tax_slug] != '' ){
                return true;
            }
        }
        return false;
    }
}
