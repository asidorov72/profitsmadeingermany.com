<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\App;
use com\cminds\videolesson\model\PostEDDPayment;
use com\cminds\videolesson\model\Micropayments;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Labels;
use com\cminds\videolesson\model\Channel;

class ProController extends Controller {

	protected static $filters = array(
		'cmvl_options_config' => array('priority' => 50),
		'login_redirect',
	);
	protected static $actions = array(
		array('name' => 'admin_menu', 'priority' => 16),
		array('name' => 'cmvl_labels_init', 'priority' => 10),
		'cmvl_load_assets_frontend',
		'cmvl_video_bottom' => array('args' => 1, 'method' => 'parseMarkupTags')
	);
	
	
	static function admin_menu() {
// 		add_submenu_page(App::MENU_SLUG, 'About ' . App::getPluginName(), 'About', 'manage_options', self::getMenuSlug('about'), array(get_called_class(), 'about'));
// 		add_submenu_page(App::MENU_SLUG, App::getPluginName() . ' User Guide', 'User Guide', 'manage_options', self::getMenuSlug('user-guide'),
// 			array(get_called_class(), 'userGuide'));
	}
	
	
	static function getMenuSlug($slug) {
		return App::MENU_SLUG . '-' . $slug;
	}
	

	static function about() {
		echo self::loadView('backend/template', array(
			'title' => 'About ' . App::getPluginName(),
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('about', array(
				'iframeURL' => SettingsController::PAGE_ABOUT_URL,
			)) . SettingsController::getSectionExperts(),
		));
	}
	
	
	static function userGuide() {
		echo self::loadView('backend/template', array(
			'title' => App::getPluginName() . ' User Guide',
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('about', array(
				'iframeURL' => SettingsController::PAGE_USER_GUIDE_URL,
			)) . SettingsController::getSectionExperts(),
		));
	}
	
	
	
