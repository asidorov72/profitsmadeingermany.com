<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\model\Settings;

use com\cminds\videolesson\model\Vimeo;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Channel;

use com\cminds\videolesson\App;
use com\cminds\videolesson\lib\VimeoAPI;
use com\cminds\videolesson\model\Note;
use com\cminds\videolesson\model\ProgressNotification;

class UpdateController extends Controller {
	
	const OPTION_NAME = 'cmvl_update_methods';
	const OPTION_UPDATE_2_0_0 = 'cmvl_update_2_0_0';
	
	const DEBUG = 0;
	
	
	static $actions = array(
		'init',
		'admin_notices',
// 		'admin_menu' => array('priority' => 12),
	);
	
	static $ajax = array(
		'cmvl_upgrade_2_0_0',
	);
	
	
	static function admin_notices() {
		global $wpdb;
		if (!get_option(static::OPTION_UPDATE_2_0_0)) {
			$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s", Channel::POST_TYPE));
			if ($count > 0) {
				wp_enqueue_style('cmvl-backend');
				wp_enqueue_script('cmvl-backend');
				$nonce = wp_create_nonce(static::OPTION_UPDATE_2_0_0);
				echo static::loadBackendView('2.0.0-update', compact('nonce'));
			}
		}
	}
	
	
	
// 	static function admin_menu() {
// 		add_submenu_page(App::MENU_SLUG . '-dont-show', App::getPluginName() . ' Update', 'Update', 'manage_options',
// 				$menuSlug = 'cmvl-update', array(get_called_class(), 'render'));
// 	}
	
	
	
// 	static function render() {
// 		wp_enqueue_style('cmvl-backend');
// 		if ($show = filter_input(INPUT_GET, 'show')) {
// 			echo static::loadBackendView($show);
// 		}
// 	}
	

