<?php
/*
 * Plugin Name: WP Sort Order
 * Plugin URI: http://www.websitedesignwebsitedevelopment.com/wordpress/plugins/wp-sort-order/
 * Description: Order terms (Users, Posts, Pages, Custom Post Types and Custom Taxonomies) using a Drag and Drop with jQuery ui Sortable.
 * Version: 1.1.2
 * Author: fahadmahmood
 * Author URI: https://profiles.wordpress.org/fahadmahmood/
 * Text Domain: wp-sort-order
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
* Define
*/
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	include('inc/functions.php');
	
	global $wpso_data, $wpso_pro, $wpso_premium_link, $wpso_premium, $premium_click;
	
	$wpso_data = get_plugin_data(__FILE__);
	$wpso_dir = plugin_dir_path( __FILE__ );
	
	define( 'WPSO_URL', plugins_url( '', __FILE__ ) );
	define( 'WPSO_DIR', $wpso_dir );
	$wpso_premium = $wpso_dir.'pro/wpso_extended.php';
	$wpso_pro = file_exists($wpso_premium);
	$wpso_premium_link = 'http://shop.androidbubbles.com/product/wp-sort-order-pro';
	
	$premium_click = $wpso_pro?'':'<small class="premium">(Premium Feature)</small>';
	
	if(is_admin()){
		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", 'wpso_plugin_links' );	
		
	}	
	
		
	function wpso_backup_pro($src='pro', $dst='') { 

		$plugin_dir = plugin_dir_path( __FILE__ );
		$uploads = wp_upload_dir();
		$dst = ($dst!=''?$dst:$uploads['basedir']);
		$src = ($src=='pro'?$plugin_dir.$src:$src);
		
		$pro_check = basename($plugin_dir);

		$pro_check = $dst.'/'.$pro_check.'.dat';

		if(file_exists($pro_check)){
			if(!is_dir($plugin_dir.'pro')){
				mkdir($plugin_dir.'pro');
			}
			$files = file_get_contents($pro_check);
			$files = explode('\n', $files);
			if(!empty($files)){
				foreach($files as $file){
					
					if($file!=''){
						
						$file_src = $uploads['basedir'].'/'.$file;
						//echo $file_src.' > '.$plugin_dir.'pro/'.$file.'<br />';
						$file_trg = $plugin_dir.'pro/'.$file;
						if(!file_exists($file_trg))
						copy($file_src, $file_trg);
					}
				}//exit;
			}
		}
		
		if(is_dir($src)){
			if(!file_exists($pro_check)){
				$f = fopen($pro_check, 'w');
				fwrite($f, '');
				fclose($f);
			}	
			$dir = opendir($src); 
			@mkdir($dst); 
			while(false !== ( $file = readdir($dir)) ) { 
				if (( $file != '.' ) && ( $file != '..' )) { 
					if ( is_dir($src . '/' . $file) ) { 
						wpso_backup_pro($src . '/' . $file, $dst . '/' . $file); 
					} 
					else { 
						$dst_file = $dst . '/' . $file;
						
						if(!file_exists($dst_file)){
							
							copy($src . '/' . $file,$dst_file); 
							$f = fopen($pro_check, 'a+');
							fwrite($f, $file.'\n');
							fclose($f);
						}
					} 
				} 
			} 
			closedir($dir); 
			
		}	
	}
		
	function wpso_activate() {	
		
		wpso_backup_pro();
		
	}
	register_activation_hook( __FILE__, 'wpso_activate' );
	
	if($wpso_pro)
	include($wpso_premium);
	
	//echo $wpso_premium;
	
	include('inc/hooks.php');
	