<?php

namespace EzFilter\Classes;

class Assets
{

    public static function register_option_assets(){
        $WP = \EzFilter\getWpObject();
        wp_register_script( $WP->plugin_domain."-settings-js", plugins_url('/assets/js/options.js', $WP->plugin_file) );
        wp_register_script( $WP->plugin_domain."-post-js", plugins_url('/assets/js/post.js', $WP->plugin_file) );
        wp_register_style( $WP->plugin_domain."-post-css", plugins_url('/assets/css/posts.css', $WP->plugin_file) );
        wp_register_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);
    }

    public static function enqueue_assets($assets=array()){
        if(isset($assets['css'])){
            foreach($assets['css'] as $css){
                wp_enqueue_style($css);
            }
        }

        if(isset($assets['js'])){
            foreach($assets['js'] as $js){
                wp_enqueue_script($js);
            }
        }
    }
}