	static function init() {
		global $wpdb;
		
		if (defined('DOING_AJAX') AND DOING_AJAX) return;
// 		static::update_2_0_0();
		
		$updates = get_option(self::OPTION_NAME);
		if (empty($updates)) $updates = array();
		$count = count($updates);
		
		$methods = get_class_methods(__CLASS__);
		foreach ($methods as $method) {
			if (preg_match('/^update((_[0-9]+)+)/', $method, $match)) {
				if (!in_array($method, $updates)) {
					call_user_func(array(__CLASS__, $method));
					$updates[] = $method;
				}
			}
		}
		
		if ($count != count($updates)) {
			update_option(self::OPTION_NAME, $updates);
		}
		
	}
	
	
	static function update_1_0_3() {
		global $wpdb;
		
		if (!App::isPro()) return;
		
		// Get subscription records in old format:
		$records = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key LIKE %s", 'cmvl_mp_subscription_%'), ARRAY_A);
		foreach ($records as $row) {
			if (preg_match('/^cmvl_mp_subscription_[0-9]+$/', $row['meta_key'])) {
				$value = @unserialize($row['meta_value']);
				if (!empty($value) AND is_array($value)) {
					
					// Create subscription records in new format
					$postId = $row['post_id'];
					$metaId = add_post_meta($postId, 'cmvl_mp_subscription', $value['userId'], $unique = false);
					add_post_meta($postId, 'cmvl_mp_subscription_start' .'_'. $metaId, $value['start'], $unique = true);
					add_post_meta($postId, 'cmvl_mp_subscription_end' .'_'. $metaId, $value['stop'], $unique = true);
					add_post_meta($postId, 'cmvl_mp_subscription_duration' .'_'. $metaId, $value['period'], $unique = true);
					add_post_meta($postId, 'cmvl_mp_subscription_points' .'_'. $metaId, 0, $unique = true);
					
					// Delete old record
					$wpdb->delete($wpdb->postmeta, array('meta_id' => $row['meta_id']));
					
				}
			}
		}
		
	}
	
	
	static function update_1_0_6() {
		$vimeo = Vimeo::getInstance();
		$vimeo->disableCacheOnce();
		$result = $vimeo->request('/me/channels', array('per_page' => 50, 'filter' => 'moderated'));
		$vimeoChannels = array();
		if ($result AND !empty($result['body']['total'])) {
			foreach ($result['body']['data'] as $row) {
				$vimeoChannels[Channel::parseId($row['uri'])] = Channel::normalizeUri($row['uri']);
			}
		}
		
		$channels = Channel::getAll($onlyVisible = false);
		
		foreach ($channels as $channel) {
			$channelId = get_post_meta($channel->getId(), App::prefix('_vimeo_id'), $single = true);
			if ($channelId AND isset($vimeoChannels[$channelId])) {
				$channel->setVimeoUri($vimeoChannels[$channelId]);
				delete_post_meta($channel->getId(), App::prefix('_vimeo_id'));
			}
		}
	}
	
	
	static function update_1_1_3() {
		update_option(App::prefix(App::OPTION_TRIGGER_FLUSH_REWRITE), 1);
	}
	
	
	static function update_1_6_1() {
		// Force to increase the cache lifetime
		update_option(Settings::OPTION_VIMEO_CACHE_SEC, 3600);
	}
	
	
	static function cmvl_upgrade_2_0_0() {
		global $wpdb;
		
		$nonce = filter_input(INPUT_POST, 'nonce');
		if (!wp_verify_nonce($nonce, static::OPTION_UPDATE_2_0_0)) die('Invalid nonce');
		
		$start = microtime(true);
		
		try {
			
			// Separate update for each site in network
			$blogId = get_current_blog_id();
			static::_update_2_0_0_single_site($blogId);
			
// 			if (is_multisite()) {
// 				$blogs = get_sites();
// 				foreach ($blogs as $blog) {
// 					$blogId = $blog->blog_id;
// 					switch_to_blog($blogId);
// 					static::_update_2_0_0_single_site($blogId);
// 				}
// 			}
			
			update_option(static::OPTION_UPDATE_2_0_0, time());
			echo 'ok';
			
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
		
		exit;
		
		$end = microtime(true);
		var_dump(($end-$start)*1000);exit;
		
	}
	
	
	protected static function _update_2_0_0_single_site($blogId) {
		
		global $wpdb;
		
		$channels = $wpdb->get_results($wpdb->prepare("SELECT ID, pm_vimeo.meta_value AS channel_vimeo_uri
				FROM $wpdb->posts p
				JOIN $wpdb->postmeta pm_vimeo ON pm_vimeo.post_id = p.ID AND pm_vimeo.meta_key = %s
				WHERE p.post_type = %s",
				'CMVL_vimeo_uri',
				'cmvl_channel'
			), ARRAY_A);
		
		static::debug($channels);
		
		foreach ($channels as $channel) {
			
			$channelId = $channel['ID'];
			
			$sql = $wpdb->prepare("SELECT v.ID, meta_value
					FROM $wpdb->postmeta pm
					JOIN $wpdb->posts as v ON v.ID = pm.post_id
					WHERE v.post_type = %s AND meta_key = %s AND v.post_parent = %d", Video::POST_TYPE, Video::META_PROVIDERS_ID, $channelId);
			$existingVideosUris = array();
			$records = $wpdb->get_results($sql, ARRAY_A);
			foreach ($records as $rec) {
				$existingVideosUris[$rec['meta_value']] = $rec['ID'];
			}
			
			static::debug('existingVideosUris');
			static::debug($existingVideosUris);
			
			// Migrate channel progress notifications
			static::_update_2_0_0_create_notifications($channelId, 'channel', $channelId);
			
			// Get videos from Vimeo API
			$videos = VimeoAPI::getInstance()->getAllVideos($channel['channel_vimeo_uri'] . '/videos');
			
			static::debug('Vimeo request');
			static::debug($videos);
			
			foreach ($videos as $video) {
				
				$uri = $video['id'];
				$videoUriId = preg_replace('~[^0-9]~', '', $video['id']);
				
				if (!isset($existingVideosUris[$uri])) {
					
					// Create video post
					$videoPostId = VideoBackendController::importVideo(Settings::API_VIMEO, $video, $channelId);
					
					static::debug('video added ID = ' . $videoPostId);
					
				} else {
					$videoPostId = $existingVideosUris[$uri];
					static::debug('video exists ' . $video['id'] . ' ID = ' . $videoPostId);
				}
				
				static::_update_2_0_0_create_notifications($videoPostId, 'video', $videoUriId);
				static::_update_2_0_0_create_bookmarks($channelId, $videoPostId, $videoUriId);
				static::_update_2_0_0_video_notes($videoPostId, $videoUriId);
				static::_update_2_0_0_stats($channelId, $videoUriId, $videoPostId);
				
			}
		}
		
	}
	
	
	
	protected static function _update_2_0_0_create_notifications($postId, $type, $previousId) {
		global $wpdb;
		
		$notif = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->usermeta WHERE meta_key = %s AND meta_value = %s",
				'cmvl_progress_notif_sent_' . $type, $previousId), ARRAY_A);
		foreach ($notif as $record) {
		
			$r = $wpdb->insert($wpdb->comments, array(
					'comment_post_ID'      => $postId,
					'comment_content'      => 'sent',
					'comment_approved'     => 1,
					'comment_date'         => current_time('mysql'),
					'comment_type'         => ProgressNotification::COMMENT_TYPE,
					'user_id'              => $record['user_id'],
					'comment_author' => '',
					'comment_author_email' => '',
					'comment_author_IP' => '',
					'comment_agent' => '',
			));
			
			static::debug('created notification record for ' . $type . ' ID = ' . $postId . ' (previous ID = '. $previousId .')');
			static::debug($r);
			
		}
		
	}
	
	
	
