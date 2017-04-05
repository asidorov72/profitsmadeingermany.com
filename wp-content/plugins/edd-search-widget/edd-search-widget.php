<?php
/**
 * Main plugin file.
 * This Plugin adds a search widget for the Easy Digital Downloads plugin post
 *    type independent from the regular WordPress search.
 *
 * @package   Easy Digital Downloads Search Widget
 * @author    David Decker
 * @copyright Copyright (c) 2012-2013, David Decker - DECKERWEB
 * @link      http://deckerweb.de/twitter
 *
 * Plugin Name: Easy Digital Downloads Search Widget
 * Plugin URI: http://genesisthemes.de/en/wp-plugins/edd-search-widget/
 * Description: This Plugin adds a search widget for the Easy Digital Downloads plugin post type independent from the regular WordPress search.
 * Version: 1.1.0
 * Author: David Decker - DECKERWEB
 * Author URI: http://deckerweb.de/
 * License:  GPL-2.0+
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: edd-search-widget
 * Domain Path: /languages/
 *
 * Copyright (c) 2012-2013 David Decker - DECKERWEB
 *
 *     This file is part of Easy Digital Downloads Search Widget,
 *     a plugin for WordPress.
 *
 *     Easy Digital Downloads Search Widget is free software:
 *     You can redistribute it and/or modify it under the terms of the
 *     GNU General Public License as published by the Free Software
 *     Foundation, either version 2 of the License, or (at your option)
 *     any later version.
 *
 *     Easy Digital Downloads Search Widget is distributed in the hope that
 *     it will be useful, but WITHOUT ANY WARRANTY; without even the
 *     implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 *     PURPOSE. See the GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with WordPress. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Prevent direct access to this file.
 *
 * @since 1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Sorry, you are not allowed to access this file directly.' );
}


/**
 * Setting constants.
 *
 * @since 1.0.0
 */
/** Plugin directory */
define( 'EDDSW_PLUGIN_DIR', dirname( __FILE__ ) );

/** Plugin base directory */
define( 'EDDSW_PLUGIN_BASEDIR', dirname( plugin_basename( __FILE__ ) ) );


add_action( 'init', 'ddw_eddsw_init', 1 );
/**
 * Load the textdomain and translations for the plugin, respecting custom
 *    locations and user language files.
 *
 * Further, load all needed (admin) (helper) functions files, only where needed.
 * 
 * @since 1.0.0
 *
 * @uses  load_textdomain()	To load translations first from WP_LANG_DIR sub folder.
 * @uses  load_plugin_textdomain() To additionally load default translations from plugin folder (default).
 * @uses  is_admin()
 * @uses  current_user_can()
 *
 * @param string 	$textdomain
 * @param string 	$locale
 * @param string 	$eddsw_wp_lang_dir
 * @param string 	$eddsw_lang_dir
 */
function ddw_eddsw_init() {

	/** Set textdomain */
	$textdomain = 'edd-search-widget';

	/** The 'plugin_locale' filter is also used by default in load_plugin_textdomain() */
	$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

	/** Set filter for WordPress languages directory */
	$eddsw_wp_lang_dir = apply_filters(
		'eddsw_filter_wp_lang_dir',
		WP_LANG_DIR . '/edd-search-widget/' . $textdomain . '-' . $locale . '.mo'
	);

	/** Set filter for plugin's languages directory */
	$eddsw_lang_dir = apply_filters( 'eddsw_filter_lang_dir', EDDSW_PLUGIN_BASEDIR . '/languages/' );

	/** Translations: First, look in WordPress' "languages" folder = custom & update-secure! */
	load_textdomain( $textdomain, $eddsw_wp_lang_dir );

	/** Translations: Secondly, look in plugin's "languages" folder = default */
	load_plugin_textdomain( $textdomain, FALSE, $eddsw_lang_dir );

	/** Load needed helper functions */
	require_once( EDDSW_PLUGIN_DIR . '/includes/eddsw-functions.php' );

	/** Load Shortcode function */
	require_once( EDDSW_PLUGIN_DIR . '/includes/eddsw-shortcode-search.php' );

	/** Include admin helper functions */
	if ( is_admin() ) {

		require_once( EDDSW_PLUGIN_DIR . '/includes/eddsw-admin.php' );

	}  // end-if is_admin() check

	/** Add "Widgets Page" link to plugin page */
	if ( is_admin() && current_user_can( 'edit_theme_options' ) ) {

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ) , 'ddw_eddsw_widgets_page_link' );

	}  // end-if is_admin() & cap check

	/** Define helper constant for removing search label */
	if ( ! defined( 'EDDSW_SEARCH_LABEL_DISPLAY' ) ) {
		define( 'EDDSW_SEARCH_LABEL_DISPLAY', TRUE );
	}

}  // end of function ddw_eddsw_init


add_action( 'widgets_init', 'ddw_eddsw_register_widgets' );
/**
 * Register the widget, include plugin file.
 * 
 * @since 1.0.0
 *
 * @uses  register_widget()
 */
function ddw_eddsw_register_widgets() {

	/** Load widget core part */
	require_once( EDDSW_PLUGIN_DIR . '/includes/eddsw-widget-search.php' );

	/** Register the widget */
	register_widget( 'EasyDigitalDownloads_Widget_Download_Search' );

}  // end of function ddw_eddsw_register_widgets


/**
 * Returns current plugin's header data in a flexible way.
 *
 * @since  1.1.0
 *
 * @uses   is_admin()
 * @uses   get_plugins()
 * @uses   plugin_basename()
 *
 * @param  $eddsw_plugin_value
 * @param  $eddsw_plugin_folder
 * @param  $eddsw_plugin_file
 *
 * @return string Plugin data.
 */
function ddw_eddsw_plugin_get_data( $eddsw_plugin_value ) {

	/** Bail early if we are not in wp-admin */
	if ( ! is_admin() ) {
		return;
	}

	/** Include WordPress plugin data */
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	$eddsw_plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
	$eddsw_plugin_file = basename( ( __FILE__ ) );

	return $eddsw_plugin_folder[ $eddsw_plugin_file ][ $eddsw_plugin_value ];

}  // end of function ddw_eddsw_plugin_get_data