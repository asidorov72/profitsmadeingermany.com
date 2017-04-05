<?php
/**
 * Helper functions for the admin - plugin links and help tabs.
 *
 * @package    Easy Digital Downloads Search Widget
 * @subpackage Admin
 * @author     David Decker - DECKERWEB
 * @copyright  Copyright (c) 2012-2013, David Decker - DECKERWEB
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link       http://genesisthemes.de/en/wp-plugins/edd-search-widget/
 * @link       http://deckerweb.de/twitter
 *
 * @since      1.0.0
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
 * Setting helper links constant
 *
 * @since 1.0.0
 *
 * @uses  get_locale()
 */
define( 'EDDSW_URL_TRANSLATE',		'http://translate.wpautobahn.com/projects/wordpress-plugins-deckerweb/edd-search-widget' );
define( 'EDDSW_URL_WPORG_FAQ',		'http://wordpress.org/extend/plugins/edd-search-widget/faq/' );
define( 'EDDSW_URL_WPORG_FORUM',	'http://wordpress.org/support/plugin/edd-search-widget' );
define( 'EDDSW_URL_WPORG_PROFILE',	'http://profiles.wordpress.org/daveshine/' );
define( 'EDDSW_URL_SNIPPETS',		'https://gist.github.com/2857613' );
define( 'EDDSW_PLUGIN_LICENSE', 	'GPL-2.0+' );
if ( get_locale() == 'de_DE' || get_locale() == 'de_AT' || get_locale() == 'de_CH' || get_locale() == 'de_LU' ) {
	define( 'EDDSW_URL_DONATE', 	'http://genesisthemes.de/spenden/' );
	define( 'EDDSW_URL_PLUGIN',		'http://genesisthemes.de/plugins/edd-search-widget/' );
} else {
	define( 'EDDSW_URL_DONATE', 	'http://genesisthemes.de/en/donate/' );
	define( 'EDDSW_URL_PLUGIN', 	'http://genesisthemes.de/en/wp-plugins/edd-search-widget/' );
}


/**
 * Add "Widgets" link to plugin page
 *
 * @since  1.0.0
 *
 * @param  $eddsw_links
 * @param  $eddsw_widgets_link
 *
 * @return strings Widgets link
 */
function ddw_eddsw_widgets_page_link( $eddsw_links ) {

	/** Widgets Admin link */
	$eddsw_widgets_link = sprintf(
		'<a href="%s" title="%s">%s</a>',
		admin_url( 'widgets.php' ),
		__( 'Go to the Widgets settings page', 'edd-search-widget' ),
		__( 'Widgets', 'edd-search-widget' )
	);

	/** Set the order of the links */
	array_unshift( $eddsw_links, $eddsw_widgets_link );

	/** Display plugin settings links */
	return apply_filters( 'eddsw_filter_settings_page_link', $eddsw_links );

}  // end of function ddw_eddsw_widgets_page_link


add_filter( 'plugin_row_meta', 'ddw_eddsw_plugin_links', 10, 2 );
/**
 * Add various support links to plugin page
 *
 * @since 1.0.0
 *
 * @param  $eddsw_links
 * @param  $eddsw_file
 *
 * @return strings plugin links
 */
function ddw_eddsw_plugin_links( $eddsw_links, $eddsw_file ) {

	/** Capability check */
	if ( ! current_user_can( 'install_plugins' ) ) {

		return $eddsw_links;

	}  // end-if cap check

	/** List additional links only for this plugin */
	if ( $eddsw_file == EDDSW_PLUGIN_BASEDIR . '/edd-search-widget.php' ) {

		$eddsw_links[] = '<a href="' . esc_url( EDDSW_URL_WPORG_FAQ ) . '" target="_new" title="' . __( 'FAQ', 'edd-search-widget' ) . '">' . __( 'FAQ', 'edd-search-widget' ) . '</a>';

		$eddsw_links[] = '<a href="' . esc_url( EDDSW_URL_WPORG_FORUM ) . '" target="_new" title="' . __( 'Support', 'edd-search-widget' ) . '">' . __( 'Support', 'edd-search-widget' ) . '</a>';

		$eddsw_links[] = '<a href="' . esc_url( EDDSW_URL_TRANSLATE ) . '" target="_new" title="' . __( 'Translations', 'edd-search-widget' ) . '">' . __( 'Translations', 'edd-search-widget' ) . '</a>';

		$eddsw_links[] = '<a href="' . esc_url( EDDSW_URL_DONATE ) . '" target="_new" title="' . __( 'Donate', 'edd-search-widget' ) . '"><strong>' . __( 'Donate', 'edd-search-widget' ) . '</strong></a>';

	}  // end-if plugin links

	/** Output the links */
	return apply_filters( 'eddsw_filter_plugin_links', $eddsw_links );

}  // end of function ddw_eddsw_plugin_links


