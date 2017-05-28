<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\App;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\model\VideoStatistics;

class ChannelReportsController extends Controller {
	
	protected static $filters = array();
	protected static $actions = array(
// 		'admin_menu' => array('priority' => 12),
	);
	
	
	static function admin_menu() {
		add_submenu_page(App::MENU_SLUG, App::getPluginName() . ' Lessons Reports', 'Lessons Reports', 'manage_options',
			self::getMenuSlug(), array(get_called_class(), 'render'));
	}
	
	static function getMenuSlug() {
		return App::MENU_SLUG . '-channel-reports';
	}
	
	
	static function render() {
		
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend');
		echo self::loadView('backend/template', array(
			'title' => App::getPluginName() . ' Lessons Reports',
			'nav' => self::getBackendNav(),
			'content' => self::getContent(),
		));
	}
	
	
	
	static function getContent() {
		$channelId = filter_input(INPUT_GET, 'channel_id');
		if ($channelId AND $channel = Channel::getInstance($channelId)) {
			$data = VideoStatistics::getReportForChannelByVideo($channel);
			return self::loadBackendView('report-channel', compact('data', 'channel'));
		} else {
			$data = VideoStatistics::getReportByChannel();
			$showVideosUrl = admin_url('admin.php?page='. self::getMenuSlug());
			return self::loadBackendView('report-index', compact('data', 'showVideosUrl'));
		}
	}
	
	
	
}
