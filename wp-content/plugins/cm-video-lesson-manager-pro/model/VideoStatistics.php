<?php

namespace com\cminds\videolesson\model;

/**
 * Aggregated statistics data for specific user and video.
 *
 */
class VideoStatistics extends CommentType {
	
	const COMMENT_TYPE = 'cmvl_video_stats';
	
	
	static function getByVideoForUserOrCreate($videoId, $userId) {
		$comments = get_comments(array(
			'type' => static::COMMENT_TYPE,
			'user_id' => $userId,
			'post_id' => $videoId,
		));
		$comment = reset($comments);
		if ($comment) {
			return new static($comment);
		} else {
			if ($comment = static::create($videoId, $userId)) {
				return $comment;
			}
		}
	}
	
	
	static function create($videoId, $userId) {
		$comment = new static(array(
			'comment_post_ID'      => $videoId,
			'comment_content'      => '',
			'comment_approved'     => 1,
			'comment_date'         => current_time('mysql'),
			'comment_type'         => static::COMMENT_TYPE,
			'comment_karma'        => 0, // watched seconds
			'user_id'              => $userId,
		));
		if ($comment->save()) {
			return $comment;
		}
	}
	
	
	
	function getUserStatsIntervals() {
		$intervals = $this->getContent();
		if (empty($intervals)) return array();
		$intervals = explode(PHP_EOL, $intervals);
		foreach ($intervals as &$interval) {
			$interval = explode(' ', $interval);
		}
		if (empty($intervals) OR !is_array($intervals)) $intervals = array();
		return $intervals;
	}
	
	
	function setUserStatsIntervals($intervals) {
		if (empty($intervals) OR !is_array($intervals)) $intervals = array();
		foreach ($intervals as &$interval) {
			$interval = implode(' ', $interval);
		}
		$intervals = implode(PHP_EOL, $intervals);
		$this->setContent($intervals);
		return $this->save();
	}
	
	
	function addUserStatsInterval($start, $stop) {
		
		$originalStart = $start;
		$originalStop = $stop;
	
		$intervals = $this->getUserStatsIntervals();
		$toRemove = array();
	
		foreach ($intervals as $i => $interval) {
			$common = false;
			if ($interval[0] <= $start && $start <= $interval[1]) { // start is in the middle
				$start = $interval[0];
				$common = true;
			}
			if ($interval[0] <= $stop && $stop <= $interval[1]) { // stop is in the middle
				$stop = $interval[1];
				$common = true;
			}
			if ($start <= $interval[0] && $interval[1] <= $stop) { // new interval contains old interval
				$common = true;
			}
			if ($common) {
				$toRemove[] = $i;
			}
		}
	
		if (count($toRemove) > 0) {
			foreach ($toRemove as $i => $index) {
				array_splice($intervals, $index-$i, 1);
			}
		}
	
		$intervals[] = array($start, $stop);
		$res = $this->setUserStatsIntervals($intervals);
// 		var_dump($res);
	
		$percentSum = 0;
		foreach ($intervals as $interval) {
			$percentSum += ($interval[1] - $interval[0]);
		}
		$durationSec = $this->getVideo()->getDurationSec();
		$this->setUserStatsWatchedSeconds($durationSec * $percentSum/100);
		$this->save();
		
		if (Settings::getOption(Settings::OPTION_VIDEO_STATS_DETAILED_LOG_ENABLE)) {
			$originalStartSec = $originalStart * $durationSec / 100;
			$originalStopSec = $originalStop * $durationSec / 100;
			VideoStatisticsTimeLog::create($this->getVideo(), $this->getAuthorId(), $this->getId(), $originalStartSec, $originalStopSec);
		}
		
		do_action('cmvl_stats_interval_added', $this, $originalStart, $originalStop);
	
		return $this;
	
	}
	

	function getUserStatsWatchedSeconds() {
		return $this->comment->comment_karma;
	}
	
	
	function setUserStatsWatchedSeconds($val) {
		$duration = $this->getVideo()->getDurationSec();
		if (($duration - $val) <= Settings::getOption(Settings::OPTION_LESSONS_PROGRESS_ROUND_UP_SECONDS)) {
			$val = $duration;
		}
		$this->comment->comment_karma = $val;
		return $this;
	}
	
	
	function getVideo() {
		return Video::getInstance($this->getVideoId());
	}
	
	
	function getVideoId() {
		return $this->getPostId();
	}
	
	