add_action( 'sidebar_admin_setup', 'ddw_eddsw_load_widgets_help' );
/**
 * Load plugin help tab after core help tabs on Widget admin page.
 *
 * @since 1.0.0
 *
 * @global mixed $pagenow
 */
function ddw_eddsw_load_widgets_help() {

	global $pagenow;

	add_action( 'admin_head-' . $pagenow, 'ddw_eddsw_help_tab' );

}  // end of function ddw_eddsw_load_widgets_help



add_action( 'admin_init', 'ddw_eddsw_load_edd_help' );
/**
 * Load plugin help tab on EDD admin page.
 *
 * @since 1.1.0
 *
 * @global mixed $edd_settings_page, $edd_add_ons_page
 */
function ddw_eddsw_load_edd_help() {

	global $edd_settings_page, $edd_add_ons_page;

	/** Only add help if EDD backend is active */
	if ( $edd_settings_page && $edd_add_ons_page ) {

		add_action( 'load-' . $edd_settings_page, 'ddw_eddsw_help_tab', 20 );
		add_action( 'load-' . $edd_add_ons_page, 'ddw_eddsw_help_tab', 20 );

	}  // end-if EDD admin check

}  // end of function ddw_eddsw_load_edd_help


/**
 * Create and display plugin help tab content.
 *
 * @since  1.0.0
 *
 * @uses   get_current_screen()
 * @uses   WP_Screen::add_help_tab()
 * @uses   WP_Screen::set_help_sidebar()
 * @uses   ddw_eddsw_help_sidebar_content()
 *
 * @global mixed $eddsw_widgets_screen, $pagenow
 */
function ddw_eddsw_help_tab() {

	global $eddsw_widgets_screen, $pagenow;

	$eddsw_widgets_screen = get_current_screen();

	/** Display help tabs only for WordPress 3.3 or higher */
	if ( ! class_exists( 'WP_Screen' )
		|| ! $eddsw_widgets_screen
		|| ! defined( 'EDD_PLUGIN_DIR' )
	) {
		return;
	}

	/** Add the help tab */
	$eddsw_widgets_screen->add_help_tab( array(
		'id'       => 'eddsw-widgets-help',
		'title'    => __( 'EDD Search Widget', 'edd-search-widget' ),
		'callback' => apply_filters( 'eddsw_filter_help_tab_content', 'ddw_eddsw_help_tab_content' ),
	) );

	/** Add help sidebar */
	if ( ( $pagenow != 'widgets.php' ) && ( 'edd-addons' == $_GET[ 'page' ] ) ) {

		$eddsw_widgets_screen->set_help_sidebar( ddw_eddsw_help_sidebar_content() );

	}  // end-if $pagehook check

}  // end of function ddw_eddsw_help_tab


/**
 * Create and display plugin help tab content.
 *
 * @since 1.0.0
 *
 * @uses  ddw_eddsw_plugin_get_data()
 */
