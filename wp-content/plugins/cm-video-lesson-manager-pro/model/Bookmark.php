<?php

namespace com\cminds\videolesson\model;

class Bookmark extends Model {
	
	const OPTION_BOOKMARKS_PAGE = 'cmvl_bookmarks_page';
	
	const META_POST_BOOKMARK = 'cmvl_bookmark_user_id';

	protected $video;

	
	function __construct(Video $video) {
		$this->video = $video;
	}
	
	
	function getUsersIdsForVideo() {
		return get_post_meta($this->video->getId(), static::META_POST_BOOKMARK, $single = false);
	}
	
	
	function isBookmarked($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		return in_array($userId, $this->getUsersIdsForVideo());
	}
	
	
	function addBookmark($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		if (!$this->isBookmarked($userId)) {
			add_post_meta($this->video->getId(), static::META_POST_BOOKMARK, $userId, $unique = false);
		}
		return $this;
	}
	
	
	function removeBookmark($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		delete_post_meta($this->video->getId(), static::META_POST_BOOKMARK, $userId);
		return $this;
	}
	
	
	
	static function getUserBookmarksVideosIds($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		global $wpdb;
		return $wpdb->get_col($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %d", static::META_POST_BOOKMARK, $userId));
	}
	
	
	static function searchIds($str, $userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		global $wpdb;
		$like = '%' . $str . '%';
		return $wpdb->get_col($wpdb->prepare("SELECT DISTINCT p.ID
				FROM $wpdb->postmeta pm
				JOIN $wpdb->posts p ON p.ID = pm.post_id
				WHERE pm.meta_key = %s
				AND pm.meta_value = %d
				AND p.post_type = %s
				AND (p.post_title LIKE %s OR p.post_content LIKE %s)",
			static::META_POST_BOOKMARK, $userId, Video::POST_TYPE, $like, $like));
	}
	
	
}
