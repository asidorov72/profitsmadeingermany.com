<?php
/**
* Plugin Name: EDD Recent downloads
* Plugin URI: 
* Description: Add widget Recent downloads to Easy Digital Downloads.
* Version: 1.0
* Text Domain: edd-recent-downloads
* Author: Rustam Galiulin
* Author URI: https://profiles.wordpress.org/galiulinr
*
* This program is free software; you can redistribute it and/or modify it under the terms of the GNU
* General Public License version 2, as published by the Free Software Foundation. You may NOT assume
* that you can use any other version of the GPL.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
* even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*
* @package EDD_Recent_downloads
* @version 1.0
* @author Rustam Galiulin
* @copyright Copyright (c) 2015, Rustam Galiulin
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class EDD_Recent_downloads_widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'edd-recent-downloads_widget', 'description' => 'Shows recent custom post types' );
		parent::__construct('edd-recent-downloads', 'Recent downloads', $widget_ops);
		$this->alt_option_name = 'edd-recent-downloads_widget';
	}

	function widget($args, $instance) {
           
			extract( $args );
		
			$title = apply_filters( 'widget_title', empty($instance['title']) ? 'Recent Posts' : $instance['title'], $instance, $this->id_base);	
			
			if ( ! $number = absint( $instance['number'] ) ) $number = 5;

		$my_args=array(
						   
				'showposts' => $number,
				
				'post_type' => 'download'
				
				);
			
			$edd_recent_posts = null;
			
			$edd_recent_posts = new WP_Query($my_args);
			
			echo $before_widget;
			
			// Widget title
			
			echo $before_title;
			
			echo $instance["title"];
			
			echo $after_title;
		
		echo "<ul>\n";
			
		while ( $edd_recent_posts->have_posts() )

		{

			$edd_recent_posts->the_post();

		?>

			<li class="edd-recent-post-item">
				<a  href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent link to <?php the_title_attribute(); ?>" class="post-title"><?php the_title(); ?></a>
			</li>

		<?php

		}

		 wp_reset_query();

		echo "</ul>\n";

		echo $after_widget;
			
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		return $instance;
	}

	public function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
		<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>

<?php
	}
}
// register EDD Recent downloads Widget
add_action( 'widgets_init', create_function( '', 'return register_widget("EDD_Recent_downloads_widget");' ) );
?>