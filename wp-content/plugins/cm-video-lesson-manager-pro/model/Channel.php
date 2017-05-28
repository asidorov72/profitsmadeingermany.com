<?php

namespace com\cminds\videolesson\model;
use com\cminds\videolesson\App;

class Channel extends PostType implements IAutocompleteModel {
	
	const POST_TYPE = 'cmvl_channel';
	const INIT_PRIORITY = 6;
	
	const META_SORT = 'cmvl_channel_sort';
	const META_SORT_DIR = 'cmvl_channel_sort_dir';
	const META_CHANNEL_PROGRESS_NOTIFICATION_STATUS = 'cmvl_channel_progress_notif_status';
	const META_VIDEOS_PROGRESS_NOTIFICATION_STATUS = 'cmvl_videos_progress_notif_status';
	const META_PAGE_TEMPLATE = 'cmvl_page_template';
	const META_VIDEOS_LAYOUT = 'cmvl_video_layout';
	const META_PLAYLIST_LAYOUT = 'cmvl_playlist_layout';
	
	const NOTIFICATION_STATUS_ENABLED = 'enabled';
	const NOTIFICATION_STATUS_DISABLED = 'disabled';
	const NOTIFICATION_STATUS_GLOBAL = 'global';
	
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
	
	static protected $sortColumnsMap = array(
		self::SORT_MANUAL => 'menu_order',
		self::SORT_DATE => 'post_date',
		self::SORT_MODIFIED_TIME => 'post_modified',
		self::SORT_ALPHABETICAL => 'post_title',
		self::SORT_DURATION => 'CAST(video_duration.meta_value as SIGNED INTEGER)',
	);
	
	
	static protected $postTypeOptions = array(
		'label' => 'Lesson',
		'public' => true,
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_admin_bar' => true,
		'show_in_menu' => App::MENU_SLUG,
		'hierarchical' => false,
		'supports' => array('title', 'editor', 'page-attributes', 'thumbnail'),
		'has_archive' => false,
// 		'taxonomies' => array(Category::TAXONOMY),
	);
	
	
	
	static protected function getPostTypeLabels() {
		$singular = ucfirst(Labels::getLocalized('Lesson'));
		$plural = ucfirst(Labels::getLocalized('Lessons'));
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
// 		static::$postTypeOptions['menu_position'] = 15;
		static::$postTypeOptions['rewrite'] = array('slug' => Settings::getOption(Settings::OPTION_PERMALINK_PREFIX) . '/lesson');
		parent::init();
	}
	
	
	
	
	static function getArchivePermalink() {
		return get_post_type_archive_link(static::POST_TYPE);
	}
	
	
	
