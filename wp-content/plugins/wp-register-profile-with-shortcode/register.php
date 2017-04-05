<?php
/*
Plugin Name: WP Register Profile With Shortcode
Plugin URI: https://wordpress.org/plugins/wp-register-profile-with-shortcode/
Description: This is a simple registration form in the widget. just install the plugin and add the register widget in the sidebar. Thats it. :)
Version: 3.3.2
Text Domain: wp-register-profile-with-shortcode
Domain Path: /languages
Author: aviplugins.com
Author URI: http://www.aviplugins.com/
*/

/**
	  |||||   
	<(`0_0`)> 	
	()(afo)()
	  ()-()
**/

include_once dirname( __FILE__ ) . '/admin_notification_mail.php';
include_once dirname( __FILE__ ) . '/settings.php';
include_once dirname( __FILE__ ) . '/register_afo_widget.php';
include_once dirname( __FILE__ ) . '/register_afo_widget_shortcode.php';
include_once dirname( __FILE__ ) . '/form_class.php';


function wp_register_profile_set_default_data() {
	
	$wprw_mail_to_user_subject = 'Registration Successful';
	$wprw_mail_to_user_body = 'We are pleased to confirm your registration for #site_name#. Below is your login credential.
<br><br>
<strong>Username</strong> : #user_name#
<br>
<strong>Password</strong> : #user_password#
<br>
<strong>Site Link</strong> : #site_url#
<br><br>
Thank You';
	
	update_option( 'new_user_register_mail_subject', $wprw_mail_to_user_subject );
	update_option( 'new_user_register_mail_body', $wprw_mail_to_user_body );
}

register_activation_hook( __FILE__, 'wp_register_profile_set_default_data' );