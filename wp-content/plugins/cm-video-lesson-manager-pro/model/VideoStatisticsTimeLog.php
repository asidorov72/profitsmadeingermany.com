<?php

namespace com\cminds\videolesson\model;

/**
 * Single statistics log record for an event when a user (even partially) watched a video.
 *
 */
class VideoStatisticsTimeLog extends CommentType {
	
	const COMMENT_TYPE = 'cmvl_time_log';
	
	
	static function create(Video $video, $userId, $parentCommentId, $startSec, $stopSec) {
		$comment = new static(array(
			'comment_post_ID'      => $video->getId(),
			'comment_content'      => $startSec . ' ' . $stopSec,
			'comment_approved'     => 1,
			'comment_date'         => current_time('mysql'),
			'comment_type'         => static::COMMENT_TYPE,
			'comment_parent'       => $parentCommentId,
// 			'comment_karma'        => 0, // watched seconds
			'user_id'              => $userId,
		));
		if ($comment->save()) {
			return $comment;
		}
	}
		
	
}
