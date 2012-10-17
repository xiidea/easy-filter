<?php

namespace EzFilter\Classes;

class Admin
{
    private $load;

    function __construct(){
        $this->load=&Loader::getInstance();
        $this->init_all_hooks();
    }


    public function restrict_manage_posts(){
        $filter=$this->load->library('Filter');
        $WP=\EzFilter\getWpObject();
        if($WP->isFilterEnabled()){
            $filter->CreateInputs($WP);
        }
    }

    public function parse_query($query){
        $WP=\EzFilter\getWpObject();
        if($WP->isFilterEnabled() && $WP->isQueryConversionNeeded()){
            $filter=$this->load->library('Filter');
            $query = $filter->ConvertQuery($query,$WP);
        }
        return $query;
    }

    public function posts_where( $where ){
        $WP=\EzFilter\getWpObject();
        if($WP->isFilterEnabled() && $WP->isWhereParsingNeeded()){
            $filter=$this->load->library('Filter');
            $where = $filter->AppendWhere( $where );
        }
        return $where;
    }


    public function init_all_hooks(){
        add_action('admin_init', '\EzFilter\Classes\Assets::register_option_assets', 20);
        add_action('admin_menu', array(&$this, 'draw_admin_menu'), 20);
        add_action( 'restrict_manage_posts', array(&$this, 'restrict_manage_posts'));
        add_action( 'parse_query', array(&$this, 'parse_query'));
        add_action( 'posts_where', array(&$this, 'posts_where'));
    }
    public function draw_admin_menu(){
        $page=add_submenu_page('options-general.php', 'Settings', 'Easy Filter', 'manage_options', 'ez-filter', array(&$this,'ez_filter_option'));
        add_action( 'admin_print_styles-' . $page,  array(&$this,'enqueue_admin_option_assets'));
        add_action( 'admin_print_styles-edit.php', array(&$this,'enqueue_manage_post_assets'));
    }

    public function enqueue_admin_option_assets(){
        wp_enqueue_script('jquery');
        $WP = \EzFilter\getWpObject();
        Assets::enqueue_assets(array('js'=>array($WP->plugin_domain."-settings-js")));
    }

    function enqueue_manage_post_assets(){
        $WP = \EzFilter\getWpObject();
        $post_types = $WP->getSettings('post_types');

        if(!in_array($WP->typenow,$post_types)){
            return false;
        }

        $assetsToEnqueue = array();

        $remove_default = $WP->removeDefaultFilter();

        $post_type_settings=$WP->getSettings($WP->typenow);

        $post_type_settings = isset( $post_type_settings['config'] )?$post_type_settings['config'] : array();



        if(in_array( 'date_range', $post_type_settings )){
            $assetsToEnqueue['css'][$WP->plugin_domain."-post-css"]=$WP->plugin_domain."-post-css";
            $assetsToEnqueue['css']['jquery-style']='jquery-style';
            $assetsToEnqueue['js']['jquery']='jquery';
            $assetsToEnqueue['js']['jquery-ui-core']='jquery-ui-core';
            $assetsToEnqueue['js']['jquery-ui-datepicker']='jquery-ui-datepicker';
            $assetsToEnqueue['js'][$WP->plugin_domain."-post-js"]=$WP->plugin_domain."-post-js";
        }

        if($remove_default){
            $assetsToEnqueue['css'][$WP->plugin_domain."-post-css"]=$WP->plugin_domain."-post-css";
            $assetsToEnqueue['js']['jquery']='jquery';
            $assetsToEnqueue['js'][$WP->plugin_domain."-post-js"]=$WP->plugin_domain."-post-js";
        }
        Assets::enqueue_assets($assetsToEnqueue);
        return true;
    }

    function arrayInArray( $needle, $haystack ){
        $intersect=array_intersect( $needle, $haystack );
        return !empty($intersect);
    }

    function ez_filter_option()
    {
        $WP=\EzFilter\getWpObject();
        $data['plugin_domain']=$WP->plugin_domain;

        $data['option_updated'] = false;

        if(isset($_POST['submit-'.$data['plugin_domain']]))
        {
            if ( $varify = wp_verify_nonce( $_POST['nonce_'.$data['plugin_domain']], $data['plugin_domain'] ) )
            {
                $data['option_updated']=update_option($data['plugin_domain'], $_POST[$data['plugin_domain']]);
            }
        }

        $data['settings']=$WP->getSettings();
        $data['filter_types']=$WP->filter_types;

        $this->load->view('option',$data);
    }
}