	static function cmvl_labels_init() {
		Labels::loadLabelFile(App::path('asset/labels/pro.tsv'));
	}
	
	
	static function cmvl_options_config($config) {
		$config = array_merge($config, array(
			
			Settings::OPTION_VIDEO_STATS_DETAILED_LOG_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'category' => 'general',
				'subcategory' => 'stats',
				'default' => 0,
				'title' => 'Track date and time',
				'desc' => 'If enabled, each time user watched part of a video that event will be logged into the database with the date and time '
					. 'so admin can browse more detailed reports. If disabled then only aggregated data will be stored.',
			),
			Settings::OPTION_VIDEOS_LAYOUT => array(
				'type' => Settings::TYPE_RADIO,
				'category' => 'appearance',
				'subcategory' => 'appearance',
				'options' => Settings::getVideosLayoutOptions(),
				'default' => Settings::LAYOUT_TILES,
				'title' => 'Default videos layout',
			),
			Settings::OPTION_PLAYLIST_LAYOUT => array(
				'type' => Settings::TYPE_RADIO,
				'category' => 'appearance',
				'subcategory' => 'appearance',
				'options' => Settings::getPlaylistLayoutOptions(),
				'default' => Settings::PLAYLIST_VIDEOS_LIST_BOTTOM,
				'title' => 'Display videos list in playlist at',
				'desc' => 'Works only if using the playlist view. The vidoes list will be placed in the chosen place.',
			),
			Settings::OPTION_PLAYLIST_MAX_WIDTH => array(
				'type' => Settings::TYPE_INT,
				'category' => 'appearance',
				'subcategory' => 'appearance',
				'default' => 0,
				'title' => 'Playlist max width',
				'desc' => 'Set 0 to disable.',
			),
			Settings::OPTION_SHOW_VIDEO_DESCRIPTION => array(
				'type' => Settings::TYPE_BOOL,
				'category' => 'appearance',
				'subcategory' => 'appearance',
				'default' => 1,
				'title' => 'Show video description',
			),
			Settings::OPTION_SHOW_LESSON_DESCRIPTION => array(
				'type' => Settings::TYPE_BOOL,
				'category' => 'appearance',
				'subcategory' => 'appearance',
				'default' => 1,
				'title' => 'Show lesson description',
			),
			Settings::OPTION_SHOW_COURSE_DESCRIPTION => array(
				'type' => Settings::TYPE_BOOL,
				'category' => 'appearance',
				'subcategory' => 'appearance',
				'default' => 0,
				'title' => 'Show course description',
			),
			Settings::OPTION_SHOW_VIDEO_NOTE => array(
				'type' => Settings::TYPE_BOOL,
				'category' => 'appearance',
				'subcategory' => 'appearance',
				'default' => 1,
				'title' => 'Show video note text area',
			),
			Settings::OPTION_PAGE_TEMPLATE => array(
				'type' => Settings::TYPE_SELECT,
				'category' => 'appearance',
				'subcategory' => 'appearance',
				'default' => '',
				'options' => array('' => '-- default --') + Settings::getPageTemplatesOptions(),
				'title' => 'Page template',
				'desc' => 'Select page template for the generic lesson pages.',
			),
			Settings::OPTION_ACCESS_VIEW => array(
				'type' => Settings::TYPE_RADIO,
				'category' => 'general',
				'subcategory' => 'access',
				'options' => array(
					Settings::ACCESS_LOGGED_IN_USERS => 'Logged-in users',
					Settings::ACCESS_EVERYONE => 'Everyone (including guests)',
				),
				'default' => Settings::ACCESS_EVERYONE,
				'title' => 'Who can watch videos',
				'desc' => 'Choose the access restriction for watching videos.',
			),
			Settings::OPTION_DISABLE_CHANNEL_PAGE => array(
				'type' => Settings::TYPE_BOOL,
				'category' => 'general',
				'subcategory' => 'access',
				'default' => 0,
				'title' => 'Disable lesson page',
				'desc' => 'You can disable the lessons\' pages and then you\'ll be able to use only the playlist shortcodes.',
			),
			Settings::OPTION_UNLOCK_PRIVATE_VIDEOS => array(
				'type' => Settings::TYPE_CUSTOM,
				'category' => 'general',
				'subcategory' => 'access',
				'title' => 'Unlock private videos',
				'desc' => 'Use this button to add your domain name to the whitelist of each private video. '
					. '<strong>Warning: this can exceed your Vimeo API limits.</strong><br>'
					. 'You can do this manually in Vimeo - go to a specific video -> Settings -> Privacy -> Where can this video be embedded? -> Only on sites I choose.',
				'content' => '<a class="button cmvl-vimeo-unlock-private-videos">Unlock private videos</a>',
			),
			
			Settings::OPTION_DASHBOARD_PAGE => array(
				'type' => Settings::TYPE_SELECT,
				'options' => Settings::getPagesOptions() + array(Settings::PAGE_CREATE_KEY => '-- create new page --'),
				'category' => 'dashboard',
				'subcategory' => 'navigation',
				'title' => 'Dashboard page',
				'desc' => 'Select page which will display the user\'s dashboard (using the cmvl-dashboard shortcode) or choose '
							. '"-- create new page --" to create such page.',
				Settings::PAGE_DEFINITION => array(
					'post_title' => App::getPluginName() . ' Dashboard',
					'post_content' => '[cmvl-dashboard]',
				),
			),
			Settings::OPTION_LOGIN_REDIRECT_DASHBOARD => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'dashboard',
				'subcategory' => 'navigation',
				'title' => 'Show dashboard after login',
				'desc' => 'If enabled, users after login to Wordpress will be redirected to the Dashboard page by default.',
			),
			Settings::OPTION_DASHBOARD_TABS => array(
				'type' => Settings::TYPE_CUSTOM,
				'category' => 'dashboard',
				'subcategory' => 'tabs',
				'default' => array(
					array('label' => ucfirst(Labels::getLocalized('statistics')), 'content' => '[cmvl-stats]'),
					array('label' => ucfirst(Labels::getLocalized('bookmarks')), 'content' => '[cmvl-bookmarks]'),
				),
				'title' => 'Dashboard tabs',
				'content' => array(__CLASS__, 'displayDashboardSettings'),
				'castFunction' => array(__CLASS__, 'castDashboardSettings'),
				'desc' => 'Drag and drop to change the order.',
			),
		));
		
		if (Micropayments::isMicroPaymentsAvailable() OR PostEDDPayment::isAvailable()) {
			$config[Settings::OPTION_RELOAD_EXPIRED_SUBSCRIPTION] = array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'access',
				'title' => 'Reload lesson when subscription expires',
				'desc' => 'If enabled, script will check in the background if the subscription is still active and reload the lesson when it expires '
					. 'or user has been logged-out to disallow further watching lesson.',
			);
		}
		
		return $config;
	}
	
	
	
	static function displayDashboardSettings($optionName, $option) {
		$current = Settings::getOption($optionName);
		$out = '';
		foreach ($current as $i => $tab) {
			$out .= sprintf('<div class="cmvl-settings-dashboard-tab">
				<input type="text" name="%s[label][]" value="%s" placeholder="Tab label" />
				<a href="" class="cmvl-remove">Remove</a>
				<br /><textarea name="%s[content][]" placeholder="Tab content">%s</textarea>
			</div>', esc_attr($optionName), esc_attr($tab['label']), esc_attr($optionName), esc_html($tab['content']));
		}
		$out = '<div class="cmvl-settings-dashboard-tabs-outer">'. $out .'</div>';
		$out .= '<p><a href="" class="cmvl-settings-dashboard-tabs-add-btn">Add new tab</a></p>';
		return $out;
	}
	
	
	static function castDashboardSettings($value) {
		if (is_string($value) AND strpos($value) > 0) return unserialize($value);
		else if (is_array($value)) {
			if (isset($value['label'][0])) {
				$new = array();
				foreach (array('label', 'content') as $field) {
					foreach ($value[$field] as $i => $v) {
						$new[$i][$field] = $v;
					}
				}
				$value = $new;
			}
			return $value;
		} else return array();
	}
	
	
	static function cmvl_load_assets_frontend() {
		wp_enqueue_script('cmvl-playlist');
	}
	
	
	static function parseMarkupTags(Video $video) {
		$tags = $video->getDescriptionMarkupTags();
		foreach ($tags as $tag) {
			$text = sprintf('<a href="%s" class="cmvl-markup-button" target="_blank">%s</a>', esc_attr($tag[2]), esc_html($tag[1]));
			echo apply_filters('cmvl_video_markup_tag', $text, $video, $tag);
		}
	}
	
	
	static function login_redirect( $redirect_to, $request = null, $user = null) {
		if (Settings::getOption(Settings::OPTION_LOGIN_REDIRECT_DASHBOARD) AND $pageId = Settings::getOption(Settings::OPTION_DASHBOARD_PAGE)) {
			$redirect_to = get_permalink($pageId);
		}
		return $redirect_to;
	}
	

}
