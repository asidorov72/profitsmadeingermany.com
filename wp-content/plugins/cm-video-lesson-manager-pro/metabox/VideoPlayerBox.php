<?php

namespace com\cminds\videolesson\metabox;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\helper\PlayerHelper;

class VideoPlayerBox extends MetaBox {

	const SLUG = 'cmvl-video-player-box';
	const NAME = 'Player';
	const CONTEXT = 'normal';
	const PRIORITY = 'high';
	
	static protected $supportedPostTypes = array(Video::POST_TYPE);
	
	
	static function render($post) {
		
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend');
		
		$video = Video::getInstance($post);
		echo PlayerHelper::getPlayer($video);
		
	}
	
	
}