<?php

namespace com\cminds\videolesson\model;
use com\cminds\videolesson\App;

class Video extends PostType implements IAutocompleteModel {
	
	const POST_TYPE = 'cmvl_video';
	
	const META_SERVICE_PROVIDER = 'cmvl_service_provider';
	const META_PROVIDERS_ID = 'cmvl_providers_id';
	const META_DURATION_SEC = 'cmvl_duration_sec';
	const META_THUMBNAILS = 'cmvl_thumbnails';
	const META_API_REQUEST_DATA = 'cmvl_api-request_data';
	
	const SORT_MANUAL = 'manual';
	const SORT_DATE = 'date';
	const SORT_ALPHABETICAL = 'alphabetical';
	const SORT_PLAYS = 'plays';
	const SORT_LIKES = 'likes';
	const SORT_COMMENTS = 'comments';
	const SORT_DURATION = 'duration';
	const SORT_MODIFIED_TIME = 'modified_time';
	
	const DIR_ASC = 'asc';
	const DIR_DESC = 'desc';
		
	
	static protected $postTypeOptions = array(
		'label' => 'Video',
		'public' => true,
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_admin_bar' => true,
		'show_in_menu' => App::MENU_SLUG,
		'hierarchical' => false,
		'supports' => array('title', 'editor', 'page-attributes'),
		'has_archive' => false,
// 		'taxonomies' => array(Category::TAXONOMY),
	);
	
	
	
	static protected function getPostTypeLabels() {
		$singular = ucfirst(Labels::getLocalized('Video'));
		$plural = ucfirst(Labels::getLocalized('Videos'));
		return array(
			'name' => $plural,
			'singular_name' => $singular,
			'add_new' => sprintf(Labels::__('Add %s'), $singular),
			'add_new_item' => sprintf(Labels::__('Add New %s'), $singular),
			'edit_item' => sprintf(Labels::__('Edit %s'), $singular),
			'new_item' => sprintf(Labels::__('New %s'), $singular),
			'all_items' => $plural,
			'view_item' => sprintf(Labels::__('View %s'), $singular),
			'search_items' => sprintf(Labels::__('Search %s'), $plural),
			'not_found' => sprintf(Labels::__('No %s found'), $plural),
			'not_found_in_trash' => sprintf(Labels::__('No %s found in Trash'), $plural),
			'menu_name' => App::getPluginName()
		);
	}
	
	
	static function init() {
// 		static::$postTypeOptions['menu_position'] = 10;
		add_filter('post_type_link', array(get_called_class(), 'permalinkStructure'), 10, 4);
		parent::init();
	}
	
	
	static function permalinkStructure($post_link, $post, $leavename, $sample) {
		if ($post->post_type == static::POST_TYPE) {
			
			if ($video = static::getInstance($post) AND $channel = $video->getChannel()) {
				$post_link = add_query_arg('video', $video->getId(), $channel->getPermalink());
			}
			
		}
			
		return $post_link;
	}
	
	
	
	/**
	 * Get instance
	 *
	 * @param WP_Post|int $post Post object or ID
	 * @return com\cminds\videolesson\model\Video
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	
	/**
	 *
	 * @return \com\cminds\videolesson\model\Video
	 */
	function getVideoPost() {
		return $this;
	}
	
	
	function getServiceProvider() {
		return $this->getPostMeta(static::META_SERVICE_PROVIDER);
	}
	
	function setServiceProvider($val) {
		return $this->setPostMeta(static::META_SERVICE_PROVIDER, $val);
	}
	
	
	function getDescription() {
		return $this->getContent();
	}
	
	function setDescription($desc) {
		return $this->setContent($desc);
	}
	
	function getDurationSec() {
		return $this->getPostMeta(static::META_DURATION_SEC);
	}
	
	function setDurationSec($val) {
		return $this->setPostMeta(static::META_DURATION_SEC, $val);
	}
	
	function getDurationMin() {
		return round($this->getDurationSec()/60);
	}
	
	
	function getDurationFormatted() {
		$duration = $this->getDurationSec();
		if ($duration > 3600) return Date('H:i:s', $duration);
		else return Date('i:s', $duration);
	}
	
	function getDescriptionMarkupTags() {
		preg_match_all('~\[([^\]]+)\]\(([^\)]+)\)~', $this->getDescription($strip = false), $match, PREG_SET_ORDER);
		return $match;
	}
	
	function getChannelId() {
		return $this->getPostParent();
	}
	
