<?php

namespace com\cminds\videolesson\addon\eddpay\model;

use com\cminds\videolesson\addon\eddpay\helper\TimeHelper;
use com\cminds\videolesson\addon\eddpay\App;

class Download extends PostType {
	
	const POST_TYPE = 'download';
	const CATEGORY_TAXONOMY = 'download_category';
	const CATEGORY_NAME = 'Video Lessons';
	
	const META_EDD_PRICE = 'edd_price';
	const META_PAID_POST_ID = 'cmvl_paid_post_id';
	const META_SUBSCRIPTION_TIME_SEC = 'cmvl_subscription_time_sec';
	
	
	static function registerPostType() {
		// don't
	}
	
	/**
	 *
	 * @param unknown $postId
	 * @return Download
	 */
	static function getInstance($post) {
		if (is_scalar($post)) {
			if (!empty(static::$instances[$post])) return static::$instances[$post];
			else if (is_numeric($post)) $post = new \EDD_Download($post);
		}
		$postType = static::POST_TYPE;
		if (!empty($post) AND is_object($post) AND (empty($postType) OR $post->post_type == $postType)) {
			if (empty(static::$instances[$post->ID])) {
				static::$instances[$post->ID] = new static($post);
			}
			return static::$instances[$post->ID];
		}
	}
	
	
	
	static function create($subscriptionTimeSec, $price, $paidPostId = 0) {
		
		if (!App::isAvailable()) return;
		
		$postarr = array(
			'post_title'	 => static::createDownloadName($subscriptionTimeSec, $paidPostId),
			'post_status'	 => 'publish',
		);
		
		$eddDownload = new \EDD_Download();
		$result		 = @ $eddDownload->create( $postarr );
		
		if ( $result ) {
			
			$postId = intval( $eddDownload->ID );
			$download = new static($eddDownload);
			
			$download->setPrice($price);
			$download->setPaidPostId($paidPostId);
			$download->setSubscriptionTime($subscriptionTimeSec);
			if ($categoryTermId = static::getVideoLessonsCategoryId()) {
				wp_set_post_terms($download->getId(), array($categoryTermId), static::CATEGORY_TAXONOMY, $append = false);
			}
			
			return $download;
			
		}
		
	}
	
	
	static function getVideoLessonsCategoryId() {
		global $wpdb;
		
		// We use manual queries since EDD registers taxonomy only after the init hook
		// and we need this in the plugins_loaded which caused an "invalid taxonomy" error.
		
		$termId = $wpdb->get_var($wpdb->prepare("SELECT tt.term_id FROM $wpdb->term_taxonomy tt
			JOIN $wpdb->terms t ON t.term_id = tt.term_id
			WHERE tt.taxonomy = %s AND t.name = %s", Download::CATEGORY_TAXONOMY, Download::CATEGORY_NAME));
		
		if ($termId) {
			return $termId;
		} else { // Insert term
			
			$termResult = $wpdb->insert($wpdb->terms, array('name' => Download::CATEGORY_NAME, 'slug' => sanitize_title(Download::CATEGORY_NAME)));
			if ($termResult) {
				$term_id = $wpdb->insert_id;
				$ttResult = $wpdb->insert($wpdb->term_taxonomy, array('taxonomy' => Download::CATEGORY_TAXONOMY, 'term_id' => $term_id));
				if ($ttResult) {
					return $term_id;
				}
			}
			
		}
		
	}
	
	
	function setPrice($price) {
		return $this->setPostMeta(static::META_EDD_PRICE, edd_sanitize_amount( floatval($price) ));
	}
	
	
	function getPrice() {
		return $this->getPostMeta(static::META_EDD_PRICE);
	}
	
	
	function setPaidPostId($postId) {
		return $this->setPostMeta(static::META_PAID_POST_ID, intval($postId));
	}
	
	
	function setSubscriptionTime($timeSec) {
		return $this->setPostMeta(static::META_SUBSCRIPTION_TIME_SEC, $timeSec);
	}
	
	
	function getSubscriptionTimeSec() {
		return $this->getPostMeta(static::META_SUBSCRIPTION_TIME_SEC);
	}
	
	
	function getSubscriptionPeriodNumber() {
		$seconds = $this->getSubscriptionTimeSec();
		$period = explode(' ', TimeHelper::seconds2period($seconds));
		return reset($period);
	}
	
	
	function getSubscriptionPeriodUnit() {
		$seconds = $this->getSubscriptionTimeSec();
		$period = explode(' ', TimeHelper::seconds2period($seconds));
		$unit = end($period);
		if ($unit == 'minute' OR $unit == 'minutes') {
			return 'min';
		} else {
			return substr($unit, 0, 1);
		}
	}
	
	
	static function createDownloadName($subscriptionTimeSec, $paidPostId = null) {
		$result = 'CM Video Lessons';
		$periodLabel = TimeHelper::seconds2period($subscriptionTimeSec);
		if ($paidPostId AND $post = get_post($paidPostId)) {
			if ($post->post_type == Channel::POST_TYPE) {
				$type = 'Channel';
			}
			else if ($post->post_type == Video::POST_TYPE) {
				$type = 'Video';
			} else {
				$type = $post->post_type;
			}
			$result .= ' '. $type .': ' . $post->post_title;
		} else {
			$result .= ' all videos';
		}
		$result .= ' ('. $periodLabel .')';
		return $result;
	}
	
	
	static function getAllGlobal() {
		return static::getForPaidPost(0);
	}
	
	
	static function getForPaidPost($postId) {
		$ids = static::getIdsForPaidPost($postId);
		$results = array();
		foreach ($ids as $id) {
			$results[] = static::getInstance($id);
		}
		return $results;
	}
	
	
	static function getIdsForPaidPost($postId) {
		global $wpdb;
		$ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts p
			JOIN $wpdb->postmeta m ON m.post_id = p.ID AND m.meta_key = %s AND m.meta_value = %d
			WHERE p.post_type = %s AND p.post_status = 'publish'
			ORDER BY p.menu_order ASC",
			static::META_PAID_POST_ID, intval($postId), static::POST_TYPE));
		return $ids;
	}
	
	
	function getEditUrl() {
		return admin_url(sprintf('post.php?action=edit&post=%d',
			$this->getId()
		));
	}
	
	
	function archive() {
		delete_post_meta($this->getId(), static::META_PAID_POST_ID);
		$this->setPostStatus('trash');
	}
	
	
	
	function setPostStatus($status) {
		$my_post = array();
		$my_post['ID'] = $this->getId();
		$my_post['post_status'] = $status;
		wp_update_post( $my_post );
		$this->post->post_status = $status;
	}
	
	
	
	static function archiveMissingIds($paidPostId, $stayIds) {
		$currentIds = static::getIdsForPaidPost($paidPostId);
		$missingIds = array_diff($currentIds, $stayIds);
		foreach ($missingIds as $id) {
			if ($download = static::getInstance($id)) {
				$download->archive();
			}
		}
	}
	
	
	function getPaidPostId() {
		return $this->getPostMeta(static::META_PAID_POST_ID);
	}
	
	
	function isVideoLessonsPrice() {
		$time = $this->getSubscriptionTimeSec();
		return (!empty($time) AND $time > 0);
	}
	
	
}