	/**
	 * Get instance
	 *
	 * @param WP_Post|int $post Post object or ID
	 * @return com\cminds\videolesson\model\Channel
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	
	function getName() {
		return $this->getTitle();
	}
	
	
	function getVideosIds($onlyVisible = false, $pagination = array()) {
		global $wpdb;
		
		$sort = $this->getSort();
		if (isset(static::$sortColumnsMap[$sort])) {
			$sortSql = static::$sortColumnsMap[$sort]; 
		} else {
			$sortSql = 'menu_order';
		}
		
		$order = $this->getSortDirection();
		
		$limit = '';
		if (!empty($pagination) AND !empty($pagination['per_page'])) {
			if (empty($pagination['page'])) $pagination['page'] = 1;
			$offset = $pagination['per_page'] * ($pagination['page'] - 1);
			$limit = ' LIMIT ' . $offset .', '. $pagination['per_page'] .' ';
		}
		
		return $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts v
				LEFT JOIN $wpdb->postmeta video_duration ON video_duration.post_id = v.ID AND video_duration.meta_key = %s
				WHERE v.post_type = %s AND v.post_parent = %d",
				Video::META_DURATION_SEC,
				Video::POST_TYPE,
				$this->getId()
			) . " ORDER BY $sortSql $order " . $limit);
	}
	
	
	function getVideos($onlyVisible = true, $pagination = array()) {
		$videos = array_map(array(App::namespaced('model\Video'), 'getInstance'), $this->getVideosIds($onlyVisible, $pagination));
		if ($onlyVisible) {
			$videos = array_filter($videos, function(Video $video) {
				return $video->isVisible();
			});
		}
		return $videos;
	}
	
	function getDescription() {
		return $this->getContent();
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
		$result = array_map(function(Channel $channel) {
			return array(
				'id' => $channel->getId(),
				'name' => $channel->getName(),
				'editUrl' => $channel->getEditUrl(),
				'permalink' => $channel->getPermalink(),
			);
		},  $result);
		return $result;
	}
	
	
	
	function getPermalinkForCategory(Category $category) {
		return $this->getPermalink() . '?' . Category::TAXONOMY . '=' . $category->getId();
		// 		return trailingslashit($category->getPermalink() . $this->getSlug());
	}
	

	function getSort() {
		$value = get_post_meta($this->getId(), self::META_SORT, true);
		if (empty($value)) {
			return Video::SORT_MANUAL;
		} else {
			return $value;
		}
	}
	
	
	function getTotalVideos() {
		return count($this->getVideosIds());
	}
	
	
	function setSort($value) {
		update_post_meta($this->getId(), self::META_SORT, $value);
	}
	
	
	function getSortDirection() {
		$value = get_post_meta($this->getId(), self::META_SORT_DIR, true);
		if (empty($value)) {
			return Video::DIR_ASC;
		} else {
			return $value;
		}
	}
	
	function setSortDirection($value) {
		update_post_meta($this->getId(), self::META_SORT_DIR, $value);
	}
	
	
	function canView($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		return apply_filters('cmvl_channel_can_view', static::checkViewAccess(), $this, $userId);
	}

	protected function sumDuration($videos) {
		$duration = 0;
		foreach ($videos as $video) {
			$duration += $video->getDurationSec();
		}
		return $duration;
	}
	
	
	protected function sumDurationMin($videos) {
		$duration = 0;
		foreach ($videos as $video) {
			$duration += round($video->getDurationSec()/60);
		}
		return $duration;
	}

	function getDurationSec() {
		global $wpdb;
		
		return intval($wpdb->get_var($wpdb->prepare("SELECT SUM(meta_value) FROM $wpdb->postmeta pm
				JOIN $wpdb->posts p ON p.ID = pm.post_id
				WHERE post_parent = %d
					AND post_type = %s
					AND post_status = %s
					AND meta_key = %s",
				$this->getId(),
				Video::POST_TYPE,
				'publish',
				Video::META_DURATION_SEC
		)));
		
	}
	
	
	
	function getDurationMin() {
		global $wpdb;
		
		$sql = $wpdb->prepare("SELECT SUM(ROUND(meta_value/60)) FROM $wpdb->postmeta pm
				JOIN $wpdb->posts p ON p.ID = pm.post_id
				WHERE post_parent = %d
				AND post_type = %s
				AND post_status = %s
				AND meta_key = %s",
				$this->getId(),
				Video::POST_TYPE,
				'publish',
				Video::META_DURATION_SEC
		);
		
		return intval($wpdb->get_var($sql));
		
	}
	
	
	static function getChannelsSummaryDurationSec() {
		global $wpdb;
		
		return intval($wpdb->get_var($wpdb->prepare("SELECT SUM(meta_value) FROM $wpdb->postmeta pm
				JOIN $wpdb->posts p ON p.ID = pm.post_id
				JOIN $wpdb->posts ch ON ch.ID = p.post_parent
				WHERE p.post_type = %s
				AND p.post_status = %s
				AND ch.post_type = %s
				AND ch.post_status = %s
				AND meta_key = %s",
				Video::POST_TYPE,
				'publish',
				Channel::POST_TYPE,
				'publish',
				Video::META_DURATION_SEC
			)));
		
	}
	
	
	static function getChannelsSummaryDurationMin() {
		global $wpdb;
		
		$sql = $wpdb->prepare("SELECT SUM(ROUND(meta_value/60)) FROM $wpdb->postmeta pm
				JOIN $wpdb->posts p ON p.ID = pm.post_id
				JOIN $wpdb->posts ch ON ch.ID = p.post_parent
				WHERE p.post_type = %s
				AND p.post_status = %s
				AND ch.post_type = %s
				AND ch.post_status = %s
				AND meta_key = %s",
				Video::POST_TYPE,
				'publish',
				Channel::POST_TYPE,
				'publish',
				Video::META_DURATION_SEC
		);
		
// 		var_dump($sql);
		
		return intval($wpdb->get_var($sql));
		
// 		return $wpdb->get_var($wpdb->prepare("SELECT SUM(m.meta_value) FROM $wpdb->postmeta m
// 				JOIN $wpdb->posts p ON m.post_id = p.ID
// 				WHERE meta_key = %s AND p.post_status = 'publish'",
// 				self::META_DURATION_MIN));
	}
	
	static function checkViewAccess() {
		switch (Settings::getOption(Settings::OPTION_ACCESS_VIEW)) {
			case Settings::ACCESS_LOGGED_IN_USERS:
				return is_user_logged_in();
			default:
			case Settings::ACCESS_EVERYONE:
				return true;
		}
	}
	
	
	function getCategories() {
		$terms = wp_get_post_terms($this->getId(), Category::TAXONOMY);
		if (empty($terms)) {
			$this->addDefaultCategory();
			$terms = wp_get_post_terms($this->getId(), Category::TAXONOMY);
		}
		foreach ($terms as &$term) {
			$term = Category::getInstance($term);
		}
		return $terms;
	}
	
	
	function addDefaultCategory() {
		$term = get_term('General', Category::TAXONOMY);
		if (empty($term)) {
			$terms = get_terms(array(Category::TAXONOMY), array('hide_empty' => false));
			if (!empty($terms)) {
				$term = reset($terms);
			}
		}
		if (!empty($term)) {
			wp_set_post_terms($this->getId(), $term->term_id, Category::TAXONOMY);
		}
	}
	
	function isProgressNotificationEnabled() {
		$status = $this->getChannelProgressNotificationStatus();
		if ($status == self::NOTIFICATION_STATUS_DISABLED) {
			return false;
		}
		else if ($status == self::NOTIFICATION_STATUS_ENABLED) {
			return true;
		} else {
			return Settings::getOption(Settings::OPTION_LESSON_PROGRESS_NOTIF_ENABLE);
		}
	}
	
	
	function getChannelProgressNotificationStatus() {
		return get_post_meta($this->getId(), self::META_CHANNEL_PROGRESS_NOTIFICATION_STATUS, true);
	}
	
	function setChannelProgressNotificationStatus($val) {
		return update_post_meta($this->getId(), self::META_CHANNEL_PROGRESS_NOTIFICATION_STATUS, $val);
	}
	
	
	function getVideosProgressNotificationStatus() {
		return get_post_meta($this->getId(), self::META_VIDEOS_PROGRESS_NOTIFICATION_STATUS, true);
	}
	
	function setVideosProgressNotificationStatus($val) {
		return update_post_meta($this->getId(), self::META_VIDEOS_PROGRESS_NOTIFICATION_STATUS, $val);
	}
	
	
	function getThumbUri($minWidth = 100) {
		
		$url = $this->getFeaturedImage();
		if (empty($url)) {
			$videos = $this->getVideos();
			if ($video = reset($videos)) {
				$url = $video->getThumbUri($minWidth);
			}
		}
		
		return $url;
		
	}
	
	
	function isChannelCompleted($userId = null) {
		$videosIds = $this->getVideosIds();
		if (is_array($videosIds) AND $videoId = reset($videosIds)) {
			if ($stats = VideoStatistics::getByVideoForUserOrCreate($videoId, $userId)) {
				return $stats->isChannelCompleted($userId);
			}
		}
	}
	
	
	function getPageTemplate() {
		return $this->getPostMeta(static::META_PAGE_TEMPLATE);
	}
	
	function setPageTemplate($value) {
		return $this->setPostMeta(static::META_PAGE_TEMPLATE, $value);
	}
	
	function getVideosLayout() {
		return $this->getPostMeta(static::META_VIDEOS_LAYOUT);
	}
	
	function getVideosLayoutOrGlobal() {
		$val = $this->getVideosLayout();
		if (empty($val)) {
			$val = Settings::getOption(Settings::OPTION_VIDEOS_LAYOUT);
		}
		return $val;
	}
	
	function setVideosLayout($value) {
		return $this->setPostMeta(static::META_VIDEOS_LAYOUT, $value);
	}
	
	function getPlaylistLayout() {
		return $this->getPostMeta(static::META_PLAYLIST_LAYOUT);
	}
	
	function getPlaylistLayoutOrGlobal() {
		$val = $this->getPlaylistLayout();
		if (empty($val)) {
			$val = Settings::getOption(Settings::OPTION_PLAYLIST_LAYOUT);
		}
		return $val;
	}
	
	function setPlaylistLayout($value) {
		return $this->setPostMeta(static::META_PLAYLIST_LAYOUT, $value);
	}
	
	
	static function getAll($onlyVisible = true) {
		$args = array('post_type' => static::POST_TYPE, 'posts_per_page' => -1);
		if ($onlyVisible) $args['post_status'] = 'publish';
		$posts = get_posts($args);
		$result = array();
		foreach ($posts as $post) {
			$result[$post->ID] = static::getInstance($post);
		}
		return $result;
	}
	
	
}