<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\model\Search;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\model\Bookmark;
use com\cminds\videolesson\model\Note;

class SearchController extends Controller {
	
	const SEARCH_CONTEXT_ALL = 'all';
	const SEARCH_CONTEXT_VIDEOS = 'videos';

	protected static $actions = array(
		'init',
	);
	protected static $filters = array(
		array('name' => 'cmvl_search_context', 'args' => 4),
		array('name' => 'cmvl_search_score', 'args' => 5),
		array('name' => 'cmvl_playlist_shortcode_content', 'args' => 2, 'priority' => 60),
		array('name' => 'cmvl_playlist_shortcode_content_preprocess', 'args' => 2),
		array('name' => 'cmvl_playlist_shortcode_channel_content', 'args' => 3),
	);
	protected static $ajax = array('cmvl_video_search');
	
	
	static function init() {
		add_rewrite_tag('%cmvl-search%', '([^/]+)');
	}
	
	
	static function cmvl_video_search() {
		$str = filter_input(INPUT_POST, 's');
		$type = filter_input(INPUT_POST, 'cmvl-search');
		if (strlen($str) > 0 AND $type) {
			if (Channel::checkViewAccess()) {
				$videos = $ids = array();
				switch ($type) {
					case 'bookmarks':
						$ids = Bookmark::searchIds($str);
						break;
					case 'notes':
						$ids = Note::searchIds($str);
						break;
					case 'all':
						$ids = array_merge(Bookmark::searchIds($str), Note::searchIds($str), Video::search($str));
						break;
					case 'videos':
					default:
						$ids = Video::searchIds($str);
				}
				$videos = array_filter(array_map(function($id) { return Video::getInstance($id); }, array_unique($ids)));
				echo ChannelController::renderPlaylist($videos);
			} else {
				echo ChannelController::loadAccessDeniedView();
			}
		}
		exit;
	}
	
	
	static function cmvl_playlist_shortcode_content($content, $atts) {
		if (is_user_logged_in() AND !empty($atts['searchbar'])) { // Show search bar only for logged-in users:
			$search = '';
			if ($atts['urlsearch']) {
				if (!empty($_GET['cmvl-search'])) {
					$search = $_GET['cmvl-search'];
				}
				else if (!empty($_GET['cmvl-channel-search'])) {
					$search = $_GET['cmvl-channel-search'];
				}
			}
			$content = self::loadFrontendView('search-bar', array('search' => $search)) . $content;
		}
		return $content;
	}
	
	
	static function cmvl_search_context(array $videos, $type, array $words, $str) {
		if ($type == SearchController::SEARCH_CONTEXT_VIDEOS OR $type == SearchController::SEARCH_CONTEXT_ALL) {
			$videos += Video::getAll($onlyVisible = true);
		}
		return $videos;
	}
	
	
	static function cmvl_search_score($score, Video $video, $type, array $words, $str) {
		$score += Search::countScore($video->getTitle(), $words);
		if ($descWordsCount = count(Search::parseWords($video->getDescription()))) {
			$score += Search::countScore($video->getDescription(), $words) / $descWordsCount;
		}
		return $score;
	}
	
	
	static function cmvl_playlist_shortcode_content_preprocess($result, $atts) {
		if ($atts['urlsearch'] AND !empty($_GET['cmvl-search'])) {
			if (Channel::checkViewAccess()) {
				$videos = Search::search(
					$_GET['cmvl-search'],
					$context = SearchController::SEARCH_CONTEXT_VIDEOS
				);
				$result .= ChannelController::renderPlaylist($videos);
			} else {
				$result .= ChannelController::loadAccessDeniedView();
			}
		}
		return $result;
	}
	
	
	static function cmvl_playlist_shortcode_channel_content($content, $atts, Channel $channel) {
		if ($atts['urlsearch'] AND !empty($_GET['cmvl-channel-search'])) {
			$videos = Search::search($_GET['cmvl-channel-search'], $type = 'channel', $channel->getVideos());
			$content = ChannelController::renderPlaylist($videos);
		}
		return $content;
	}

}
