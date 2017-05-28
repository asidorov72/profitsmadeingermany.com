<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\App;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\model\VideoStatistics;

class UserReportsController extends Controller {
	
	protected static $actions = array(
// 			'admin_menu' => array('priority' => 11),
	);
	
	

	static function admin_menu() {
		add_submenu_page(App::MENU_SLUG, App::getPluginName() . ' Users Statistics', 'Users Statistics', 'manage_options',
				self::getMenuSlug(), array(get_called_class(), 'render'));
	}
	
	static function getMenuSlug() {
		return App::MENU_SLUG . '-stats';
	}
	
	
	static function render() {
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend');
		echo self::loadView('backend/template', array(
				'title' => App::getPluginName() . ' Statistics',
				'nav' => self::getBackendNav(),
				'content' => self::getContent(),
		));
	}
	
	
	
	static function getContent() {
		
		$userId = filter_input(INPUT_GET, 'user_id');
		$channelId = filter_input(INPUT_GET, 'channel_id');
		
		if ($userId AND $user = get_userdata($userId)) {
			if ($channelId AND $channel = Channel::getInstance($channelId)) {
				$videos = $channel->getVideos();
				return self::loadBackendView('stats-user-channel', array(
						'channel' => $channel,
						'videos' => $videos,
						'user' => $user,
						'data' => VideoStatistics::getReportByVideoForUserAndChannel($userId, $channel)
				));
			} else {
				return self::loadBackendView('stats-user', array(
						'categories' => Category::getAll(),
						'user' => $user,
						'data' => VideoStatistics::getReportByChannelForUser($userId),
						'detailsUrl' => add_query_arg(array('page' => static::getMenuSlug(), 'user_id' => $userId), admin_url('admin.php')),
				));
			}
		} else {
				
			$pagination = array(
					'limit' => 20,
					'page' => isset($_GET['p']) ? $_GET['p'] : 1,
					'firstPageUrl' => admin_url('admin.php?page=cmvl-stats'),
					'prevPageUrl' => null,
					'nextPageUrl' => null,
					'lastPageUrl' => null,
					'lastPage' => 1,
			);
			
			$data = VideoStatistics::getReportByUser();
			
			
			if ($pagination['page'] > 1) {
				$pagination['prevPageUrl'] = add_query_arg('p', $pagination['page']-1, $pagination['firstPageUrl']);
			}
			if ($pagination['page'] < $pagination['lastPage']) {
				$pagination['nextPageUrl'] = add_query_arg('p', $pagination['page']+1, $pagination['firstPageUrl']);
			}
			$pagination['lastPageUrl'] = add_query_arg('p', $pagination['lastPage'], $pagination['firstPageUrl']);
				
			return self::loadBackendView('stats-index', array(
					'channelsSummaryDurationMin' => Channel::getChannelsSummaryDurationMin(),
					'channelsSummaryDurationSec' => Channel::getChannelsSummaryDurationSec(),
					'data' => $data,
					'pagination' => $pagination,
					'userDetailedStatsUrl' => add_query_arg(array('page' => static::getMenuSlug()), admin_url('admin.php')),
			));
		}
	}
	
	
	
}