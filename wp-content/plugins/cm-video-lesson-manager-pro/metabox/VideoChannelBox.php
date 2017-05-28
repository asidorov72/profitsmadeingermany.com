<?php

namespace com\cminds\videolesson\metabox;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\controller\VideoBackendController;

class VideoChannelBox extends MetaBox {

	const SLUG = 'cmvl-video-channel-box';
	const NAME = 'Lesson';
	const CONTEXT = 'side';
	const PRIORITY = 'high';
	
	const FIELD_CHANNEL_ID = 'cmvl_channel_id';
	
	static protected $supportedPostTypes = array(Video::POST_TYPE);
	
	
	static function render($post) {
		
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend');
		
		static::renderNonceField($post);
		static::renderChannelsList($post);
		
	}
	
	
	static function renderChannelsList($post) {
		$video = Video::getInstance($post);
		$channel = $video->getChannel();
		echo VideoBackendController::loadBackendView('metabox-channel', compact('video', 'channel'));
	}
	
	
	static function savePost($postId) {
		if ($video = Video::getInstance($postId)) {
			$channelId = filter_input(INPUT_POST, static::FIELD_CHANNEL_ID);
			$video->setChannelId($channelId);
			$video->save();
		}
	}
	
}