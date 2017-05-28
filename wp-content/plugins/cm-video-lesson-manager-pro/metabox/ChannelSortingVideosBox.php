<?php

namespace com\cminds\videolesson\metabox;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\controller\ChannelBackendController;
use com\cminds\videolesson\model\Video;

class ChannelSortingVideosBox extends MetaBox {

	const SLUG = 'cmvl-channel-sorting-box';
	const NAME = 'Sorting videos';
// 	const META_BOX_PRIORITY = 5;
	const CONTEXT = 'side';
	const PRIORITY = 'low';

	const FIELD_SORT = 'cmvl_channel_sort';
	const FIELD_SORT_DIR = 'cmvl_channel_sort_dir';
	
	static protected $supportedPostTypes = array(Channel::POST_TYPE);
	
	
	static protected $sortOptions = array(
		Channel::SORT_ALPHABETICAL => 'by title',
		Channel::SORT_DATE => 'by publish date',
		Channel::SORT_MODIFIED_TIME => 'by modified date',
		Channel::SORT_DURATION => 'by duration',
		Channel::SORT_MANUAL => 'manual',
	);
	
	static protected $sortDirOptions = array(
		Channel::DIR_ASC => 'ascending',
		Channel::DIR_DESC => 'descending',
	);
	
	
	static function render($post) {
		
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend');
		
		static::renderNonceField($post);
		
		$channel = Channel::getInstance($post);
		$sortOptions = static::$sortOptions;
		$sortDirOptions = static::$sortDirOptions;
		echo ChannelBackendController::loadBackendView('metabox-sorting-videos', compact('channel', 'videos', 'sortOptions', 'sortDirOptions'));
		
	}
	
	
	static function savePost($postId) {
		if ($channel = Channel::getInstance($postId)) {
			
			$sort = filter_input(INPUT_POST, static::FIELD_SORT);
			$dir = filter_input(INPUT_POST, static::FIELD_SORT_DIR);
			
			$channel->setSort($sort);
			$channel->setSortDirection($dir);
			
		}
	}
	
	
}