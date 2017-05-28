<?php

namespace com\cminds\videolesson\shortcode;

/**
 * 
 * @deprecated
 *
 */
class ChannelsListShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cmvl-channels-list';
	
	
	static function shortcode($atts = array()) {
		return LessonsListShortcode::shortcode($atts);
	}
	
	
}
