<?php

namespace com\cminds\videolesson\model;

class ProgressNotification extends CommentType {
	
	const COMMENT_TYPE = 'cmvl_progress_email';
	
	
	static function find($postId, $userId) {
		global $wpdb;
		$sql = $wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %s AND user_id = %d AND comment_type = %s", $postId, $userId, static::COMMENT_TYPE);
		$record = $wpdb->get_row($sql, ARRAY_A);
		if (!empty($record)) {
			return new static($record);
		}
	}
	
	
	static function create($postId, $userId) {
		$comment = new static(array(
			'comment_post_ID'      => $postId,
			'comment_content'      => 'sent',
			'comment_approved'     => 1,
			'comment_date'         => current_time('mysql'),
			'comment_type'         => static::COMMENT_TYPE,
			'user_id'              => $userId,
// 			'comment_author'       => 'admin',
// 			'comment_author_email'       => 'admin',
			'comment_parent' => 0,
// 			'comment_author_IP' => '127.0.0.1',
// 			'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
		));
		return $comment->save();
	}
	
}
