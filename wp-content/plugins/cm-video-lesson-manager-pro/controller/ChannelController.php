<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\helper\VimeoPrivacyHelper;

use com\cminds\videolesson\model\Settings;

use com\cminds\videolesson\shortcode\PlaylistShortcode;

use com\cminds\videolesson\model\Category;

use com\cminds\videolesson\model\Video;

use com\cminds\videolesson\model\Channel;

class ChannelController extends Controller {
	
	const DEFAULT_VIEW = Settings::LAYOUT_TILES;
	const PARAM_PAGE = 'cmvl_page';
	const NONCE_EDIT_CHANNEL = 'cmvl_channel_edit_nonce';
	const NONCE_AJAX_CHANNEL = 'cmvl_channel_ajax_nonce';
	const NONCE_AJAX_BACKEND = 'cmvl_ajax_backend';

	
	protected static $actions = array(
// 		'plugins_loaded',
		'init',
		'save_post' => array('args' => 1),
// 		'template_redirect',
	);
	protected static $filters = array(
		'the_content',
		'cmvl_playlist_shortcode_content' => array('args' => 2, 'priority' => 30),
		'cmvl_is_channel_completed' => array('args' => 3),
		'template_include' => array('priority' => 1000),
		'cmvl_get_lesson_description' => array('args' => 2),
	);
	
	protected static $suspendActions = 0;
	

	static function init() {
		add_rewrite_tag('%video%', '(\d+)');
		add_rewrite_tag('%'. Category::TAXONOMY .'%', '([^/]+)');
	}
	

	static function save_post($post_id) {
		if (!empty($_POST[self::NONCE_EDIT_CHANNEL]) AND wp_verify_nonce($_POST[self::NONCE_EDIT_CHANNEL], self::NONCE_EDIT_CHANNEL) AND $channel = Channel::getInstance($post_id)) {
// 			static::$suspendActions++;
			
			if (!$channel->getCategories()) {
				$channel->addDefaultCategory();
			}
			
			do_action('cmvl_save_channel', $channel);
			
// 			static::$suspendActions--;
		}
	}
	
	
	static function the_content($content) {
		if (is_main_query() AND is_single() AND get_post_type() == Channel::POST_TYPE) {
			
			global $post;
			
			$channel = Channel::getInstance($post);
			$view = $channel->getVideosLayoutOrGlobal();
			$layout = $channel->getPlaylistLayoutOrGlobal();
			$atts = array();
			
			if (!empty($_POST['nonce']) AND wp_verify_nonce($_POST['nonce'], self::NONCE_AJAX_CHANNEL)) {
				
				$view = (isset($_POST['view']) ? $_POST['view'] : '');
				
				if (!empty($_POST['shortcodeAtts'])) {
					$atts = $_POST['shortcodeAtts'];
				}
				
			} else {
				if (Settings::getOption(Settings::OPTION_DISABLE_CHANNEL_PAGE)) {
					return self::loadFrontendView('access_denied', compact('channel'));
				}
			}
			
			if (empty($atts)) {
				$atts = array(
					'view' => $view,
					'layout' => $layout,
					'ajax' => 0,
					'videodesc' => Settings::getOption(Settings::OPTION_SHOW_VIDEO_DESCRIPTION),
					'lessondesc' => Settings::getOption(Settings::OPTION_SHOW_LESSON_DESCRIPTION),
					'coursedesc' => Settings::getOption(Settings::OPTION_SHOW_COURSE_DESCRIPTION),
				);
			}
			
// 			var_dump($view);exit;

			$category = get_query_var(Category::TAXONOMY);
			if (empty($category)) {
				if ($categories = $channel->getCategories()) {
					$cat = reset($categories);
					$category = $cat->getId();
				}
			}
			$atts['course'] = $category;
			$atts['lesson'] = $post->ID;
			
			$playlist = PlaylistShortcode::shortcode($atts);
			
			self::loadAssets();
			return self::loadFrontendView('single_content', compact('content', 'playlist'));
			
		} else {
			return $content;
		}
	}
	
	
	static function playlist($post, $pagination = array(), $view = null, $layout = null, $videoId = null, $currentCategory = null, $atts = array()) {
		
		$view = self::checkView($view);
		$pagination = shortcode_atts(array(
			'page' => ((isset($_GET[self::PARAM_PAGE]) AND is_numeric($_GET[self::PARAM_PAGE])) ? $_GET[self::PARAM_PAGE] : 1),
			'per_page' => (($view == Settings::LAYOUT_PLAYLIST) ? 999 : Settings::getOption(Settings::OPTION_PAGINATION_LIMIT)),
		), $pagination);
		
		if ($channel = Channel::getInstance($post)) {
			do_action('cmvl_channel_playlist_load', $channel);
			if ($channel->canView()) {
				if (empty($videoId)) {
					$videos = $channel->getVideos($onlyVisible = true, $pagination);
					$pagination['total'] = $channel->getTotalVideos();
				} else {
					$videos = array(Video::getInstance($videoId));
					$pagination['total'] = 1;
				}
				$videos = array_filter($videos);
				$categories = $channel->getCategories();
				$pagination['base_url'] = $channel->getPermalinkForCategory($currentCategory ? $currentCategory : $categories[0]);
				return self::renderPlaylist($videos, $pagination, $view, $layout, $channel, $currentCategory, $atts);
			} else {
				return self::loadAccessDeniedView($channel);
			}
		} else {
			return self::loadNotFoundView();
		}
	}
	
	
	static function renderPlaylist($videos, $pagination = array(), $view = null, $layout = null, $channel = null, $currentCategory = null, $atts = array()) {
		
		self::loadAssets();
		
		$view = self::checkView($view);
		$pagination = shortcode_atts(array(
			'page' => 1,
			'per_page' => ($view == Settings::LAYOUT_PLAYLIST ? 999 : Settings::getOption(Settings::OPTION_PAGINATION_LIMIT)),
			'total' => 0,
			'base_url' => null,
		), $pagination);
		
		$currentVideo = reset($videos);
// 		var_dump(get_query_var('video'));exit;
		if ($currentVideoId = get_query_var('video')) {
			foreach ($videos as $v) {
				if ($v->getId() == $currentVideoId) {
					$currentVideo = $v;
					break;
				}
			}
		}
		
		$playerOptions = array('autoplay' => false /*self::isAjax()*/ );
		
		if ($pagination['per_page'] > 0) {
			$pagination['total_pages'] = ceil($pagination['total'] / $pagination['per_page']);
		}
		if (empty($pagination['total_pages'])) {
			$pagination['total_pages'] = 1;
		}
		if (!empty($pagination['base_url']) AND $pagination['total_pages'] > 1) {
			$paginationView = self::loadView('frontend/playlist/pagination', $pagination);
		} else $paginationView = '';
		
		$layout = (empty($layout) ? Settings::getOption(Settings::OPTION_PLAYLIST_LAYOUT) : $layout);
		
		$videos = array_filter($videos);
		$params = compact('videos', 'currentVideo', 'playerOptions', 'paginationView', 'layout', 'channel', 'currentCategory', 'atts');
		$content = self::loadView('frontend/playlist/' . $view, $params);
		return apply_filters('cmvl_render_playlist', $content, $params);
		
	}
	
	
	protected static function checkView($view) {
		if ($availableViews = Settings::getOptionConfig(Settings::OPTION_VIDEOS_LAYOUT)) {
			$availableViews = array_keys($availableViews['options']);
			if (!in_array($view, $availableViews)) {
				$view = Settings::getOption(Settings::OPTION_VIDEOS_LAYOUT);
				if (empty($view)) {
					$view = self::DEFAULT_VIEW;
				}
			}
			return $view;
		} else {
			return self::DEFAULT_VIEW;
		}
	}
	
	
	static function cmvl_playlist_shortcode_content($content, $atts) {
		if (!empty($atts['linksbar']) AND $linksbar = apply_filters('cmvl_playlist_links_bar', '')) {
			$content = sprintf('<ul class="cmvl-inline-nav">%s</ul>', $linksbar) . $content;
		}
		return $content;
	}
	
	
	static function template_redirect() {
		if (is_main_query() AND is_single() AND get_post_type() == Channel::POST_TYPE) {
			
		}
	}
	
	
	
