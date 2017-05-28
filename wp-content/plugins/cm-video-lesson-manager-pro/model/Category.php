<?php

namespace com\cminds\videolesson\model;
use com\cminds\videolesson\App;

class Category extends TaxonomyTerm {
	
	const TAXONOMY = 'cmvl_category';
	
	const META_PROGRESS_NOTIFICATION_STATUS = 'cmvl_progress_notification_status';
	const META_PROGRESS_NOTIFICATION_SENT_USERS = 'cmvl_progress_notification_sent_users';
	
	const NOTIFICATION_STATUS_ENABLED = 'enabled';
	const NOTIFICATION_STATUS_DISABLED = 'disabled';
	const NOTIFICATION_STATUS_GLOBAL = 'global';
	
	
	static function init() {
		parent::init();
		
		// Register taxonomy
		$args = array(
			'hierarchical' => TRUE,
			'labels' => static::getTaxonomyLabels(),
			'show_ui' => (App::isPro() ? true : false),
			'query_var' => TRUE,
			'show_admin_column' => (App::isPro() ? true : false),
			'post_types' => array(Channel::POST_TYPE),
			'object_type' => Channel::POST_TYPE,
			'public' => false,
			'with_front' => false,
			// 			'rewrite' => array('slug' => Settings::getOption(Settings::OPTION_PERMALINK_PREFIX) .'/course'),
		);
		register_taxonomy(static::TAXONOMY, $args['post_types'], apply_filters('cmvl_category_term_args', $args));

		// Create General category if no categories exists
		static::createFirstCategory();
	
	}
	
	
	/**
	 * Create General category if no categories exists
	 */
	static function createFirstCategory() {
		global $wpdb;
		$count = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_taxonomy WHERE taxonomy = %s", static::TAXONOMY)));
		if ($count == 0) {
			wp_insert_term(Labels::__('All Videos'), static::TAXONOMY);
		}
	}
	

	static function getTaxonomyLabels() {
		$plural = ucfirst(Labels::getLocalized('courses'));
		$singular = ucfirst(Labels::getLocalized('course'));
		return array(
			'name' => $plural,
			'singular_name' => $singular,
			'search_items' => 'Search ' . $plural,
			'popular_items' => 'Popular ' . $plural,
			'all_items' => 'All ' . $plural,
			'parent_item' => 'Parent ' . $singular,
			'parent_item_colon' => 'Parent ' . $singular . ':',
			'edit_item' => 'Edit ' . $singular,
			'update_item' => 'Update ' . $singular,
			'add_new_item' => 'Add New ' . $singular,
			'new_item_name' => 'New ' . $singular . ' Name',
			'menu_name' => $plural,
		);
	}
	

	/**
	 * Get instance
	 *
	 * @param object|int $term Term object or ID
	 * @return com\cminds\videolesson\model\Category
	 */
	static function getInstance($term) {
		return parent::getInstance($term);
	}
	
	

	/**
	 *
	 * @return array<Channel>
	 */
	function getChannels($queryArgs = array()) {
		$queryArgs = array_merge(array(
				'post_type' => Channel::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'orderby' => 'post_title',
				'order' => 'asc',
				'tax_query' => array(
						array(
								'taxonomy' => static::TAXONOMY,
								'field' => 'term_id',
								'terms' => array($this->getId()),
						)
				),
		), $queryArgs);
		$query = new \WP_Query($queryArgs);
		$channels = $query->get_posts();
		foreach ($channels as &$channel) {
			$channel = Channel::getInstance($channel);
		}
		return $channels;
	}
	
	
	function getFirstChannelPermalink() {
		if ($channel = $this->getFirstChannel()) {
			return $channel->getPermalinkForCategory($this);
		}
	}
	
	/**
	 *
	 * @return com\cminds\videolesson\model\Channel
	 */
	function getFirstChannel() {
		$channel = $this->getChannels(array('posts_per_page' => 1));
		return reset($channel);
	}
	
	
	function getEditUrl() {
		return admin_url(sprintf('edit-tags.php?action=edit&taxonomy=%s&tag_ID=%d&post_type=%s',
				Category::TAXONOMY,
				$this->getId(),
				Channel::POST_TYPE
				));
	}
	
	
	function isProgressNotificationEnabled() {
		$status = $this->getProgressNotificationStatus();
		if ($status == static::NOTIFICATION_STATUS_DISABLED) {
			return false;
		}
		else if ($status == static::NOTIFICATION_STATUS_ENABLED) {
			return true;
		} else {
			return Settings::getOption(Settings::OPTION_COURSE_PROGRESS_NOTIF_ENABLE);
		}
	}
	
	
	function getProgressNotificationStatus() {
		return $this->getTermMeta(static::META_PROGRESS_NOTIFICATION_STATUS);
	}
	
	function setProgressNotificationStatus($val) {
		return $this->setTermMeta(static::META_PROGRESS_NOTIFICATION_STATUS, $val);
	}
	
	
	function isCompletedByUser($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		
		$channel = $this->getChannels();
		foreach ($channel as $channel) {
			if (!$channel->isChannelCompleted($userId)) {
				return false;
			}
		}
		
		return true;
		
	}
	
	function getProgressNotificationSentUsers() {
		$users = $this->getTermMeta(static::META_PROGRESS_NOTIFICATION_SENT_USERS);
		if (empty($users) OR !is_array($users)) $users = array();
		return $users;
	}
	
	function setProgressNotificationSentUsers(array $val) {
		return $this->setTermMeta(static::META_PROGRESS_NOTIFICATION_SENT_USERS, $val);
	}
	
	
	function wasProgressNotificationSentToUser($userId) {
		$users = $this->getProgressNotificationSentUsers();
		return in_array($userId, $users);
	}
	
	
	function markProgressNotificationSentToUser($userId) {
		$users = $this->getProgressNotificationSentUsers();
		$users[] = $userId;
		return $this->setProgressNotificationSentUsers($users);
	}
	
	
}
