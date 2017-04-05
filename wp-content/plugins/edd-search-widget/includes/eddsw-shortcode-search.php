<?php
/**
 * Shortcode: "Downloads" search box.
 *
 * @package    Easy Digital Downloads Search Widget
 * @subpackage Shortcode
 * @author     David Decker - DECKERWEB
 * @copyright  Copyright (c) 2013, David Decker - DECKERWEB
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link       http://genesisthemes.de/en/wp-plugins/genesis-connect-edd/
 * @link       http://deckerweb.de/twitter
 *
 * @since      1.1.0
 */

/**
 * Prevent direct access to this file.
 *
 * @since 1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Sorry, you are not allowed to access this file directly.' );
}


add_shortcode( 'edd-searchbox', 'ddw_eddsw_shortcode_search' );
/**
 * Shortcode: Search Box (for EDD "Downloads").
 *
 * @since  1.1.0
 *
 * @param  array 	$defaults 	Default values of Shortcode parameters.
 * @param  array 	$atts 		Attributes passed from Shortcode.
 * @param  string 	$output
 * @param  string 	$eddsw_label_string
 * @param  string 	$eddsw_placeholder_string
 * @param  string 	$eddsw_search_string
 * @param  string 	$form
 *
 * @return string HTML content of the shortcode.
 */
function ddw_eddsw_shortcode_search( $atts ) {

	/** Set default shortcode attributes */
	$defaults = array(
		'label_text'        => __( 'Search downloads for:', 'edd-search-widget' ),
		'placeholder_text'  => __( 'Search downloads&#x2026;', 'edd-search-widget' ),
		'button_text'       => __( 'Search', 'edd-search-widget' ),
		'class'             => '',	// easter egg, kind of :)
	);

	/** Default shortcode attributes */
	$atts = shortcode_atts( $defaults, $atts, 'edd-searchbox' );

	/** Set filters for various strings */
	$eddsw_label_string = ( ! empty( $atts[ 'label_text' ] ) ) ? apply_filters( 'eddsw_filter_label_string', $atts[ 'label_text' ] ) : FALSE;
	$eddsw_placeholder_string = apply_filters( 'eddsw_filter_placeholder_string', $atts[ 'placeholder_text' ] );
	$eddsw_search_string = apply_filters( 'eddsw_filter_search_string', $atts[ 'button_text' ] );

	/** Construct the search form */
	$form = '<form role="search" method="get" id="searchform" class="searchform eddsw-search-form" action="' . home_url() . '">';
	$form .= '<div class="eddsw-form-container">';
		if ( EDDSW_SEARCH_LABEL_DISPLAY && $eddsw_label_string ) {
			$form .= '<label class="screen-reader-text eddsw-label" for="s">' . esc_attr__( $eddsw_label_string ) . '</label>';
			$form .= '<br />';
		}
		$form .= '<input type="hidden" name="post_type" value="' . ddw_eddsw_download_cpt() . '" />';
		$form .= '<input type="text" value="' . get_search_query() . '" name="s" id="s" class="s eddsw-search-field" placeholder="' . esc_attr__( $eddsw_placeholder_string ) . '" />';
		$form .= '<input type="submit" id="searchsubmit" class="searchsubmit eddsw-search-submit" value="' . esc_attr__( $eddsw_search_string ) . '" />';

	$form .= '</div>';
	$form .= '</form>';

	/** Prepare the shortcode frontend output */
	$output = sprintf(
		'<div id="eddsw-form-wrapper"%1$s>%2$s</div>',
		! empty( $atts[ 'class' ] ) ? ' class="' . esc_attr( $atts[ 'class' ] ) . '"' : '',
		$form
	);

	/** Return Shortcode's HTML - filterable */
    return apply_filters( 'eddsw_filter_shortcode_search', $output, $atts );

}  // end of function ddw_eddsw_shortcode_search