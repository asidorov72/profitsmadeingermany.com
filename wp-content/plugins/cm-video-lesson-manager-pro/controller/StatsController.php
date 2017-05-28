<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\App;
use com\cminds\videolesson\model\Labels;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\model\Stats;
use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\model\VideoStatistics;

class StatsController extends Controller {

	protected static $filters = array(
		'cmvl_options_config',
		'cmvl_playlist_links_bar'
	);
	protected static $actions = array(
		'cmvl_get_users_stats' => array('args' => 2),
	);
	protected static $ajax = array('cmvl_video_watching_stats');
	
	
	

	static function cmvl_options_config($config) {
		$config[Settings::OPTION_STATS_PAGE] = array(
			'type' => Settings::TYPE_SELECT,
			'options' => Settings::getPagesOptions() + array(Settings::PAGE_CREATE_KEY => '-- create new page --'),
			'category' => 'general',
			'subcategory' => 'navigation',
			'title' => 'Statistics page',
			'desc' => 'Select page which will display the user\'s statistics (using the cmvl-stats shortcode) or choose '
						. '"-- create new page --" to create such page.',
			Settings::PAGE_DEFINITION => array(
				'post_title' => App::getPluginName() . ' Statistics',
				'post_content' => '[cmvl-stats permalinks=1]',
			),
		);
		return $config;
	}
	
	
	static function cmvl_video_watching_stats() {
	
		$videoId = filter_input(INPUT_POST, 'videoId');
		$start = filter_input(INPUT_POST, 'start');
		$stop = filter_input(INPUT_POST, 'stop');
		
		if (is_user_logged_in() AND $videoId AND is_numeric($start) AND is_numeric($stop)
// 			AND isset($_POST['start']) AND is_numeric($_POST['start']) AND $_POST['start'] >= 0 AND $_POST['start'] <= 100
// 			AND isset($_POST['stop']) AND is_numeric($_POST['stop']) AND $_POST['stop'] >= 0 AND $_POST['stop'] <= 100
			AND $start < $stop) {
				
			if ($video = Video::getInstance($videoId)) {
				
				$userId = get_current_user_id();
				$stats = VideoStatistics::getByVideoForUserOrCreate($video->getId(), $userId);
				
				$duration = $video->getDurationSec();
				$startPercent = $start / $duration * 100;
				$stopPercent = $stop / $duration * 100;
				
// 				var_dump($stats);
// 				var_dump($stats->getVideo());
				$stats->addUserStatsInterval($startPercent, $stopPercent);
				

				echo 'ok';
	
			} else {
				echo 'invalid video';
			}
				
		} else {
			echo 'input error';
		}
		exit;
	}
	
	
	static function cmvl_playlist_links_bar($content) {
		if ($statsPageId = Settings::getOption(Settings::OPTION_STATS_PAGE)) {
			$content .= sprintf('<li class="cmvl-stats-link"><a href="%s">%s</a></li>',
				esc_attr(get_permalink($statsPageId)),
				Labels::getLocalized('stats_page_link')
			);
		}
		return $content;
	}
	
	
	
	static function cmvl_get_users_stats($result, $userId) {
		return array(
			'channel' => VideoStatistics::getReportByChannelForUser($userId),
			'video' => VideoStatistics::getReportByVideoForUserAndChannel($userId),
		);
	}
	
	
}
