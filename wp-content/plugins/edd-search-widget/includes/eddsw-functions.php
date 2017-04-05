<?php
/**
 * Load the needed helper logic/ functions.
 *
 * @package    Easy Digital Downloads Search Widget
 * @subpackage Helper Functions
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


/**
 * Check and retrieve the correct ID/tag of the registered post type 'Download' by EDD.
 *
 * @since  1.1.0
 *
 * @uses   post_type_exists()
 *
 * @param  $eddsw_download_cpt
 *
 * @return string "Downloads" post type slug.
 */
function ddw_eddsw_download_cpt() {

	/** Get the proper 'Download' post type ID/tag */
	if ( post_type_exists( 'edd_download' ) ) {

		$eddsw_download_cpt = 'edd_download';

	} elseif ( post_type_exists( 'download' ) ) {

		$eddsw_download_cpt = 'download';

	}

	/** EDD "Downloads" post type slug */
	return $eddsw_download_cpt;

}  // end of function ddw_eddsw_download_cpt