	static function clearStats($channelId) {
		global $wpdb;
		$ids = $wpdb->get_results($wpdb->prepare("SELECT comment_ID FROM $wpdb->comments c
				JOIN $wpdb->posts v ON v.ID = c.comment_post_ID AND v.post_type = %s
				JOIN $wpdb->posts c ON c.ID = v.post_parent AND c.post_type = %s
				WHERE c.comment_type = %s AND c.ID = %d",
				Video::POST_TYPE, Channel::POST_TYPE, static::COMMENT_TYPE, $channelId));
		foreach ($ids as $id) {
			wp_delete_comment($id, $force = true);
		}
	}
	
	
	function isVideoCompleted() {
		$watched = $this->getUserStatsWatchedSeconds();
		$duration = $this->getVideo()->getDurationSec();
		return (($duration - $watched) <= Settings::getOption(Settings::OPTION_COURSE_PROGRESS_NOTIF_LACK_SECONDS));
	}
	
	
	function isChannelCompleted($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		/* @var $channel Channel */
		$channel = $this->getVideo()->getChannel();
		$videos = $channel->getVideos();
		$result = 0;
		foreach ($videos as $video) {
			/* @var $video Video */
			$stats = static::getByVideoForUserOrCreate($video->getId(), $userId);
			if (!$stats->isVideoCompleted()) {
				return false;
			}
		}
		return true;
	}
	
	
	
