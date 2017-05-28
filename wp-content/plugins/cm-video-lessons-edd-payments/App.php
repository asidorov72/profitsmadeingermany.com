<?php

namespace com\cminds\videolesson\addon\eddpay;

use com\cminds\videolesson\addon\eddpay\core\Core;

use com\cminds\videolesson\addon\eddpay\controller\SettingsController;

use com\cminds\videolesson\addon\eddpay\model\Settings;

require_once dirname(__FILE__) . '/core/Core.php';

class App extends Core {
	
	const VERSION = '2.1.2';
	const PREFIX = 'cmeddpay';
	const SLUG = 'cm-edd-pay';
	const PLUGIN_NAME		 = 'CM Video Lessons EDD Payments';
	const PLUGIN_WEBSITE	 = 'https://www.cminds.com/';
	const PARENT_LICENSING_SLUG = 'c-m-video-lesson-manager-pro';
	const PARENT_PREFIX = 'CMVL';
	const PARENT_MENU = 'cmvl';
	const PARENT_SETTINGS_SLUG = 'cmvl-settings';
	
	static function bootstrap($pluginFile) {
		parent::bootstrap($pluginFile);
	}
	
	
	static protected function getClassToBootstrap() {
		$classToBootstrap = array_merge(
			parent::getClassToBootstrap(),
			static::getClassNames('controller'),
			static::getClassNames('model')
		);
		if (static::isLicenseOk()) {
			$classToBootstrap = array_merge($classToBootstrap, static::getClassNames('shortcode'), static::getClassNames('widget'), static::getClassNames('metabox'));
		}
		return $classToBootstrap;
	}
	
	
	static function init() {
		parent::init();
		
		wp_register_script('cmeddpay-utils', static::url('asset/js/utils.js'), array('jquery'), static::VERSION, true);
		wp_register_script('cmeddpay-backend', static::url('asset/js/backend.js'), array('jquery'), static::VERSION, true);
		
		wp_register_style('cmeddpay-settings', static::url('asset/css/settings.css'), null, static::VERSION);
// 		wp_register_style('cmeddpay-backend', static::url('asset/css/backend.css'), null, static::VERSION);
		wp_register_style('cmeddpay-frontend', static::url('asset/css/frontend.css'), array('dashicons'), static::VERSION);
		
		wp_register_script('cmeddpay-frontend', static::url('asset/js/frontend.js'), array('jquery'), static::VERSION, true);
		
		wp_localize_script('cmeddpay-frontend', 'CMEDDPAY_Settings', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
		));
		
	}
	

	static function admin_menu() {
		parent::admin_menu();
		$name	 = static::getPluginName( true );
// 		$page	 = add_menu_page( $name, $name, 'manage_options', static::PREFIX,
// 			array( App::namespaced('controller\PluginController'), 'displayTheInstructions' ), 'dashicons-admin-users', 5679 );
	}
	
	
	static function getPluginName($full = false) {
		return static::PLUGIN_NAME;
	}
	
	
	static function getLicenseAdditionalNames() {
		return array( static::getPluginName( false ), static::getPluginName( true ) );
	}
	
	static function isAvailable() {
// 		return true;
		return (App::isLicenseOk() AND function_exists('EDD') AND class_exists('\\EDD_Download'));
	}
	
	
}
