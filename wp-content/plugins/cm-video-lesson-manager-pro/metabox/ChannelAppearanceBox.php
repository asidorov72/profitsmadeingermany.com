<?php

namespace com\cminds\videolesson\metabox;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\controller\ChannelBackendController;

class ChannelAppearanceBox extends MetaBox {

	const SLUG = 'cmvl-channel-appearance-box';
	const NAME = 'Appearance';
// 	const META_BOX_PRIORITY = 5;
	const CONTEXT = 'side';
	const PRIORITY = 'low';

	const FIELD_PAGE_TEMPLATE = 'cmvl_page_template';
	const FIELD_VIDEOS_LAYOUT = 'cmvl_video_layout';
	const FIELD_PLAYLIST_LAYOUT = 'cmvl_playlist_layout';
	
	static protected $supportedPostTypes = array(Channel::POST_TYPE);
	
	
	static function render($post) {
		
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend');
		
		static::renderNonceField($post);
		
		$channel = Channel::getInstance($post);
		
		$pageTemplatesOptions = array('' => 'use global setting') + Settings::getPageTemplatesOptions();
		$videosLayoutsOptions = array('' => 'use global setting') + Settings::getVideosLayoutOptions();
		$playlistLayoutsOptions = array('' => 'use global setting') + Settings::getPlaylistLayoutOptions();
		
		echo ChannelBackendController::loadBackendView('metabox-appearance', compact('channel', 'pageTemplatesOptions', 'videosLayoutsOptions', 'playlistLayoutsOptions'));
		
	}
	
	
	static function savePost($postId) {
		if ($channel = Channel::getInstance($postId)) {
			
			$pageTemplate = filter_input(INPUT_POST, static::FIELD_PAGE_TEMPLATE);
			$videosLayout = filter_input(INPUT_POST, static::FIELD_VIDEOS_LAYOUT);
			$playlistLayout = filter_input(INPUT_POST, static::FIELD_PLAYLIST_LAYOUT);
			
			$channel->setPageTemplate($pageTemplate);
			$channel->setVideosLayout($videosLayout);
			$channel->setPlaylistLayout($playlistLayout);
			
		}
	}
	
	
}