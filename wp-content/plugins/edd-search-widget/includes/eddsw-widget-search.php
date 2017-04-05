<?php
/**
 * EDDSW: Search Downloads Widget.
 *
 * @package    Easy Digital Downloads Search Widget
 * @subpackage Widgets
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
 * The main plugin class - creating the EDD search widget
 *
 * @since 1.0.0
 */
class EasyDigitalDownloads_Widget_Download_Search extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * Set up the widget's unique name, ID, class, description, and other options.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	
		$widget_options = apply_filters( 'eddsw_filter_search_widget_options', array(
			'classname'   => 'edd_search',
			'description' => esc_html__( 'Search box for the Easy Digital Downloads plugin. Search in downloads only. (No mix up with regular WordPress search!)', 'edd-search-widget' ),
		) );
		
		/* Set up (additional) widget control options. */
		$control_options = array(
			'width' => 375
		);

		/** Create the widget */
		parent::__construct(
			'edd_search',
			__( 'EDD Downloads Search', 'edd-search-widget' ),
			$widget_options,
			$control_options
		);

	}  // end of method __construct


	/**
	 * Display the widget, based on the parameters/ arguments set through the widget options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
	
		/** Check display option for this widget and optionally disable it from displaying */
		if (
			/** Downloads: single */
			( ( 'single_downloads' == $instance[ 'widget_display' ] ) && ! is_singular( ddw_eddsw_download_cpt() ) )

			/** Downloads: archives */
			|| ( ( 'downloads_archives' == $instance[ 'widget_display' ] ) && ! is_post_type_archive( ddw_eddsw_download_cpt() ) )

			/** Downloads: taxonomies */
			|| ( ( 'downloads_tax' == $instance[ 'widget_display' ] ) && ! is_tax( array( 'download_category', 'download_tag' ) ) )

			/** Downloads: global (EDD post type) */
			|| ( ( 'edd_global' == $instance[ 'widget_display' ] ) && ! ( ddw_eddsw_download_cpt() == get_post_type() ) )

			/** Posts/ Pages stuff */
			|| ( ( 'single_posts' == $instance[ 'widget_display' ] ) && ! is_singular( 'post' ) )
			|| ( ( 'single_pages' == $instance[ 'widget_display' ] ) && ! is_singular( 'page' ) )
			|| ( ( 'single_posts_pages' == $instance[ 'widget_display' ] ) && ! is_singular( array( 'post', 'page' ) ) )
		) {

			return;

		}  // end-if widget display checks

		/** Extract the widget arguments */
		extract( $args );

		/** Set up the arguments */
		$args = array(
			'intro_text' => $instance[ 'intro_text' ],
			'outro_text' => $instance[ 'outro_text' ]
		);

		$instance = wp_parse_args( (array) $instance, array(
			'title'            => '',
			'label_text'       => '',
			'placeholder_text' => '',
			'button_text'      => ''
		) );

		/** Typical WordPress Widget title filter */
		$title = apply_filters( 'widget_title', $instance[ 'title' ], $instance, $this->id_base );

		/** EDDSW Widget title filter */
		$title = apply_filters( 'eddsw_filter_search_widget_title', $instance[ 'title' ], $instance, $this->id_base );

		/** Display the before widget HTML */
		echo $before_widget;

		/** Display the widget title */
		if ( $instance[ 'title' ] ) {

			echo $before_title . $title . $after_title;

		}  // end-if title

		/** Action hook 'eddsw_before_search_widget' */
		do_action( 'eddsw_before_search_widget' );

		/** Display widget intro text if it exists */
		if ( ! empty( $instance[ 'intro_text' ] ) ) {

			echo '<div class="textwidget"><p class="'. $this->id . '-intro-text eddsw-intro-text">' . $instance[ 'intro_text' ] . '</p></div>';

		}  // end-if optional intro

		/** Set filters for various strings */
		$eddsw_label_string = ( ! empty( $instance[ 'label_text' ] ) ) ? apply_filters( 'eddsw_filter_label_string', $instance[ 'label_text' ] ) : FALSE;
		$eddsw_placeholder_string = apply_filters( 'eddsw_filter_placeholder_string', $instance[ 'placeholder_text' ] );
		$eddsw_search_string = apply_filters( 'eddsw_filter_search_string', $instance[ 'button_text' ] );

		/** Construct the search form */
		$form = '<div id="eddsw-form-wrapper"><form role="search" method="get" id="searchform" class="searchform eddsw-search-form" action="' . home_url() . '">';
		$form .= '<div class="eddsw-form-container">';
			if ( EDDSW_SEARCH_LABEL_DISPLAY && $eddsw_label_string ) {
				$form .= '<label class="screen-reader-text eddsw-label" for="s">' . esc_attr__( $eddsw_label_string ) . '</label>';
				$form .= '<br />';
			}
			$form .= '<input type="hidden" name="post_type" value="' . ddw_eddsw_download_cpt() . '" />';
			$form .= '<input type="text" value="' . get_search_query() . '" name="s" id="s" class="s eddsw-search-field" placeholder="' . esc_attr__( $eddsw_placeholder_string ) . '" />';
			$form .= '<input type="submit" id="searchsubmit" class="searchsubmit eddsw-search-submit" value="' . esc_attr__( $eddsw_search_string ) . '" />';

		$form .= '</div>';
		$form .= '</form></div>';

		/** Apply filter to allow for additional fields */
		echo apply_filters( 'eddsw_filter_search_form', $form, $instance, $this->id_base );

		/** Display widget outro text if it exists */
		if ( ! empty( $instance[ 'outro_text' ] ) ) {

			echo '<div class="textwidget"><p class="'. $this->id . '-outro_text eddsw-outro-text">' . $instance[ 'outro_text' ] . '</p></div>';

		}  // end-if optional outro

		/** Action hook 'eddsw_after_search_widget' */
		do_action( 'eddsw_after_search_widget' );

		/** Output the closing widget wrapper */
		echo $after_widget;

	}  // end of method widget


	/**
	 * Updates the widget control options for the particular instance of the widget.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $new_instance New settings for this instance as input by the user via form()
	 * @param  array $old_instance Old settings for this instance
	 *
	 * @return array Settings to save or bool false to cancel saving
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		/** Set the instance to the new instance. */
		$instance = $new_instance;

		/** Strip tags from elements that don't need them */
		$instance[ 'title' ]            = strip_tags( stripslashes( $new_instance[ 'title' ] ) );
		$instance[ 'intro_text' ]       = $new_instance[ 'intro_text' ];
		$instance[ 'outro_text' ]       = $new_instance[ 'outro_text' ];
		$instance[ 'label_text' ]       = strip_tags( stripslashes( $new_instance[ 'label_text' ] ) );
		$instance[ 'placeholder_text' ] = strip_tags( stripslashes( $new_instance[ 'placeholder_text' ] ) );
		$instance[ 'button_text' ]      = strip_tags( stripslashes( $new_instance[ 'button_text' ] ) );
		$instance[ 'widget_display' ]   = strip_tags( $new_instance[ 'widget_display' ] );

		return $instance;

	}  // end of method update


	/**
	 * Displays the widget options in the Widgets admin screen.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Current settings
	 */
	public function form( $instance ) {

		/** Setup defaults parameters */
		$defaults = apply_filters( 'eddsw_filter_search_widget_defaults', array(
			'label_text'       => __( 'Search downloads for:', 'edd-search-widget' ),
			'placeholder_text' => __( 'Search downloads&#x2026;', 'edd-search-widget' ),
			'button_text'      => __( 'Search', 'edd-search-widget' ),
			'widget_display'   => 'global'
		) );

		/** Get the values from the instance */
		$instance = wp_parse_args( (array) $instance, $defaults );

		/** Get values from instance */
		$title = ( isset( $instance[ 'title' ] ) ) ? esc_attr( $instance[ 'title' ] ) : null;
		$intro_text = ( isset( $instance[ 'intro_text' ] ) ) ? esc_textarea( $instance[ 'intro_text' ] ) : null;
		$outro_text = ( isset( $instance[ 'outro_text' ] ) ) ? esc_textarea( $instance[ 'outro_text' ] ) : null;

		/** Begin form code */
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
			<?php _e( 'Title:', 'edd-search-widget' ); ?>
			</label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" />
	   	</p>

		<p>
			<label for="<?php /** Optional intro text */ echo $this->get_field_id( 'intro_text' ); ?>"><?php _e( 'Optional intro text:', 'edd-search-widget' ); ?>
				<small><?php echo sprintf( __( 'Add some additional %s info. NOTE: Just leave blank to not use at all.', 'edd-search-widget' ), __( 'Search', 'edd-search-widget' ) ); ?></small>
				<textarea name="<?php echo $this->get_field_name( 'intro_text' ); ?>" id="<?php echo $this->get_field_id( 'intro_text' ); ?>" rows="2" class="widefat"><?php echo $intro_text; ?></textarea>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'label_text' ); ?>">
			<?php _e( 'Label string before search input field:', 'edd-search-widget' ); ?>
			<input type="text" id="<?php echo $this->get_field_id( 'label_text' ); ?>" name="<?php echo $this->get_field_name( 'label_text' ); ?>" value="<?php echo esc_attr( $instance[ 'label_text' ] ); ?>" class="widefat" />
				<small><?php _e( 'NOTE: Leave empty to not use/ display this string!', 'edd-search-widget' ); ?></small>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'placeholder_text' ); ?>">
			<?php _e( 'Placeholder string for search input field:', 'edd-search-widget' ); ?>
			<input type="text" id="<?php echo $this->get_field_id( 'placeholder_text' ); ?>" name="<?php echo $this->get_field_name( 'placeholder_text' ); ?>" value="<?php echo esc_attr( $instance[ 'placeholder_text' ] ); ?>" class="widefat" />
				<small><?php _e( 'NOTE: Leave empty to not use/ display this string!', 'edd-search-widget' ); ?></small>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'button_text' ); ?>">
			<?php _e( 'Search button string:', 'edd-search-widget' ); ?>
			<input type="text" id="<?php echo $this->get_field_id( 'button_text' ); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" value="<?php echo esc_attr( $instance[ 'button_text' ] ); ?>" class="widefat" />
				<small><?php _e( 'NOTE: Displaying may depend on your theme settings/ styles.', 'edd-search-widget' ); ?></small>
			</label>
		</p>

		<p>
    		<label for="<?php echo $this->get_field_id( 'widget_display' ); ?>">
				<?php _e( 'Where to display this widget?', 'edd-search-widget' ); ?>:
				<select id="<?php echo $this->get_field_id( 'widget_display' ); ?>" name="<?php echo $this->get_field_name( 'widget_display' ); ?>">        
					<?php
						printf( '<option value="global" %s>%s</option>', selected( 'global', $instance[ 'widget_display' ], 0 ), __( 'Global (default)', 'edd-search-widget' ) );
						printf( '<option value="single_downloads" %s>%s</option>', selected( 'single_downloads', $instance[ 'widget_display' ], 0 ), sprintf( __( 'Single %s', 'edd-search-widget' ), __( 'Downloads', 'edd-search-widget' ) ) );
						printf( '<option value="downloads_archives" %s>%s</option>', selected( 'downloads_archives', $instance[ 'widget_display' ], 0 ), __( 'Downloads Archives', 'edd-search-widget' ) );
						printf( '<option value="downloads_tax" %s>%s</option>', selected( 'downloads_tax', $instance[ 'widget_display' ], 0 ), __( 'Downloads Taxonomies', 'edd-search-widget' ) );
						printf( '<option value="edd_global" %s>%s</option>', selected( 'edd_global', $instance[ 'widget_display' ], 0 ), __( 'All Downloads (EDD) Instances', 'edd-search-widget' ) );
						printf( '<option value="single_posts" %s>%s</option>', selected( 'single_posts', $instance[ 'widget_display' ], 0 ), sprintf( __( 'Single %s', 'edd-search-widget' ), __( 'Posts', 'edd-search-widget' ) ) );
						printf( '<option value="single_pages" %s>%s</option>', selected( 'single_pages', $instance[ 'widget_display' ], 0 ), sprintf( __( 'Single %s', 'edd-search-widget' ), __( 'Pages', 'edd-search-widget' ) ) );
						printf( '<option value="single_posts_pages" %s>%s</option>', selected( 'single_posts_pages', $instance[ 'widget_display' ], 0 ), __( 'Both, Single Posts & Pages', 'edd-search-widget' ) );
					?>
				</select>
        	</label>
		</p>

		<p>
			<label for="<?php /** Optional outro text */ echo $this->get_field_id( 'outro_text' ); ?>"><?php _e( 'Optional outro text:', 'edd-search-widget' ); ?>
				<small><?php echo sprintf( __( 'Add some additional %s info. NOTE: Just leave blank to not use at all.', 'edd-search-widget' ), __( 'Search', 'edd-search-widget' ) ); ?></small>
				<textarea name="<?php echo $this->get_field_name( 'outro_text' ); ?>" id="<?php echo $this->get_field_id( 'outro_text' ); ?>" rows="2" class="widefat"><?php echo $outro_text; ?></textarea>
			</label>
		</p>

		<?php
		/** ^End form code */

	}  // end of method form

}  // end of main class EasyDigitalDownloads_Widget_Download_Search