<?php

namespace com\cminds\videolesson;

use com\cminds\videolesson\controller\ChannelController;

use com\cminds\videolesson\model\Labels;

use com\cminds\videolesson\core\Core;

use com\cminds\videolesson\controller\SettingsController;

use com\cminds\videolesson\model\Settings;

require_once dirname(__FILE__) . '/core/Core.php';

class App extends Core {
	
	const PREFIX = 'CMVL';
	const MENU_SLUG = 'cmvl';
	const SLUG = 'cm-video-lessons';
	const PLUGIN_NAME = 'CM Video Lesson Manager';
	const PLUGIN_WEBSITE = 'https://plugins.cminds.com/cm-video-lessons-manager-plugin-for-wordpress/';
	const LICENSING_SLUG = 'c-m-video-lesson-manager-pro';
	
	
	static function bootstrap($pluginFile) {
		parent::bootstrap($pluginFile);
	}
	
	
	static protected function getClassToBootstrap() {
		$classToBootstrap = array_merge(
			parent::getClassToBootstrap(),
			static::getClassNames('controller'),
			static::getClassNames('model'),
			static::getClassNames('metabox')
		);
		if (static::isLicenseOk()) {
			$classToBootstrap = array_merge($classToBootstrap, static::getClassNames('shortcode'), static::getClassNames('widget'));
		}
		return $classToBootstrap;
	}
	
	
	static function init() {
		parent::init();
		
		wp_register_script('cmvl-utils', App::url('asset/js/utils.js'), array('jquery'), App::getVersion(), true);
		wp_register_script('cmvl-backend', App::url('asset/js/backend.js'), array('jquery', 'jquery-ui-sortable', 'cmvl-utils', 'jquery-ui-tooltip'), App::getVersion(), true);
		wp_register_script('cmvl-backend-import-videos', App::url('asset/js/backend-import-videos.js'), array('jquery', 'cmvl-backend'), App::getVersion(), true);
		wp_register_script('cmvl-autocomplete', App::url('asset/js/autocomplete.js'), array('jquery'), App::getVersion(), true);
		
		wp_register_script('cmvl-vimeo-player', App::url('asset/js/vimeo/player.js'), null, App::getVersion(), true);
		wp_register_script('cmvl-stats', App::url('asset/js/stats.js'), array('jquery', 'cmvl-vimeo-player'), App::getVersion(), true);
		wp_register_script('cmvl-froogaloop', App::url('asset/js/froogaloop/froogaloop-min.js'), null, App::getVersion(), true);
		wp_register_script('cmvl-logout-heartbeat', App::url('asset/js/logout-heartbeat.js'), array('jquery', 'heartbeat'), App::getVersion(), true);
		wp_register_script('cmvl-paybox', App::url('asset/js/paybox.js'), array('jquery', 'cmvl-utils'), App::getVersion(), true);
		wp_register_script('cmvl-playlist', App::url('asset/js/playlist.js'), array('jquery', 'cmvl-utils', 'cmvl-paybox', 'cmvl-froogaloop', 'cmvl-stats'), App::getVersion(), true);
		wp_localize_script('cmvl-playlist', 'CMVLSettings', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'ajaxNonce' => wp_create_nonce(ChannelController::NONCE_AJAX_CHANNEL),
		));
		
		wp_localize_script('cmvl-backend', 'CMVLBackend', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'ajaxNonce' => wp_create_nonce(ChannelController::NONCE_AJAX_BACKEND),
		));
		
		wp_register_style('cmvl-settings', App::url('asset/css/settings.css'), null, App::getVersion());
		wp_register_style('cmvl-backend', App::url('asset/css/backend.css'), null, App::getVersion());
		wp_register_style('cmvl-frontend', App::url('asset/css/frontend.css'), null, App::getVersion());
		
	}
	

	static function admin_menu() {
		parent::admin_menu();
		$name = App::getPluginName(true);
		$page = add_menu_page($name, $name, 'manage_options', App::MENU_SLUG, create_function('$q', 'return;'), 'dashicons-video-alt2');
	}
	
	
}
