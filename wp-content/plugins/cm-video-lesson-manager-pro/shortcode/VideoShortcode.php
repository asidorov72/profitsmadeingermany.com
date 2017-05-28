<?php

namespace com\cminds\videolesson\shortcode;

use com\cminds\videolesson\model\Video;

class VideoShortcode extends Shortcode {

	const SHORTCODE_NAME = 'cmvl-video';


	static function shortcode($atts) {

		$atts = shortcode_atts(array(
			'id' => null,
		), $atts);
		
		
		if ($atts['id'] AND is_numeric($atts['id']) AND $video = Video::getInstance($atts['id'])) {
			
			$atts = array(
				'navbar' => 0,
				'searchbar' => 0,
				'linksbar' => 0,
				'layout' => 'nomenu',
				'lessondesc' => 0,
				'video' => $video->getId(),
				'lesson' => $video->getChannelId(),
			);
			
			return PlaylistShortcode::shortcode($atts);
			
		}
		
	}
	
	
}