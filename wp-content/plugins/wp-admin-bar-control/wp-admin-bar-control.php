<?php

/*
  Plugin Name: WP Admin Bar Control
  Description: Add Plugins list to your Admin Bar. Activate and Deactivate plugins without page reload and moving to plugins page.
	Version: 0.4
  Author: Alex Egorov
	Author URI: https://yummi.club
	Plugin URI: https://wordpress.org/plugins/yummi-admin-bar-control/
  GitHub Plugin URI:
  License: GPLv2 or later (license.txt)
  Text Domain: yabp
  Domain Path: /languages
*/
global $yabp;
$yabp = get_option('yabp');
//error_reporting(E_ALL);
define('WPABC_URL', plugins_url( '/', __FILE__ ) );
define('WPABC_PATH', plugin_dir_path(__FILE__) );
define('WPABC_PREF', $wpdb->base_prefix.'n_' );

// Async load
if (!function_exists('async_scripts')){
  function async_scripts($url) {
      if ( strpos( $url, '#async') === false )
          return $url;
      else if ( is_admin() )
          return str_replace( '#async', '', $url );
      else
      return str_replace( '#async', '', $url )."' async='async";
  }
  add_filter( 'clean_url', 'async_scripts', 11, 1 );
}

require WPABC_PATH.'/includes/admin.php';
// include_once 'includes/shortcodes.php';
// include_once 'includes/widget.php';


  // wp_enqueue_style( 'snow' , WPABC_URL.'includes/css/snow.min.css');
  // add_action( 'admin_enqueue_scripts', 'load_admin_styles' );
  // add_action('admin_footer','wpabc_options');
  // add_action('admin_header','wpabc_options');

  //   //Second solution : two or more files.
  //   add_action( 'admin_enqueue_scripts', 'load_admin_styles' );
  //   function load_admin_styles() {
  //     wp_enqueue_style( 'admin_css_foo', get_template_directory_uri() . '/admin-style-foo.css', false, '1.0.0' );
  //     wp_enqueue_style( 'admin_css_bar', get_template_directory_uri() . '/admin-style-bar.css', false, '1.0.0' );
  //   }

  // $mobile = wp_is_mobile() ? true : null;
  // if( !$mobile ) {
  //   wp_enqueue_script( 'yabp-'.$this->place.'-scripts', wpabc_UPLOAD_URL.'js/'.$filename,$parents,VER_RCL,$in_footer);
  // }

  add_action('admin_enqueue_scripts', 'wpabc_scripts');
  function wpabc_scripts(){
    global $yabp;

    if( $yabp['style'] == 'yummi' ){
      wp_enqueue_style( 'yummi', WPABC_URL . '/includes/css/admin_style.min.css' );
      wp_enqueue_style( 'yummi-hint', WPABC_URL . '/includes/css/hint.min.css' );
    }
  }

  // add_action('admin_footer','wpabc_header');
  // function wpabc_header(){
  //   global $yabp;
  //
  //   if( is_array($yabp['mcss']) ){
  //     $mcss = '';
  //     for ($i=0; $i < count($yabp['mcss']); $i++) {
  //       $mcss .= $yabp['mcss'];
  //     }
  //   }
  //   echo '<style>'.$mcss.$yabp['css'].'</style>'; // <script type="text/javascript">alert("yep!");</script>
  // }

/* Multiplugin functions */
register_activation_hook(__FILE__, 'wpabc_activation');
function wpabc_activation() {}
register_deactivation_hook( __FILE__, 'wpabc_deactivation' );
function wpabc_deactivation() {}

register_uninstall_hook( __FILE__, 'wpabc_uninstall' );
function wpabc_uninstall() {}

add_filter('plugin_action_links', 'wpabc_plugin_action_links', 10, 2);
function wpabc_plugin_action_links($links, $file) {
    static $this_plugin;
    if (!$this_plugin)
        $this_plugin = plugin_basename(__FILE__);

    if ($file == $this_plugin) { // check to make sure we are on the correct plugin
			//$settings_link = '<a href="https://yummi.club/" target="_blank">' . __('Demo', 'yabp') . '</a> | ';
			$settings_link = '<a href="https://yummi.club/paypal" target="_blank"><span class="dashicons dashicons-heart"></span> ' . __('Donate', 'yabp') . '</a> | <a href="admin.php?page=yabp">' . __('Settings') . '</a>'; // the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page

      array_unshift($links, $settings_link); // add the link to the list
    }
    return $links;
}
/* /Multiplugin functions */