	static function getForUser($userId) {
		$comments = get_comments(array(
			'type' => static::COMMENT_TYPE,
			'user_id' => $userId,
		));
		return array_map(function($comment) {
			return VideoStatistics::getInstance($comment);
		}, $comments);
	}
	
	
	static function getForVideo($videoId) {
		$comments = get_comments(array(
			'type' => static::COMMENT_TYPE,
			'post__in' => (is_array($videoId) ? $videoId : array($videoId)),
		));
// 		var_dump($comments);
		return array_map(function($comment) {
			return VideoStatistics::getInstance($comment);
		}, $comments);
	}
	
	
	static function getReportByChannel() {
		global $wpdb;
		
		$results = $wpdb->get_results($wpdb->prepare("SELECT
					ch.ID AS channelId,
					ch.post_title AS title,
					IFNULL(SUM(v_duration.meta_value), 0) AS durationSec,
					IFNULL(SUM(ROUND(v_duration.meta_value/60)), 0) AS durationMin,
					IFNULL(COUNT(s.comment_ID), 0) AS usersViewed,
					IFNULL(SUM(s.comment_karma), 0) AS seconds,
					IFNULL(SUM(ROUND(s.comment_karma/60)), 0) AS minutes,
					IFNULL(COUNT(v.ID), 0) AS videosNumber
				FROM $wpdb->posts AS ch
				LEFT JOIN $wpdb->posts AS v ON v.post_parent = ch.ID AND v.post_type = %s
				LEFT JOIN $wpdb->postmeta AS v_duration ON v_duration.post_id = v.ID AND v_duration.meta_key = %s
				LEFT JOIN $wpdb->comments s ON s.comment_post_ID = v.ID AND s.comment_type = %s
				WHERE ch.post_type = %s
				GROUP BY ch.ID
				ORDER BY ch.post_title",
				Video::POST_TYPE,
				Video::META_DURATION_SEC,
				static::COMMENT_TYPE,
				Channel::POST_TYPE
			), ARRAY_A);
		
		foreach ($results as &$result) {
			$result['channel'] = Channel::getInstance($result['channelId']);
		}
		
		return $results;
		
	}
	
	
	static function getReportForChannelByVideo(Channel $channel) {
		
		global $wpdb;
		
		$results = $wpdb->get_results($wpdb->prepare("SELECT
				v.ID AS videoId,
				v.post_title AS title,
				IFNULL(v_duration.meta_value, 0) AS durationSec,
				IFNULL(ROUND(v_duration.meta_value/60), 0) AS durationMin,
				IFNULL(COUNT(s.comment_ID), 0) AS usersViewed,
				IFNULL(SUM(s.comment_karma), 0) AS seconds,
				IFNULL(SUM(ROUND(s.comment_karma/60)), 0) AS minutes
				FROM $wpdb->posts AS v
				LEFT JOIN $wpdb->postmeta AS v_duration ON v_duration.post_id = v.ID AND v_duration.meta_key = %s
				LEFT JOIN $wpdb->comments s ON s.comment_post_ID = v.ID AND s.comment_type = %s
				WHERE v.post_type = %s
					AND v.post_parent = %d
				GROUP BY v.ID
				ORDER BY v.post_title",
				Video::META_DURATION_SEC,
				static::COMMENT_TYPE,
				Video::POST_TYPE,
				$channel->getId()
			), ARRAY_A);
		
// 		var_dump($results);
		
		foreach ($results as &$result) {
			$result['video'] = Video::getInstance($result['videoId']);
		}
		
		return $results;
		
	}
	
	
	static function getReportByUser() {
		global $wpdb;
	
		$results = $wpdb->get_results($wpdb->prepare("SELECT
				u.ID AS userId,
				u.display_name AS display_name,
				IFNULL(COUNT(s.comment_post_ID), 0) AS videosViewed,
				IFNULL(SUM(s.comment_karma), 0) AS seconds,
				IFNULL(SUM(ROUND(s.comment_karma/60)), 0) AS minutes
				FROM $wpdb->users AS u
				LEFT JOIN $wpdb->comments s ON s.user_id = u.ID AND s.comment_type = %s
				WHERE s.comment_karma > 0
				GROUP BY u.ID
				ORDER BY u.display_name",
				static::COMMENT_TYPE
			), ARRAY_A);
	
		$channelsSumDurationMin = Channel::getChannelsSummaryDurationMin();
		$channelsSumDurationSec = Channel::getChannelsSummaryDurationSec();
		foreach ($results as &$result) {
// 			$result['percent'] = ($channelsSumDurationMin == 0 ? 0 : max(0, min(100, ceil(100*$result['minutes']/$channelsSumDurationMin))));
			$result['percent'] = ($channelsSumDurationSec == 0 ? 0 : max(0, min(100, ceil(100*$result['seconds']/$channelsSumDurationSec))));
		}
	
		return $results;
	
	}
	
	
	static function getReportByChannelForUser($userId) {
		global $wpdb;
	
		$results = $wpdb->get_results($wpdb->prepare("SELECT
				ch.ID AS channelId,
				ch.post_title AS title,
				IFNULL(SUM(v_duration.meta_value), 0) AS durationSec,
				IFNULL(SUM(ROUND(v_duration.meta_value/60)), 0) AS durationMin,
				IFNULL(COUNT(s.comment_ID), 0) AS usersViewed,
				IFNULL(SUM(s.comment_karma), 0) AS seconds,
				IFNULL(SUM(ROUND(s.comment_karma/60)), 0) AS minutes,
				IFNULL(CEIL(100 * SUM(s.comment_karma)/SUM(v_duration.meta_value)), 0) AS percent,
				IFNULL(COUNT(v.ID), 0) AS videosNumber
				FROM $wpdb->posts AS ch
				LEFT JOIN $wpdb->posts AS v ON v.post_parent = ch.ID AND v.post_type = %s
				LEFT JOIN $wpdb->postmeta AS v_duration ON v_duration.post_id = v.ID AND v_duration.meta_key = %s
				LEFT JOIN $wpdb->comments s ON s.comment_post_ID = v.ID AND s.comment_type = %s AND s.user_id = %d
				WHERE ch.post_type = %s
				GROUP BY ch.ID
				ORDER BY ch.post_title",
				Video::POST_TYPE,
				Video::META_DURATION_SEC,
				static::COMMENT_TYPE,
				$userId,
				Channel::POST_TYPE
			), ARRAY_A);
		
// 		var_dump($results);
		
		$out = array();
		foreach ($results as $result) {
			$result['channel'] = Channel::getInstance($result['channelId']);
			$out[$result['channelId']] = $result;
		}
		
		return $out;
	
	}
	
	
	static function getReportByVideoForUserAndChannel($userId, $channel = null) {
		global $wpdb;
	
		$sql = $wpdb->prepare("SELECT
				v.ID AS videoId,
				v.post_title AS title,
				IFNULL(v_duration.meta_value, 0) AS durationSec,
				IFNULL(ROUND(v_duration.meta_value/60), 0) AS durationMin,
				IFNULL(COUNT(s.comment_ID), 0) AS usersViewed,
				IFNULL(SUM(s.comment_karma), 0) AS seconds,
				IFNULL(SUM(ROUND(s.comment_karma/60)), 0) AS minutes,
				IFNULL(CEIL(100 * SUM(s.comment_karma)/SUM(v_duration.meta_value)), 0) AS percent
				FROM $wpdb->posts AS v
				LEFT JOIN $wpdb->postmeta AS v_duration ON v_duration.post_id = v.ID AND v_duration.meta_key = %s
				LEFT JOIN $wpdb->comments s ON s.comment_post_ID = v.ID AND s.comment_type = %s
				WHERE v.post_type = %s AND s.user_id = %d",
				Video::META_DURATION_SEC,
				static::COMMENT_TYPE,
				Video::POST_TYPE,
				$userId
			);
		
		if ($channel) {
			$sql .= $wpdb->prepare(' AND v.post_parent = %d',  $channel->getId());
		}
		
		$sql .= ' GROUP BY v.ID ORDER BY v.post_title';
		
// 		var_dump($sql);
		$results = $wpdb->get_results($sql, ARRAY_A);
		
		// 		var_dump($results);
		
		$out = array();
		foreach ($results as $result) {
			$result['video'] = Video::getInstance($result['videoId']);
			$out[$result['videoId']] = $result;
		}
		
		return $out;
	
	}
	
	
	
}
