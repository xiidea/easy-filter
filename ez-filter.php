<?php
/*
Plugin Name: Easy Filter
Plugin URI: http://wp-plugins.xiidea.net/easy-filter
Description: Easy Filter Improve The Filter option of list pages(posts/pages/any custom post types)
Version: 1.0
Author: Roni Saha
Author URI: http://helpful-roni.com
*/


namespace EzFilter;

define('EZ_FILTER_OPTION','ez_filter_options');

//include the loader and other include files
include_once dirname(__FILE__) . '/Classes/Loader.php';


function getWpObject(){
    global $wpdb,$pagenow,$typenow;
    return Classes\WP::getInstance($wpdb,$pagenow,$typenow,__FILE__,EZ_FILTER_OPTION);
}

//instantiate the loader
$loader = Classes\Loader::getInstance(__FILE__);

spl_autoload_register(array($loader,'load'));

$ez_filter= new Classes\EzFilter($loader);
$ez_filter->run();
