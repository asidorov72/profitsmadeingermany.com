<?php

	if(!function_exists('pre')){
		function pre($data){
			if(isset($_GET['debug'])){
				pree($data);
			}
		}	 
	} 
		
	if(!function_exists('pree')){
	function pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';	
		
		}	 
	} 	

	if ( ! function_exists("wpso_plugin_links"))
	{
		function wpso_plugin_links($links) { 
			global $wpso_premium_link, $wpso_pro;
			
			$settings_link = '<a href="options-general.php?page=wpso-settings">Settings</a>';
			
			if($wpso_pro){
				array_unshift($links, $settings_link); 
			}else{
				 
				$wpso_premium_link = '<a href="'.$wpso_premium_link.'" title="Go Premium" target=_blank>Go Premium</a>'; 
				array_unshift($links, $settings_link, $wpso_premium_link); 
			
			}
			
			
			return $links; 
		}
	}
	
/**
* Uninstall hook
*/
	
	register_uninstall_hook( __FILE__, 'wpso_uninstall' );
	
	function wpso_uninstall()
	{
		global $wpdb;
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$curr_blog = $wpdb->blogid;
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				wpso_uninstall_db();
			}
			switch_to_blog( $curr_blog );
		} else {
			wpso_uninstall_db();
		}
	}
	function wpso_uninstall_db()
	{
		global $wpdb;
		$result = $wpdb->query( "DESCRIBE $wpdb->terms `term_order`" );
		if ( $result ){
			$query = "ALTER TABLE $wpdb->terms DROP `term_order`";
			$result = $wpdb->query( $query );
		}
		
		$result = $wpdb->query( "DESCRIBE $wpdb->users `user_order`" );
		if ( $result ){
			$query = "ALTER TABLE $wpdb->users DROP `user_order`";
			$result = $wpdb->query( $query );
		}
	
		delete_option( 'wpso_activation' );	
	}