function ddw_eddsw_help_tab_content() {

	echo '<h3>' . __( 'Plugin', 'edd-search-widget' ) . ': ' . __( 'Easy Digital Downloads Search Widget', 'edd-search-widget' ) . ' <small>v' . esc_attr( ddw_eddsw_plugin_get_data( 'Version' ) ) . '</small></h3>';

	echo '<p><strong>' . sprintf( __( 'Added Widget by the plugin: %s', 'edd-search-widget' ), '<em>' . __( 'EDD Downloads Search', 'edd-search-widget' ) . '</em>' ) . '</strong></p>' .
		'<p><blockquote>' . sprintf( __( 'It searches only in the post type %s and outputs the results formatted like the other search results (of WordPress).', 'edd-search-widget' ), '<em>' . __( 'Download', 'edd-search-widget' ) . '</em>' ) . '</blockquote></p>' .
			'<p><blockquote>' . __( 'Please note: This plugin does not mix up its displayed search results with WordPress built-in search. It is limited to the Easy Digital Downloads post type. For enhanced styling of the widget and/or the search results please have a look on the FAQ page linked below.', 'edd-search-widget' ) . '</blockquote></p>';

	echo '<p><strong>' . sprintf( __(' Provided Shortcode by the plugin: %s', 'edd-search-widget' ), '<code>[edd-searchbox]</code>' ) . '</strong></p>' .
		'<ul>' .
			'<li><em>' . __( 'Supporting the following parameters', 'edd-search-widget' ) . ':</em></li>' .
			'<li><code>label_text</code> &mdash; ' . __( 'Label text before the input field', 'edd-search-widget' ) . '</li>' .
			'<li><code>placeholder_text</code> &mdash; ' . __( 'Input field placeholder text', 'edd-search-widget' ) . '</li>' .
			'<li><code>button_text</code> &mdash; ' . __( 'Submit button text', 'edd-search-widget' ) . '</li>' .
			'<li><code>class</code> &mdash; ' . sprintf( __( 'Can be a custom class, added to the wrapper %s container', 'edd-search-widget' ), '<code>div</code>' ) . '</li>' .
		'</ul>';

	echo '<p><strong>' . __( 'Important plugin links:', 'edd-search-widget' ) . '</strong>' . 
		'<br /><a href="' . esc_url( EDDSW_URL_PLUGIN ) . '" target="_new" title="' . __( 'Plugin Homepage', 'edd-search-widget' ) . '">' . __( 'Plugin Homepage', 'edd-search-widget' ) . '</a> | <a href="' . esc_url( EDDSW_URL_WPORG_FAQ ) . '" target="_new" title="' . __( 'FAQ', 'edd-search-widget' ) . '">' . __( 'FAQ', 'edd-search-widget' ) . '</a> | <a href="' . esc_url( EDDSW_URL_WPORG_FORUM ) . '" target="_new" title="' . __( 'Support', 'edd-search-widget' ) . '">' . __( 'Support', 'edd-search-widget' ) . '</a> | <a href="' . esc_url( EDDSW_URL_TRANSLATE ) . '" target="_new" title="' . __( 'Translations', 'edd-search-widget' ) . '">' . __( 'Translations', 'edd-search-widget' ) . '</a> | <a href="' . esc_url( EDDSW_URL_DONATE ) . '" target="_new" title="' . __( 'Donate', 'edd-search-widget' ) . '"><strong>' . __( 'Donate', 'edd-search-widget' ) . '</strong></a></p>';

	echo '<p><a href="http://www.opensource.org/licenses/gpl-license.php" target="_new" title="' . esc_attr( EDDSW_PLUGIN_LICENSE ). '">' . esc_attr( EDDSW_PLUGIN_LICENSE ). '</a> &copy; 2012-' . date( 'Y' ) . ' <a href="' . esc_url( ddw_eddsw_plugin_get_data( 'AuthorURI' ) ) . '" target="_new" title="' . esc_attr__( ddw_eddsw_plugin_get_data( 'Author' ) ) . '">' . esc_attr__( ddw_eddsw_plugin_get_data( 'Author' ) ) . '</a></p>';

}  // end of function ddw_eddsw_help_tab_content


/**
 * Helper function for returning the Help Sidebar content.
 *
 * @since  1.1.0
 *
 * @uses   ddw_eddsw_plugin_get_data()
 *
 * @return string HTML content for help sidebar.
 */
function ddw_eddsw_help_sidebar_content() {

	$eddsw_help_sidebar = '<p><strong>' . __( 'More about the plugin author', 'edd-search-widget' ) . '</strong></p>' .
		'<p>' . __( 'Social:', 'edd-search-widget' ) . '<br /><a href="http://twitter.com/#!/deckerweb" target="_blank">Twitter</a> | <a href="http://www.facebook.com/deckerweb.service" target="_blank">Facebook</a> | <a href="http://deckerweb.de/gplus" target="_blank">Google+</a> | <a href="' . esc_url( ddw_eddsw_plugin_get_data( 'AuthorURI' ) ) . '" target="_blank" title="@ deckerweb.de">deckerweb</a></p>' .
		'<p><a href="' . esc_url( EDDSW_URL_WPORG_PROFILE ) . '" target="_blank" title="@ WordPress.org">@ WordPress.org</a></p>';

	return apply_filters( 'eddtb_filter_help_sidebar_content', $eddsw_help_sidebar );

}  // end of function ddw_eddsw_help_sidebar_content