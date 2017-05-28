<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\model\Channel;

class ChannelBackendController extends Controller {
	
	const NONCE_AJAX_CHANNEL = 'cmvl_channel_ajax_nonce';
	const NONCE_AJAX_BACKEND = 'cmvl_channel_ajax_backend_nonce';
	
	static $filters = array(
		'manage_cmvl_channel_posts_columns',
	);
	static $actions = array(
		'manage_cmvl_channel_posts_custom_column' => array('args' => 2),
	);
	static $ajax = array(
		'cmvl_unlock_private_videos',
	);
	
	static function manage_cmvl_channel_posts_columns($columns) {
		$before['cb'] = $columns['cb'];
		$before['cmvl_thumb_col'] = 'Image';
		unset($columns['cb']);
		return $before + $columns;
	}
	
	
	static function manage_cmvl_channel_posts_custom_column($column, $postId) {
		if ($column == 'cmvl_thumb_col') {
			if ($channel = Channel::getInstance($postId) AND $url = $channel->getThumbUri()) {
				printf('<img src="%s" alt="Image" />', esc_attr($url));
			}
		}
	}
	
	
	static function cmvl_unlock_private_videos() {
		if ($nonce = filter_input(INPUT_POST, 'nonce') AND wp_verify_nonce($nonce, static::NONCE_AJAX_BACKEND)) {
				
			$videos = Video::getAll($onlyVisible = false, $status = 'publish', $provider = Settings::API_VIMEO);
			foreach ($videos as $video) {
				echo 'Checking video: '. $video->getTitle();
				$privacy = new VimeoPrivacyHelper($video);
				if ($privacy->unlock()) {
					echo ' ... unlocked';
				} else {
					echo ' ... no need';
				}
				echo '<br>';
				ob_flush();
				flush();
			}
				
			if (empty($videos)) {
				echo 'No videos found.<br>';
			}
				
			echo '<br>END.';
			exit;
				
		}
	}
	
	
	
}