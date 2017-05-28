<?php

namespace com\cminds\videolesson\model;

class Note extends CommentType {
	
	const COMMENT_TYPE = 'cmvl_user_note';
	
	
	static function find($videoId, $userId) {
		$comments = get_comments(array('post_id' => $videoId, 'type' => static::COMMENT_TYPE, 'user_id' => $userId));
		if (!empty($comments)) {
			return new static(reset($comments));
		}
	}
	
	
	static function createNote($videoId, $userId, $content) {
		$comment = new static(array(
			'comment_post_ID'      => $videoId,
			'comment_content'      => $content,
			'comment_approved'     => 1,
			'comment_date'         => current_time('mysql'),
			'comment_type'         => static::COMMENT_TYPE,
			'user_id'              => $userId,
		));
		return $comment->save();
	}
	
	
	
	static function createOrUpdateNote($videoId, $userId, $content) {
		$note = static::find($videoId, $userId);
		if (!empty($note)) {
			$note->updateNote($content);
		} else {
			static::createNote($videoId, $userId, $content);
		}
	}
	
	
	static function getNoteContent($videoId, $userId) {
		if ($noteObj = static::find($videoId, $userId)) {
			return $noteObj->getContent();
		} else {
			return '';
		}
	}
	
	
	function updateNote($content) {
		return $this->setContent($content)->save();
	}
	

	static function searchIds($str, $userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		global $wpdb;
		$like = '%' . $str . '%';
		$sql = $wpdb->prepare("SELECT DISTINCT p.ID
				FROM $wpdb->comments c
				JOIN $wpdb->posts p ON p.ID = c.comment_post_id
				WHERE c.comment_type = %s AND c.user_id = %d AND c.comment_content <> '' AND c.comment_content IS NOT NULL
				AND (c.comment_content LIKE %s OR p.post_title LIKE %s OR p.post_content LIKE %s)",
			static::COMMENT_TYPE, $userId, $like, $like, $like);
		$res = $wpdb->get_col($sql);
		return $res;
	}
	
	
}
