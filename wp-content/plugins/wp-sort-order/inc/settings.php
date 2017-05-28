<?php
global $wpso_data, $wpso_pro, $wpso_premium_link, $premium_click;

$wpso_options = get_option( 'wpso_options' );
$wpso_objects = isset( $wpso_options['objects'] ) ? $wpso_options['objects'] : array();
$wpso_tags = isset( $wpso_options['tags'] ) ? $wpso_options['tags'] : array();

?>

<div class="wrap wpso">

<?php screen_icon( 'plugins' ); ?>

<h2><?php _e( 'WP Sort Order', 'wp-sort-order' ); ?> <?php echo '('.$wpso_data['Version'].($wpso_pro?') Pro':')'); ?> - Settings <?php echo ($wpso_pro?'':'<a href="'.$wpso_premium_link.'" target="_blank" class="premium">Go Premium</a>'); ?></h2>

<?php if ( isset($_GET['msg'] )) : ?>
<div id="message" class="updated below-h2">
	<?php if ( $_GET['msg'] == 'update' ) : ?>
		<p><?php _e( 'Settings saved.' ); ?></p>
	<?php endif; ?>
</div>
<?php endif; ?>

<form method="post">

<?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( 'nonce_wpso' ); ?>

<div class="more_features">
<ul>
<?php if(!$wpso_pro): ?>
<li><label>User categories required?</label><?php echo $premium_click; ?></li>
<?php endif; ?>
<li>
<label>A shortcode to list users under taxonomy, terms and children. <span>[</span>WPSO_USERS <span>slug="</span>taxonomy or term slug<span>"</span> <span>id="</span>taxonomy or term id<span>"]</span><?php echo $premium_click; ?></label>
</li>
</ul>
</div>
<div id="wpso_select_objects">
<label class="clickable"><input type="checkbox" id="wpso_allcheck_objects" class="hide" /> <?php _e( 'Check/Uncheck', 'wp-sort-order' ) ?></label>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><h4><?php _e( 'Post Types', 'wp-sort-order' ) ?></h4></th>
			<td class="options1">
            <ul>
			<?php
				$post_types = get_post_types( array (
					'show_ui' => true,
					'show_in_menu' => true,
				), 'objects' );
				
				foreach ( $post_types  as $post_type ) {
					if ( $post_type->name == 'attachment' ) continue;
					?>
                    <li>
					<label><input type="checkbox" name="objects[]" value="<?php echo $post_type->name; ?>" <?php if ( isset( $wpso_objects ) && is_array( $wpso_objects ) ) { if ( in_array( $post_type->name, $wpso_objects ) ) { echo 'checked="checked"'; } } ?>>&nbsp;<?php echo $post_type->label; ?></label>
                    </li>
					<?php
				}
			?>
            </ul>
			</td>
		</tr>
	</tbody>
</table>

</div>



<div id="wpso_select_tags">
<label class="clickable"><input class="hide" type="checkbox" id="wpso_allcheck_tags"> <?php _e( 'Check/Uncheck', 'wp-sort-order' ) ?></label>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><h4><?php _e( 'Taxonomies', 'wp-sort-order' ) ?></h4></th>
			<td class="options2">
            <ul>
			<?php
				$taxonomies = get_taxonomies( array(
					'show_ui' => true,
				), 'objects' );
				
				foreach( $taxonomies as $taxonomy ) {
					if ( $taxonomy->name == 'post_format' ) continue;
					?>
					<li><label><input type="checkbox" name="tags[]" value="<?php echo $taxonomy->name; ?>" <?php if ( isset( $wpso_tags ) && is_array( $wpso_tags ) ) { if ( in_array( $taxonomy->name, $wpso_tags ) ) { echo 'checked="checked"'; } } ?>>&nbsp;<?php echo $taxonomy->label ?></label></li>
					<?php
				}
				
			?>
            	<?php if(!$wpso_pro): ?>
                <li><label><input disabled="disabled" type="checkbox">&nbsp;User Categories</label>&nbsp;<?php echo $premium_click; ?></li>
                <?php endif; ?>
            </ul>
			</td>
		</tr>
        <tr>
        <th></th>
        <td><p class="submit">
	<input type="submit" class="button-primary" name="wpso_submit" value="<?php _e( 'Save Changes' ); ?>">
</p></td>
        </tr>
	</tbody>
</table>

</div>




	
</form>

</div>

<script type="text/javascript" language="javascript">
(function($){
	
	$("#wpso_allcheck_objects").on('click', function(){
		var items = $("#wpso_select_objects input");
		if ( $(this).is(':checked') ) $(items).prop('checked', true);
		else $(items).prop('checked', false);	
	});

	$("#wpso_allcheck_tags").on('click', function(){
		var items = $("#wpso_select_tags input");
		if ( $(this).is(':checked') ) $(items).prop('checked', true);
		else $(items).prop('checked', false);	
	});
	
})(jQuery);
</script>
<style type="text/css">
.update-nag, #wpfooter{
	display:none;
}
</style>