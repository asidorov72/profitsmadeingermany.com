<?php

namespace com\cminds\videolesson\shortcode;

use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\controller\ChannelController;


class BookmarksShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cmvl-bookmarks';
	
	
	static function shortcode($atts) {
		if (is_user_logged_in()) {
			$atts['lesson'] = 'bookmarks';
			return PlaylistShortcode::shortcode($atts);
		}
	}
	
	
}
