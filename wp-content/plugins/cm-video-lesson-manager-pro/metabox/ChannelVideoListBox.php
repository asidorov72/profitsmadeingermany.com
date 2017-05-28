<?php

namespace com\cminds\videolesson\metabox;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\controller\ChannelBackendController;
use com\cminds\videolesson\model\Video;

class ChannelVideoListBox extends MetaBox {

	const SLUG = 'cmvl-channel-video-list-box';
	const NAME = 'Videos';
	const META_BOX_PRIORITY = 5;
// 	const CONTEXT = 'side';
// 	const PRIORITY = 'high';
	
	static protected $supportedPostTypes = array(Channel::POST_TYPE);
	
	
	static function render($post) {
		
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend');
		
		static::renderNonceField($post);
		static::renderChannelsList($post);
		
	}
	
	
	static function renderChannelsList($post) {
		$channel = Channel::getInstance($post);
		$videos = $channel->getVideos();
		echo ChannelBackendController::loadBackendView('metabox-video-list', compact('channel', 'videos'));
	}
	
	
	static function savePost($postId) {
		$channelId = $postId;
		if ($channel = Channel::getInstance($postId)) {
			if (isset($_POST['cmvl_videos']) AND is_array($_POST['cmvl_videos'])) {
				
				$newVideosIds = array_filter($_POST['cmvl_videos']);
				$oldVideosIds = $channel->getVideosIds();
				
				$toAdd = array_diff($newVideosIds, $oldVideosIds);
				$toRemove = array_diff($oldVideosIds, $newVideosIds);
				
				foreach ($newVideosIds as $i => $videoId) {
					if ($video = Video::getInstance($videoId)) {
						$video->setChannelId($channelId);
						$video->setMenuOrder($i);
						$video->save();
					}
				}
				
				foreach ($toRemove as $i => $videoId) {
					if ($video = Video::getInstance($videoId)) {
						$video->setChannelId(null);
						$video->setMenuOrder(0);
						$video->save();
					}
				}
				
			}
		}
	}
	
}