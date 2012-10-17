<?php

namespace EzFilter\Classes;

class EzFilter
{
    private $load;
    private $WP;

    function __construct(Loader &$loader){
        $this->load = $loader;
        $this->WP = &\EzFilter\getWpObject();;
    }

    function uninstall() {
        delete_option($this->WP->plugin_domain);
    }

    private function load_admin(){
        $this->load->library('Admin');
    }

    function run(){
        register_deactivation_hook($this->WP->plugin_file, array(&$this, 'uninstall'));
        if(is_admin()) {
            $this->load_admin();
        }
    }
}