	function getChannel() {
		if ($id = $this->getChannelId()) {
			return Channel::getInstance($id);
		}
	}
	
	
	function setChannelId($channelId) {
		$this->post->post_parent = $channelId;
		return $this;
	}
	
	
	function getProvidersId() {
		return $this->getPostMeta(static::META_PROVIDERS_ID);
	}
	
	function setProvidersId($id) {
		return $this->setPostMeta(static::META_PROVIDERS_ID, $id);
	}
	
	function getThumbnails() {
		return $this->getPostMeta(static::META_THUMBNAILS);
	}
	
	function setThumbnails(array $thumbnails) {
		return $this->setPostMeta(static::META_THUMBNAILS, $thumbnails);
	}
	
	
	function setAPIRequestData($data) {
		return $this->setPostMeta(static::META_API_REQUEST_DATA, $data);
	}
	
	function getAPIRequestData() {
		return $this->getPostMeta(static::META_API_REQUEST_DATA);
	}
	
	
	/**
	 * Get the smallest thumb URL with minimum width
	 * 
	 * @param number $minWidth
	 * @return string|NULL
	 */
	function getThumbUri($minWidth = 100) {
		$thumbs = $this->getThumbnails();
		$currentWidth = PHP_INT_MAX;
		$currentUrl = null;
		foreach ($thumbs as $thumb) {
			if ($thumb['w'] >= $minWidth AND $thumb['w'] < $currentWidth) {
				$currentWidth = $thumb['w'];
				$currentUrl = $thumb['url'];
			}
		}
		return $currentUrl;
	}
	
	/**
	 * Get the largest thumb URL
	 * 
	 * @return string|NULL
	 */
	function getScreenshot() {
		$thumbs = $this->getThumbnails();
		$currentWidth = 0;
		$currentUrl = null;
		foreach ($thumbs as $thumb) {
			if ($thumb['w'] > $currentWidth) {
				$currentWidth = $thumb['w'];
				$currentUrl = $thumb['url'];
			}
		}
		return $currentUrl;
	}
	
	function getName() {
		return $this->getTitle();
	}
	
	function getAutocompleteResults($search, $orderby, $order, $limit) {
		$posts = get_posts(array(
				's' => $search,
				'order' => $order,
				'orderby' => $orderby,
				'limit' => $limit,
				'post_type' => static::POST_TYPE,
		));
		$result = array_map(array(__CLASS__, 'getInstance'), $posts);
		$result = array_map(function(Video $video) {
			return array(
					'id' => $video->getId(),
					'name' => $video->getName(),
					'editUrl' => $video->getEditUrl(),
					'permalink' => $video->getPermalink(),
					'thumb' => $video->getThumbUri(),
			);
		},  $result);
		return $result;
	}
	
	
	function isVisible() {
		return ($this->getPostStatus() == 'publish' AND $this->getPostParent());
	}
	
	
	static function getAll($onlyVisible = true, $status = 'publish', $provider = null) {
		$args = array('post_type' => static::POST_TYPE, 'post_status' => $status);
		if ($provider) {
			$args['meta_key'] = static::META_SERVICE_PROVIDER;
			$args['meta_value'] = $provider;
		}
		$posts = get_posts($args);
		$videos = array_map(array(__CLASS__, 'getInstance'), $posts);
		if ($onlyVisible) {
			$videos = array_filter($videos, function(Video $video) {
				return $video->isVisible();
			});
		}
		return $videos;
	}
	
	function getPermalink() {
		if ($channel = $this->getChannel()) {
			return add_query_arg('video', urlencode($this->getId()), $channel->getPermalink());
		} else {
			return parent::getPermalink();
		}
	}
	
	
	static function searchIds($str) {
		global $wpdb;
		$like = '%' . $str . '%';
		return $wpdb->get_col($wpdb->prepare("SELECT DISTINCT p.ID
				FROM $wpdb->posts p
				WHERE post_type = %s
				AND post_status = %s
				AND post_parent <> '' AND post_parent IS NOT NULL
				AND (post_title LIKE %s OR post_content LIKE %s)",
			Video::POST_TYPE,
			'publish',
			$like, $like
		));
	}
	
	
	function isProgressNotificationEnabled() {
		$status = $this->getChannel()->getVideosProgressNotificationStatus();
		if ($status == Channel::NOTIFICATION_STATUS_DISABLED) {
			return false;
		}
		else if ($status == Channel::NOTIFICATION_STATUS_ENABLED) {
			return true;
		} else {
			return Settings::getOption(Settings::OPTION_VIDEO_PROGRESS_NOTIF_ENABLE);
		}
	}
	
	
	function canView($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		return apply_filters('cmvl_video_can_view', $this->getChannel()->canView($userId), $this, $userId);
	}
	
	
	
}