	static function cmvl_is_channel_completed($result, $channelId, $userId) {
		if ($channel = Channel::getInstance($channelId)) {
			$result = $channel->isChannelCompleted($userId);
		}
		return $result;
	}
	


	static function loadAssets() {
		wp_enqueue_style('dashicons');
		wp_enqueue_style('cmvl-frontend');
		wp_enqueue_script('cmvl-utils');
		wp_enqueue_script('jquery');
		wp_enqueue_script('cmvl-paybox');
		wp_enqueue_script('cmvl-playlist');
		do_action('cmvl_load_assets_frontend');
		add_action('wp_footer', array(__CLASS__, 'footerCustomCSS'));
	}
	
	
	static function footerCustomCSS() {
		echo '<style type="text/css">/* CMVL Custom CSS */' . PHP_EOL . Settings::getOption(Settings::OPTION_CUSTOM_CSS) . PHP_EOL . '</style>';
	}
	
	
	static function loadAccessDeniedView(Channel $channel = null) {
		self::loadAssets();
		return self::loadFrontendView('access_denied', compact('channel'));
	}
	
	
	static function loadNotFoundView() {
		self::loadAssets();
		return self::loadFrontendView('not_found');
	}
	
	
	
	static function template_include($template) {
		
		if (is_main_query() AND is_single() AND get_post_type() == Channel::POST_TYPE) {
			
			global $post;
			
			if (!empty($post) AND $channel = Channel::getInstance($post)) {
// 				var_dump($pageTemplate);exit;
				if ($pageTemplate = $channel->getPageTemplate()) {
					// Lesson's specific page template
					$template = locate_template($pageTemplate, false, false);
				}
				else if ($pageTemplate = Settings::getOption(Settings::OPTION_PAGE_TEMPLATE)) {
					// Global page template for lessons
					$template = locate_template($pageTemplate, false, false);
				}
			}
			
		}
		
		return $template;
	}
	

	static function cmvl_get_lesson_description($content, Channel $channel) {
		return static::loadFrontendView('description', compact('channel'));
	}
	
	
}
