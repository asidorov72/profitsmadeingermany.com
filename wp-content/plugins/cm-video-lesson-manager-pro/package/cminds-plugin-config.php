<?php

use com\cminds\videolesson\App;

$cminds_plugin_config = array(
	'plugin-is-pro'					 => App::isPro(),
	'plugin-has-addons'      => TRUE,
	'plugin-addons'        => array(
		array(
			'title' => 'Video Lessons Direct Payments',
			'description' => 'Allow users to pay for viewing video lessons using Easy digital downloads cart.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/video-lessons-edd-payments-add-on-for-wordpress-by-creativeminds/',
			'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=86324&edd_options[price_id]=1'
		),
		array(
			'title' => 'CM Video Lessons Certificates Addon',
			'description' => 'Create PDF certificates for lessons and courses and send it by email to the user that finished a course.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/purchase-cm-video-lessons-manager-plugin-for-wordpress/',
			'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=154123&edd_options[price_id]=1'
		),
		array(
			'title' => 'CM MicroPayment Platform',
			'description' => 'Add your own “virtual currency“ and allow to charge for posting and answering questions.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/micropayments/',
			'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=11388&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=0'
		),
	),
	'plugin-version'				 => App::getVersion(),
	'plugin-abbrev'					 => App::PREFIX,
	'plugin-parent-abbrev'			 => '',
	'plugin-affiliate'				 => '',
	'plugin-redirect-after-install'	 => admin_url( 'admin.php?page=cmvl-settings' ),
	'plugin-settings-url'		 	 => admin_url( 'admin.php?page=cmvl-settings' ),
	'plugin-file'					 => App::getPluginFile(),
	'plugin-dir-path'				 => plugin_dir_path( App::getPluginFile() ),
	'plugin-dir-url'				 => plugin_dir_url( App::getPluginFile() ),
	'plugin-basename'				 => plugin_basename( App::getPluginFile() ),
	'plugin-icon'					 => '',
	'plugin-name'					 => App::getPluginName( true ),
	'plugin-license-name'			 => App::getPluginName(),
	'plugin-slug'					 => App::LICENSING_SLUG,
	'plugin-short-slug'				 => App::PREFIX,
	'plugin-parent-short-slug'		 => '',
	'plugin-menu-item'				 => App::MENU_SLUG,
	'plugin-textdomain'				 => '',
	'plugin-show-shortcodes'	 => TRUE,
	'plugin-shortcodes'			 => '<p>You can use the following available shortcodes.</p>',
	'plugin-shortcodes-action'	 => 'cmvl_display_supported_shortcodes',
	'plugin-userguide-key'			 => '354-cm-video-lessons-manager-cmvlm',
	'plugin-store-url'				 => 'https://www.cminds.com/wordpress-plugins-library/purchase-cm-video-lessons-manager-plugin-for-wordpress/',
	'plugin-support-url'			 => 'https://wordpress.org/support/plugin/cm-video-lesson-manager',
	'plugin-review-url'				 => 'http://wordpress.org/support/view/plugin-reviews/cm-video-lesson-manager',
	'plugin-changelog-url'			 => 'https://www.cminds.com/wordpress-plugins-library/purchase-cm-video-lessons-manager-plugin-for-wordpress/#changelog',
	'plugin-licensing-aliases'		 => array( App::getPluginName( false ), App::getPluginName( true ) ),
	'plugin-show-guide'              => TRUE,
	'plugin-guide-text'              => '    <div style="display:block">
	<ol>
	<li>Go to the  <strong>"Plugin Settings"</strong></li>
	<li>Visit <strong>"Vimeo Develop Dashboard"</strong> to generate a new App</li>
	<li>Copy <strong>App keys and access tokens</strong> to plugin settings</li>
	<li>From the plugin admin menu Select <strong>"Lessons" </strong>.</li>
	<li>Select <strong>Add Lesson</strong> and define your first lesson. Make sure to select the corresponding Vimeo album</li>
	<li>View lesson or add a shortcode to embed in a post or page</li>
	</ol>
	</div>',
	'plugin-guide-video-height'      => 240,
	'plugin-guide-videos'            => array(
		array( 'title' => 'Installation tutorial', 'video_id' => '161022219' ),
	),
	'plugin-compare-table'			 => '<div class="pricing-table" id="pricing-table">
	<ul>
	<li class="heading">Current Edition</li>
	<li class="price">$0.00</li>
	<li class="noaction"><span>Free Download</span></li>
	<li>Read Vimeo private videos, albums and lessons</li>
	<li>Create multiple lessons</li>
	<li>Shortcode to include video List</li>
	<li>X</li>
	<li>X</li>
	<li>X</li>
	<li>X</li>
	<li>X</li>
	<li>X</li>
	<li>X</li>
	<li>X</li>
	<li>X</li>
	<li>X</li>
	<li>X</li>
	<li>X</li>
	<li class="price">$0.00</li>
	<li class="noaction"><span>Free Download</span></li>
	</ul>

	<ul>
	<li class="heading">Pro</li>
	<li class="price">$29.00</li>
                    <li class="action"><a href="https://www.cminds.com/wordpress-plugins-library/purchase-cm-video-lessons-manager-plugin-for-wordpress/" style="background-color:darkblue;" target="_blank">More Info</a> &nbsp;&nbsp;<a href="https://www.cminds.com/?edd_action=add_to_cart&download_id=31919&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1" target="_blank">Buy Now</a></li>
	<li>Read Vimeo private videos</li>
	<li>Create multiple lessons</li>
	<li>Shortcode to Include video list</li>
	<li>Bookmarks support</li>
	<li>History and statistics</li>
	<li>User notes support</li>
	<li>Student dashboard</li>
	<li>Search within videos and notes</li>
	<li>Access control</li>
	<li>Courses</li>
	<li>Videos view templates</li>
	<li>Edit plugin labels
	<li>Notifications support</li>
	<li>X</li>
	<li class="price">$29.00</li>
                    <li class="action"><a href="https://www.cminds.com/wordpress-plugins-library/purchase-cm-video-lessons-manager-plugin-for-wordpress/" style="background-color:darkblue;" target="_blank">More Info</a> &nbsp;&nbsp;<a href="https://www.cminds.com/?edd_action=add_to_cart&download_id=31919&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1" target="_blank">Buy Now</a></li>
	</ul>
	<ul>
	<li class="heading">Pro with Payments</li>
	<li class="price">$59.00</li>
                   <li class="action"><a href="https://www.cminds.com/wordpress-plugins-library/purchase-cm-video-lessons-manager-plugin-for-wordpress/" style="background-color:darkblue;" target="_blank">More Info</a> &nbsp;&nbsp;<a href="https://www.cminds.com/?edd_action=add_to_cart&download_id=87413&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1" target="_blank">Buy Now</a></li>
	<li>Read Vimeo private videos</li>
	<li>Create multiple lessons</li>
	<li>Shortcode to Include video list</li>
	<li>Bookmarks support</li>
	<li>History and statistics</li>
	<li>User notes support</li>
	<li>Student dashboard</li>
	<li>Search within videos and notes</li>
	<li>Access control</li>
	<li>Courses</li>
	<li>Videos view templates</li>
	<li>Edit plugin labels
	<li>Notifications support</li>
	<li>Payment Support</li>
	<li class="price">$59.00</li>
                     <li class="action"><a href="https://www.cminds.com/wordpress-plugins-library/purchase-cm-video-lessons-manager-plugin-for-wordpress/" style="background-color:darkblue;" target="_blank">More Info</a> &nbsp;&nbsp;<a href="https://www.cminds.com/?edd_action=add_to_cart&download_id=87413&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1" target="_blank">Buy Now</a></li>
	</ul>


	</div>',
);
