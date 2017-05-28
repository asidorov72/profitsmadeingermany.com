<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\model\Labels;
use com\cminds\videolesson\lib\VimeoAPI;
use com\cminds\videolesson\App;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\lib\WistiaApi;

class SettingsController extends Controller {
	
	const ACTION_CLEAR_CACHE = 'clear-cache';
	const ACTION_RESET_LABELS = 'cmvl-reset-labels';
	
	
	protected static $actions = array(
		'admin_menu' => array('priority' => 15),
		'admin_notices',
		'cmvl_display_supported_shortcodes',
		'shutdown',
	);
	protected static $filters = array(
		'cmvl-settings-category' => array('args' => 2, 'method' => 'settingsLabels'),
	);
	protected static $ajax = array(
		'cmvl_test_configuration',
	);
	
	static function admin_menu() {
		
		wp_enqueue_style('cmvl-backend');
		
		$title = 'Settings';
		add_submenu_page(App::MENU_SLUG, App::getPluginName() . ' ' . $title, $title, 'manage_options', static::getMenuSlug(), array(get_called_class(), 'render'));
	}
	
	
	static function getMenuSlug() {
		return App::MENU_SLUG . '-settings';
	}
	
	
	static function admin_notices() {
		if (!get_option('permalink_structure')) {
			printf('<div class="error"><p><strong>%s:</strong> to make the plugin works properly
				please enable the <a href="%s">Wordpress permalinks</a>.</p></div>', App::getPluginName(), admin_url('options-permalink.php'));
		}
	}
	
	
	static function loadAssets() {
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend');
	}
	
	
	static function render() {
		static::loadAssets();
		wp_enqueue_style('cmvl-frontend');
		wp_enqueue_style('cmvl-settings');
		echo static::loadView('backend/template', array(
			'title' => App::getPluginName() . ' Settings',
			'nav' => static::getBackendNav(),
			'content' => static::loadBackendView('help') . static::loadBackendView('licensing-box') . static::loadBackendView('settings', array(
				'clearCacheUrl' => static::createBackendUrl(static::getMenuSlug(), array('action' => static::ACTION_CLEAR_CACHE), static::ACTION_CLEAR_CACHE),
				'resetLabelsUrl' => static::createBackendUrl(static::getMenuSlug(), array('action' => static::ACTION_RESET_LABELS), static::ACTION_RESET_LABELS),
			)),
		));
	}
	
	
	static function settingsLabels($result, $category) {
		if ($category == 'labels') {
			$result = static::loadBackendView('labels');
		}
		return $result;
	}
	
	
	static function processRequest() {
		$fileName = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		if (is_admin() AND $fileName == 'admin.php' AND !empty($_GET['page']) AND $_GET['page'] == static::getMenuSlug()) {
			
			if (!empty($_POST)) {
				
				// CSRF protection
		        if ((empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], static::getMenuSlug()))) {
		        	// Do nothing
		        } else {
			        Settings::processPostRequest($_POST);
			        Labels::processPostRequest();
			        $response = array('status' => 'ok', 'msg' => 'Settings have been updated.');
			        wp_redirect(static::createBackendUrl(static::getMenuSlug(), $response));
			        exit;
		        }
	            
			}
			else if (!empty($_GET['action']) AND !empty($_GET['nonce']) AND wp_verify_nonce($_GET['nonce'], $_GET['action'])) switch ($_GET['action']) {
				case static::ACTION_CLEAR_CACHE:
					flush_rewrite_rules(false);
					flush_rewrite_rules(true);
					VimeoAPI::clearCache();
// 					WistiaApi::clearCache();
					wp_redirect(static::createBackendUrl(static::getMenuSlug(), array('status' => 'ok', 'msg' => 'Cache has been removed.')));
					exit;
					break;
				case static::ACTION_RESET_LABELS:
					Labels::resetAll();
					wp_redirect(static::createBackendUrl(static::getMenuSlug(), array('status' => 'ok', 'msg' => 'Labels has been reset.')));
					exit;
					break;
			}
	        
		}
	}
	
	
	static function getSectionExperts() {
		return static::loadBackendView('experts');
	}
	
	
	static function cmvl_test_configuration() {
		if (is_user_logged_in() AND current_user_can('manage_options')) {
			
			$api = filter_input(INPUT_POST, 'api');
			switch ($api) {
				case 'vimeo':
					static::testVimeoConfiguration();
					break;
				case 'wistia':
					static::testWistiaConfiguration();
					break;
			}
			
		}
	}
	
	
	protected static function testVimeoConfiguration() {
		$errorMsg = null;
		if (!Settings::getOption(Settings::OPTION_VIMEO_CLIENT_ID)) {
			$errorMsg = 'You must provide the Vimeo Client ID.';
		}
		if (!Settings::getOption(Settings::OPTION_VIMEO_CLIENT_SECRET)) {
			$errorMsg = 'You must provide the Vimeo Client Secret.';
		}
		if (!Settings::getOption(Settings::OPTION_VIMEO_ACCESS_TOKEN)) {
			$errorMsg = 'You must provide the Vimeo Access Token.';
		}
		if (!empty($errorMsg)) {
			echo static::loadBackendView('test-configuration-error', compact('errorMsg'));
			exit;
		}
			
		$vimeo = VimeoAPI::getInstance();
		$videos = $vimeo->request('/me/videos', array('per_page' => 50, 'filter' => 'moderated'), $method = 'GET', $json_body = true, $cacheExpiration = 0);
		echo static::loadBackendView('test-configuration-vimeo', compact('videos'));
		exit;
	}
	
	
	protected static function testWistiaConfiguration() {
		$errorMsg = null;
		if (!Settings::getOption(Settings::OPTION_API_WISTIA_ACCESS_TOKEN)) {
			$errorMsg = 'You must provide the Wistia API access token.';
		}
		if (!empty($errorMsg)) {
			echo static::loadBackendView('test-configuration-error', compact('errorMsg'));
			exit;
		}
		
		try {
			$wistia = WistiaApi::getInstance();
			$videos = $wistia->mediaList();
// 			var_dump($videos);exit;
			echo static::loadBackendView('test-configuration-wistia', compact('videos'));
		} catch (\Exception $e) {
			$errorMsg = $e->getMessage();
			echo static::loadBackendView('test-configuration-error', compact('errorMsg'));
		}
		exit;
	}
	
	
	static function cmvl_display_supported_shortcodes() {
		echo static::loadBackendView('shortcodes');
	}
	
	
	
	static function shutdown() {
		if (filter_input(INPUT_GET, 'cmvimeodebug')) {
			if (!empty(Vimeo::$log) AND is_array(Vimeo::$log)) {
// 				var_dump(Vimeo::$log);return;
				echo '<table class="cmvl-debug"><caption>CM Video Lessons - Vimeo requests</caption>
					<thead><tr><th>No.</th><th>Type</th><th>URL</th><th>Params</th><th>Method</th><th>JSON Body</th></tr></thead>';
				foreach (Vimeo::$log as $i => $row) {
					echo '<tr><td>'. ($i+1) .'</td>';
					foreach ($row as $key => $value) {
						echo '<td>';
						if (is_string($value) OR is_numeric($value)) echo $value;
						else var_dump($value);
						echo '</td>';
					}
					echo '</tr>';
				}
				echo '</table><style>.cmvl-debug {margin: 2em auto;}
					.cmvl-debug tr:nth-child(even), .cmvl-debug thead {background: #f0f0f0;}
					.cmvl-debug td {vertical-align: top; border: solid 1px #f0f0f0; padding: 5px 10px;}</style>';
			}
		}
	}
	
	
}

