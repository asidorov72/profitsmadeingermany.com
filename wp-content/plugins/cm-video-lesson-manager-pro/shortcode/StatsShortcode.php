<?php

namespace com\cminds\videolesson\shortcode;

use com\cminds\videolesson\model\Stats;

use com\cminds\videolesson\controller\StatsController;

use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\controller\ChannelController;
use com\cminds\videolesson\model\VideoStatistics;


class StatsShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cmvl-stats';
	
	
	static function shortcode($atts) {
		
		$atts = shortcode_atts(apply_filters('cmvl_stats_shortcode_atts', array(
			'permalinks' => 1,
		), $atts), $atts);
		
		if (is_user_logged_in() AND $user = get_userdata(get_current_user_id())) {
			ChannelController::loadAssets();
			
// 			$channels = Channel::getAll();
// 			$videoData = array();
// 			foreach ($channels as $channel) {
// 				$videoData[$channel->getId()] = VideoStatistics::getReportByVideoForUserAndChannel($user->ID, $channel);
// 			}
			
			return StatsController::loadFrontendView('stats-shortcode', array(
				'categories' => Category::getAll(),
				'user' => $user,
				'channelData' => VideoStatistics::getReportByChannelForUser($user->ID),
				'videoData' => VideoStatistics::getReportByVideoForUserAndChannel($user->ID),
				'atts' => $atts,
			));
		}
	}
	
	
}
