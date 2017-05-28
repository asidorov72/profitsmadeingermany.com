<?php

use com\cminds\videolesson\addon\eddpay\App;

$cminds_plugin_config = array(
	'plugin-is-pro'				 => App::isPro(),
	'plugin-is-addon'			 => TRUE,
	'plugin-version'			 => App::VERSION,
	'plugin-abbrev'				 => App::PREFIX,
	'plugin-parent-abbrev'		 => App::PARENT_PREFIX,
	'plugin-settings-url'	 	 => admin_url( 'admin.php?page=' . App::PARENT_SETTINGS_SLUG ),
	'plugin-file'				 => App::getPluginFile(),
	'plugin-dir-path'			 => plugin_dir_path( App::getPluginFile() ),
	'plugin-dir-url'			 => plugin_dir_url( App::getPluginFile() ),
	'plugin-basename'			 => plugin_basename( App::getPluginFile() ),
	'plugin-icon'				 => '',
	'plugin-name'				 => App::PLUGIN_NAME,
	'plugin-license-name'		 => App::PLUGIN_NAME,
	'plugin-slug'				 => App::PREFIX,
	'plugin-short-slug'			 => App::PREFIX,
	'plugin-parent-short-slug'	 => App::PARENT_PREFIX,
	'plugin-menu-item'			 => App::PARENT_MENU,
	'plugin-textdomain'			 => '',
	'plugin-userguide-key'		 => '354-cm-video-lessons-manager-cmvlm',
	'plugin-store-url'			 => 'https://www.cminds.com/store/video-lessons-edd-payments-add-on-for-wordpress-by-creativeminds/',
	'plugin-support-url'		 => 'https://www.cminds.com/wordpress-plugin-customer-support-ticket/',
	'plugin-review-url'			 => 'https://wordpress.org/support/view/plugin-reviews/cm-video-lesson-manager',
	'plugin-changelog-url'		 => 'https://www.cminds.com/store/video-lessons-edd-payments-add-on-for-wordpress-by-creativeminds/#changelog',
	'plugin-licensing-aliases'	 => App::getLicenseAdditionalNames(),
);
