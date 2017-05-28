<?php

namespace com\cminds\videolesson\shortcode;

use com\cminds\videolesson\App;

use com\cminds\videolesson\model\Settings;

use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\controller\ChannelController;


class PlaylistShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cmvl-playlist';
	
	
	static function shortcode($atts) {
		
		// Rewrite some params for backwards compatibility
		if (!isset($atts['course']) AND isset($atts['category'])) $atts['course'] = $atts['category'];
		if (!isset($atts['lesson']) AND isset($atts['channel'])) $atts['lesson'] = $atts['channel'];
		if (!isset($atts['lessondesc']) AND isset($atts['lessondesc'])) $atts['lessondesc'] = $atts['channeldesc'];
		
		$atts = shortcode_atts(array(
			'navbar' => 1,
			'searchbar' => 1,
			'linksbar' => 1,
			'ajax' => 1,
			'view' => '',
			'layout' => '',
// 			'category' => null, // deprecated
			'course' => null,
// 			'channel' => null, // deprecated
			'lesson' => null,
// 			'channeldesc' => 1, // deprecated
			'videodesc' => Settings::getOption(Settings::OPTION_SHOW_VIDEO_DESCRIPTION),
			'lessondesc' => Settings::getOption(Settings::OPTION_SHOW_LESSON_DESCRIPTION),
			'coursedesc' => Settings::getOption(Settings::OPTION_SHOW_COURSE_DESCRIPTION),
			'urlsearch' => 0,
			'video' => '',
			'maxwidth' => Settings::getOption(Settings::OPTION_PLAYLIST_MAX_WIDTH),
		), $atts);
		
		
		// Check if other controller has injected a different content for this shortcode:
		$result = apply_filters('cmvl_playlist_shortcode_content_preprocess', '', $atts);
		
		if (empty($result)) {
			
			$categoriesTree = Category::getTree(array('hide_empty' => 1));
			$currentCategory = null;
			if (!empty($atts['course']) AND $currentCategory = Category::getInstance($atts['course'])) {
				// ok
			} else {
				$atts['course'] = key($categoriesTree);
				$currentCategory = Category::getInstance($atts['course']);
			}
			
			$channels = array();
			if (!empty($currentCategory)) {
				$channels = $currentCategory->getChannels();
			}
			
			$currentChannel = null;
			if ($atts['lesson'] == 'bookmarks') {
				// do nothing
			}
			else if (!empty($atts['lesson']) AND $currentChannel = Channel::getInstance($atts['lesson'])) {
				// ok
			}
			else if ($channels) {
				$atts['lesson'] = $channels[0]->getId();
				$currentChannel = Channel::getInstance($atts['lesson']);
			}
			else $atts['lesson'] = null;
			
			$displayOptions = array();
			
			// Navbar
			if (!empty($atts['navbar']) AND $atts['lesson'] != 'bookmarks' AND Channel::checkViewAccess()) {
				$currentChannelId = ($currentChannel ? $currentChannel->getId() : null);
				$currentCategoryId = ($currentCategory ? $currentCategory->getId() : null);
				$result .= ChannelController::loadView('frontend/playlist/navbar',
					compact('currentChannel', 'currentChannelId', 'currentCategory', 'currentCategoryId', 'categoriesTree', 'channels'));
			}
			
			// Playlist
			if (!empty($atts['lesson'])) {
				$bookmarkClass = App::namespaced('controller\BookmarkController');
				if ($atts['lesson'] == 'bookmarks' AND class_exists($bookmarkClass)) {
					$result .= $bookmarkClass::render($atts['view']);
				} else {
					
					// Check if other controller has injected a different content for this channel:
					$channelContent = apply_filters('cmvl_playlist_shortcode_channel_content', '', $atts, $currentChannel, $currentCategory);
					if (empty($channelContent)) {
						$channelContent = ChannelController::playlist($atts['lesson'], $pagination = array(), $atts['view'], $atts['layout'], $atts['video'], $currentCategory, $atts);
					}
					$result .= $channelContent;
					
				}
			} else {
				$result .= ChannelController::loadNotFoundView();
			}
			
		}
		
		$extra = '';
		if ($atts['ajax']) {
			$extra .= ' data-use-ajax="1" data-shortcode-atts="'. esc_attr(json_encode($atts)) .'" ';
		}
		
		$result = apply_filters('cmvl_playlist_shortcode_content', $result, $atts);
		
		if (!empty($atts['maxwidth'])) {
// 			$result .= '<style type="text/css">.cmvl-widget-playlist {max-width: '. intval($atts['maxwidth']) .'px;}</style>';
			$extra .= ' style="max-width:'. intval($atts['maxwidth']) .'px;" ';
		}
		
		return self::wrap($result, $extra);
	}

	
}
