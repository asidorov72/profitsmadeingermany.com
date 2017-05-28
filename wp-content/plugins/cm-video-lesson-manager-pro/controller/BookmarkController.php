<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\App;
use com\cminds\videolesson\model\Search;
use com\cminds\videolesson\model\Bookmark;
use com\cminds\videolesson\model\Labels;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Channel;

class BookmarkController extends Controller {
	
	const SEARCH_CONTEXT_BOOKMARKS = 'bookmarks';
	
	protected static $filters = array(
		array('name' => 'cmvl_video_controls', 'args' => 2),
		array('name' => 'cmvl_search_context', 'args' => 4),
		array('name' => 'cmvl_search_score', 'args' => 5),
		'cmvl_playlist_links_bar',
		'cmvl_options_config'
	);
	protected static $ajax = array('cmvl_video_set_user_bookmark');
	
	
	static function render($view = null) {
		$videos = array_filter(array_map(function($id) { return Video::getInstance($id); }, Bookmark::getUserBookmarksVideosIds()));
		return ChannelController::renderPlaylist($videos, $pagination = array('per_page' => 9999), $view);
	}
	
	

	static function cmvl_video_set_user_bookmark() {
		header('content-type: application/json');
		if (is_user_logged_in() AND isset($_POST['videoId']) AND isset($_POST['bookmark'])) {
			if ($video = Video::getInstance($_POST['videoId'])) {
				$bookmark = new Bookmark($video);
				if ($_POST['bookmark'] == 'add') {
					$bookmark->addBookmark();
				} else {
					$bookmark->removeBookmark();
				}
				echo json_encode(array('status' => 'ok'));
				exit;
			}
		}
		echo json_encode(array('status' => 'error'));
		exit;
	}
	
	
	static function cmvl_video_controls($controls, Video $video) {
		if (is_user_logged_in()) {
			$bookmark = new Bookmark($video);
			$controls .= sprintf(
				'<li class="cmvl-bookmark%s" title="%s"></li>',
				($bookmark->isBookmarked() ? ' on' : ''),
				esc_attr(ucfirst(Labels::getLocalized('bookmark')))
			);
		}
		return $controls;
	}
	
	
	static function cmvl_options_config($config) {
		$config[Bookmark::OPTION_BOOKMARKS_PAGE] = array(
			'type' => Settings::TYPE_SELECT,
			'options' => Settings::getPagesOptions() + array(Settings::PAGE_CREATE_KEY => '-- create new page --'),
			'category' => 'general',
			'subcategory' => 'navigation',
			'title' => 'Bookmarks page',
			'desc' => 'Select page which will display the user\'s bookmarks (using the cmvl-bookmarks shortcode) or choose '
						. '"-- create new page --" to create such page.',
			Settings::PAGE_DEFINITION => array(
				'post_title' => App::getPluginName() . ' Bookmarks',
				'post_content' => '[cmvl-bookmarks]',
			),
		);
		return $config;
	}


	static function cmvl_search_context(array $videos, $type, array $words, $str) {
		if ($type == self::SEARCH_CONTEXT_BOOKMARKS OR $type == SearchController::SEARCH_CONTEXT_ALL) {
			$videos += array_filter(array_map(function($id) { return Video::getInstance($id); }, Bookmark::getUserBookmarksVideosIds()));
// 			$videos += Bookmark::getUserBookmarksVideosIds();
		}
		return $videos;
	}
	
	
	static function cmvl_search_score($score, Video $video, $type, array $words, $str) {
		if ($type == self::SEARCH_CONTEXT_BOOKMARKS OR $type == SearchController::SEARCH_CONTEXT_ALL) {
			$score += Search::countScore($video->getTitle(), $words);
			$count = count(Search::parseWords($video->getDescription()));
			if ($count) {
				$score += Search::countScore($video->getDescription(), $words) / $count;
			}
		}
		return $score;
	}
	
	
	static function cmvl_playlist_links_bar($content) {
		if ($bookmarksPageId = Settings::getOption(Bookmark::OPTION_BOOKMARKS_PAGE)) {
			$content .= sprintf('<li class="cmvl-bookmarks-link"><a href="%s">%s</a></li>',
				esc_attr(get_permalink($bookmarksPageId)),
				Labels::getLocalized('bookmarks_page_link')
			);
		}
		return $content;
	}
	
	
}
