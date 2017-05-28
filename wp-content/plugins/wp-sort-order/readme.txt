=== WP Sort Order ===
Contributors: fahadmahmood
Tags: post order, taxonomy order, user order
Requires at least: 3.5.0


Tested up to: 4.7


Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Order terms (Users, Posts, Pages, Custom Post Types and Custom Taxonomies) using a Drag and Drop with jQuery ui Sortable.

== Description ==

Order terms (Users, Posts, Pages, Custom Post Types and Custom Taxonomies) using a Drag and Drop with jQuery ui Sortable.

Select sortable items from 'WP Sort Order' menu of Setting menu in WordPress.

In addition, You can re-override the parameters of 'orderby' and 'order', by using the 'WP_Query' or 'pre_get_posts' or 'query_posts()'.<br>
The 'get_posts()' is excluded.


== Installation ==

1. Upload 'wp-sort-order' folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Select sortable items from 'WP Sort Order' menu of Setting menu in WordPress.

== Screenshots ==
1. Installation & Activation
2. Settings Page


== Frequently Asked Questions ==

= How to re-override the parameters of 'orderby' and 'order' =

<strong>Sub query</strong>

By using the 'WP_Query', you can re-override the parameters.

* WP_Query

`
<?php $query = new WP_Query( array(
	'orderby' => 'date',
	'order' => 'DESC',
) ) ?>
`

<strong>Main query</strong>

By using the 'pre_get_posts' action hook or 'query_posts()', you can re-override the parameters.

* pre_get_posts

`
function my_filter( $query )
{
	if ( is_admin() || !$query->is_main_query() ) return;
	if ( is_home() ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
		return;
	}
}
add_action( 'pre_get_posts', 'my_filter' );
`

* query_posts()

`
<?php query_posts( array(
	'orderby' => 'rand'
) ); ?>
`

== Changelog ==
= 1.1.1 =
* A few improvements related to WordPress 4.6.
= 1.1.0 =
* A few improvements related to WordPress 4.5.0.

Initial Release

== Upgrade Notice ==
= 1.1.1 =
A few improvements related to WordPress 4.6.
= 1.1.0 =
A few improvements related to WordPress 4.5.0.