	protected static function _update_2_0_0_create_bookmarks($channelId, $videoPostId, $videoUriId) {
		global $wpdb;
		
		$oldBookmarks = $wpdb->get_results($wpdb->prepare("SELECT user_id, meta_value FROM $wpdb->usermeta WHERE meta_key = %s AND meta_value LIKE %s",
				'cmvl_video_bookmarks', $channelId . '\\_' . $videoUriId
		), ARRAY_A);
		
		static::debug('old bookmarks for channel = ' . $channelId .' and video URI = ' . $videoUriId);
		static::debug($oldBookmarks);
		
		foreach ($oldBookmarks as $bookmark) {
			
			// Don't create the same record twice
			$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s AND meta_value = %s",
					$videoPostId, 'cmvl_bookmark_user_id', $note['user_id']));
			if ($count == 0) {
			
				$r = add_post_meta($videoPostId, 'cmvl_bookmark_user_id', $bookmark['user_id'], $unique = false);
				static::debug('added bookmark for video ID = ' . $videoPostId . ' URI = ' . $videoUriId);
				static::debug($r);
				
			}
		}
		
	}
	
	
	protected static function _update_2_0_0_video_notes($videoPostId, $videoUriId) {
		global $wpdb;
		
		$oldNotes = $wpdb->get_results($wpdb->prepare("SELECT user_id, meta_value FROM $wpdb->usermeta WHERE meta_key = %s",
			'cmvl_video_note_' . $videoUriId
		), ARRAY_A);
		
		static::debug('old notes for video ID = ' . $videoPostId .' and video URI = ' . $videoUriId);
		static::debug($oldNotes);
		
		foreach ($oldNotes as $note) {
			$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_type = %s AND user_id = %d",
					$videoPostId, 'cmvl_user_note', $note['user_id']), ARRAY_A);
			if ($count == 0) { // create
				$r = $wpdb->insert($wpdb->comments, array(
						'comment_post_ID'      => $videoPostId,
						'comment_content'      => $note['meta_value'],
						'comment_approved'     => 1,
						'comment_date'         => current_time('mysql'),
						'comment_type'         => 'cmvl_user_note',
						'user_id'              => $note['user_id'],
						'comment_author' => '',
						'comment_author_email' => '',
						'comment_author_IP' => '',
						'comment_agent' => '',
				));
				static::debug('created video note');
				static::debug($r);
			}
			Note::createOrUpdateNote($videoPostId, $note['user_id'], $note['meta_value']);
		}
		
	}
	
	protected static function _update_2_0_0_stats($channelId, $videoUriId, $videoPostId) {
		
		$secondsMetaKey = 'cmvl_vidstatwatchedseconds';
		$intervalsMetaKey = 'cmvl_vidstatintervals';
		
		global $wpdb;
		$intervals = $wpdb->get_results($wpdb->prepare("SELECT meta_value, user_id FROM $wpdb->usermeta
				WHERE meta_key = %s", $intervalsMetaKey .'_' . $channelId . '_' . $videoUriId), ARRAY_A);
		
		static::debug('old stats intervals for channel ID = '. $channelId .' and video ID =  ' . $videoPostId .' and video URI = ' . $videoUriId);
		static::debug($intervals);
		
		$seconds = $wpdb->get_results($wpdb->prepare("SELECT meta_value, user_id FROM $wpdb->usermeta
				WHERE meta_key = %s", $secondsMetaKey .'_' . $channelId . '_' . $videoUriId), ARRAY_A);
		
		static::debug('old stats seconds for channel ID = '. $channelId .' and video ID =  ' . $videoPostId .' and video URI = ' . $videoUriId);
		static::debug($seconds);
		
		$secondsByUserId = array();
		foreach ($seconds as $row) {
			$secondsByUserId[$row['user_id']] = $row['meta_value'];
		}
		
		foreach ($intervals as $record) {
			
			$userId = $record['user_id'];
			
			// Don't add the same record few times
			$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments WHERE user_id = %d AND comment_post_ID = %s AND comment_type = %s",
					$userId, $videoPostId, 'cmvl_video_stats'));
			if ($count != 0) continue;
			
			$r = $wpdb->insert($wpdb->comments, array(
				'comment_post_ID'      => $videoPostId,
				'comment_content'      => $record['meta_value'],
				'comment_approved'     => 1,
				'comment_date'         => current_time('mysql'),
				'comment_type'         => 'cmvl_video_stats',
				'comment_karma'        => ceil(isset($secondsByUserId[$userId]) ? $secondsByUserId[$userId] : 0), // watched seconds
				'user_id'              => $userId,
				'comment_author' => '',
				'comment_author_email' => '',
				'comment_author_IP' => '',
				'comment_agent' => '',
			));
			
			static::debug('created video stat record for video ID = ' .  $videoPostId . ' and user ID = ' . $userId);
			static::debug($r);
		}
		
	}
	
	
	protected static function debug($val) {
		if (static::DEBUG) {
			$backtrace = debug_backtrace();
			$func = reset($backtrace);
			echo '<br>In line: ' . $func['line'];
			var_dump($val);
		}
	}
	
	
}
