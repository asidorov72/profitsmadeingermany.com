<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\App;
use com\cminds\videolesson\lib\Email;
use com\cminds\videolesson\model\Stats;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\model\VideoStatistics;
use com\cminds\videolesson\model\ProgressNotification;

class NotificationsController extends Controller {
	
	const DEBUG = 0;

	const NONCE_CHANNEL_NOTIFICATIONS = 'cmvl_channel_notifications_nonce';
	
	const NOTIFICATION_TYPE_VIDEO = 'video';
	const NOTIFICATION_TYPE_CHANNEL = 'channel';
	const NOTIFICATION_TYPE_COURSE = 'course';
	
	
	protected static $actions = array(
		'add_meta_boxes' => array('priority' => 100),
		'cmvl_stats_interval_added' => array('args' => 1),
		'save_post' => array('args' => 1),
	);
	protected static $filters = array('cmvl_options_config');
	
	
	
	static function cmvl_stats_interval_added(VideoStatistics $stats) {
		/* @var $video Video */
		$video  = $stats->getVideo();
		$userId = get_current_user_id();
		
// 		do_action('cmvl_user_completed_channel', $video->getChannelId(), $userId);
		
		if ($stats->isVideoCompleted()) {
			
			do_action('cmvl_user_completed_video', $video->getId(), $userId);
			
			// Check whether to send per video notification
			if ($video->isProgressNotificationEnabled()) {
				if (!static::wasNotificationSent($video->getId(), $userId)) {
					static::debug('video completed - sending notification');
					$receivers = Settings::getOption(Settings::OPTION_VIDEO_PROGRESS_NOTIF_EMAILS);
					$subject = Settings::getOption(Settings::OPTION_VIDEO_PROGRESS_NOTIF_SUBJECT);
					$body = Settings::getOption(Settings::OPTION_VIDEO_PROGRESS_NOTIF_TEMPLATE);
					static::sendNotification($receivers, $subject, $body, $userId,
							array('[video_name]' => $video->getName(), '[video_permalink]' => $video->getPermalink()));
					ProgressNotification::create($video->getId(), $userId);
				} else static::debug('video is completed and notification is enabled but email has been already sent');
			} else static::debug('video is completed but notification is disabled');
			
			if ($stats->isChannelCompleted()) {
				do_action('cmvl_user_completed_channel', $video->getChannelId(), $userId);
				
				$channel = $video->getChannel();
				
				// Check whether to send per channel notification
				if ($channel->isProgressNotificationEnabled()) {
					if (!static::wasNotificationSent($channel->getId(), $userId)) {
						static::debug('channel completed - sending notification');
						$receivers = Settings::getOption(Settings::OPTION_LESSON_PROGRESS_NOTIF_EMAILS);
						$subject = Settings::getOption(Settings::OPTION_LESSON_PROGRESS_NOTIF_SUBJECT);
						$body = Settings::getOption(Settings::OPTION_LESSON_PROGRESS_NOTIF_TEMPLATE);
						static::sendNotification($receivers, $subject, $body, $userId,
								array('[lesson_name]' => $channel->getName(), '[lesson_permalink]' => $channel->getPermalink()));
						ProgressNotification::create($channel->getId(), $userId);
					} else static::debug('channel is completed and notification are enabled but email has been already sent');
				} else static::debug('channel is completed but notification is disabled');
				
				$courses = $channel->getCategories();
				foreach ($courses as $course) {
					if ($course->isCompletedByUser($userId)) {
						do_action('cmvl_user_completed_course', $course->getId(), $userId);
						if ($course->isProgressNotificationEnabled()) {
							if (!$course->wasProgressNotificationSentToUser($userId)) {
								$receivers = Settings::getOption(Settings::OPTION_COURSE_PROGRESS_NOTIF_EMAILS);
								$subject = Settings::getOption(Settings::OPTION_COURSE_PROGRESS_NOTIF_SUBJECT);
								$body = Settings::getOption(Settings::OPTION_COURSE_PROGRESS_NOTIF_TEMPLATE);
								static::sendNotification($receivers, $subject, $body, $userId,
										array('[course_name]' => $course->getName(), '[course_permalink]' => $course->getPermalink()));
								$course->markProgressNotificationSentToUser($userId);
								static::debug('course completed - sending notification ' . $course->getId());
							} else static::debug('course is completed and notification is enabled but email has been already sent ' . $course->getId());
						} else static::debug('course is completed but notificaton is disabled ' . $course->getId());
					} else static::debug('course is not completed ' . $course->getId());
				}
				
			} else static::debug('channel is not completed');
			
		} else static::debug('video is not completed');
	}
	
	
	static protected function debug($var) {
		if (static::DEBUG) {
			var_dump($var);
		}
	}
	
	
	static function wasNotificationSent($id, $userId) {
		$record = ProgressNotification::find($id, $userId);
// 		var_dump($record);
		return !empty($record);
	}
	
	
	/**
	 * @TODO: rebuild to use courses also - maybe create an interface
	 * 
	 * @param unknown $type
	 * @param Video $video
	 * @return boolean
	 */
	static function sendNotification($receivers, $subject, $body, $userId, array $vars) {
		
		if (empty($receivers)) return false;
		
		$user = get_userdata($userId);
		
		$vars = array_merge($vars, array(
			'[blogname]' => get_option('blogname'),
			'[home]' => get_option('home'),
			'[username]' => $user->display_name,
			'[userlogin]' => $user->user_login,
		));
		Email::send($receivers, $subject, $body, $vars);
		
	}
	
	
	
	static function add_meta_boxes() {
		add_meta_box( App::prefix('-notifications'), 'Lessons Progress Notifications', array(get_called_class(), 'renderMetaBox'),
			Channel::POST_TYPE, 'side', 'low' );
	}
	
	
	/**
	 * Show channel notifications options.
	 * 
	 * @param unknown $post
	 */
	static function renderMetaBox($post) {
		if ($channel = Channel::getInstance($post)) {
			$channelNotificationsStatus = $channel->getChannelProgressNotificationStatus();
			$videosNotificationsStatus = $channel->getVideosProgressNotificationStatus();
		} else {
			$channelNotificationsStatus = $videosNotificationsStatus = null;
		}
		
		$nonce = wp_create_nonce(static::NONCE_CHANNEL_NOTIFICATIONS);
		echo static::loadBackendView('notifications-meta-box', compact('channelNotificationsStatus', 'videosNotificationsStatus', 'nonce'));
	}
	
	
	static function save_post($post_id) {
		if (!empty($_POST[static::NONCE_CHANNEL_NOTIFICATIONS]) AND wp_verify_nonce($_POST[static::NONCE_CHANNEL_NOTIFICATIONS], static::NONCE_CHANNEL_NOTIFICATIONS)
				AND $channel = Channel::getInstance($post_id)) {
			
			if (isset($_POST['cmvl_channel_notifications'])) {
				$channel->setChannelProgressNotificationStatus($_POST['cmvl_channel_notifications']);
			}
			if (isset($_POST['cmvl_videos_notifications'])) {
				$channel->setVideosProgressNotificationStatus($_POST['cmvl_videos_notifications']);
			}
			
		}
	}
	
	
	static function cmvl_options_config($config) {
		return array_merge($config, array(
			
			// Course progress
			Settings::OPTION_COURSE_PROGRESS_NOTIF_ENABLE => array(
					'type' => Settings::TYPE_BOOL,
					'category' => 'notifications',
					'subcategory' => 'course_progress',
					'title' => 'Enable notifications for courses',
					'desc' => 'Send notification once entire course has been completed by user.',
			),
			Settings::OPTION_COURSE_PROGRESS_NOTIF_EMAILS => array(
				'type' => Settings::TYPE_CSV_LINE,
				'category' => 'notifications',
				'subcategory' => 'course_progress',
				'title' => 'Emails to notify',
				'desc' => 'Enter comma separated email addresses to send the notification to.',
			),
			Settings::OPTION_COURSE_PROGRESS_NOTIF_SUBJECT => array(
				'type' => Settings::TYPE_STRING,
				'default' => '[[blogname]] Course "[course_name]" has been completed by [username]',
				'category' => 'notifications',
				'subcategory' => 'course_progress',
				'title' => 'Email subject',
				'desc' => 'You can use following shortcodes:<br />[blogname] - website\'s name<br />'
							. '<br />[course_name] - name of the course<br />[username]<br />[userlogin]',
			),
			Settings::OPTION_COURSE_PROGRESS_NOTIF_TEMPLATE => array(
				'type' => Settings::TYPE_TEXTAREA,
				'default' => "Hi,\nuser has completed a course: [course_name].\n\nWebsite: [blogname]\nWebsite URL: [home]\n"
							. "Course link: [course_permalink]\nUser name: [username]\nUser login: [userlogin]",
				'category' => 'notifications',
				'subcategory' => 'course_progress',
				'title' => 'Email body template',
				'desc' => 'You can use following shortcodes:<br />[blogname] - website\'s name<br />[home] - website\'s home url'
					. '<br />[course_name] - name of the course<br />[course_permalink] - permalink to the course<br />[username]<br />[userlogin]',
			),
				
			// Lesson progress
			Settings::OPTION_LESSON_PROGRESS_NOTIF_ENABLE => array(
					'type' => Settings::TYPE_BOOL,
					'category' => 'notifications',
					'subcategory' => 'lesson_progress',
					'title' => 'Enable notifications for lessons',
					'desc' => 'Send notification once entire lessons has been completed by user.',
			),
			Settings::OPTION_LESSON_PROGRESS_NOTIF_EMAILS => array(
					'type' => Settings::TYPE_CSV_LINE,
					'category' => 'notifications',
					'subcategory' => 'lesson_progress',
					'title' => 'Emails to notify',
					'desc' => 'Enter comma separated email addresses to send the notification to.',
			),
			Settings::OPTION_LESSON_PROGRESS_NOTIF_SUBJECT => array(
					'type' => Settings::TYPE_STRING,
					'default' => '[[blogname]] Lesson "[lesson_name]" has been completed by [username]',
					'category' => 'notifications',
					'subcategory' => 'lesson_progress',
					'title' => 'Email subject',
					'desc' => 'You can use following shortcodes:<br />[blogname] - website\'s name'
					. '<br />[lesson_name] - name of the lesson<br />[username]<br />[userlogin]',
			),
			Settings::OPTION_LESSON_PROGRESS_NOTIF_TEMPLATE => array(
					'type' => Settings::TYPE_TEXTAREA,
					'default' => "Hi,\nuser has completed a lesson: [lesson_name].\n\nWebsite: [blogname]\nWebsite URL: [home]\n"
					. "Lesson link: [lesson_permalink]\nUser name: [username]\nUser login: [userlogin]",
					'category' => 'notifications',
					'subcategory' => 'lesson_progress',
					'title' => 'Email body template',
					'desc' => 'You can use following shortcodes:<br />[blogname] - website\'s name<br />[home] - website\'s home url'
					. '<br />[lesson_name] - name of the lesson<br />[lesson_permalink] - permalink to the lesson<br />[username]<br />[userlogin]',
			),
			
			// Video progress
			Settings::OPTION_VIDEO_PROGRESS_NOTIF_ENABLE => array(
					'type' => Settings::TYPE_BOOL,
					'category' => 'notifications',
					'subcategory' => 'video_progress',
					'title' => 'Enable notifications for single videos completed',
					'desc' => 'Send notification once a video has been completed by user.',
			),
			Settings::OPTION_VIDEO_PROGRESS_NOTIF_EMAILS => array(
					'type' => Settings::TYPE_CSV_LINE,
					'category' => 'notifications',
					'subcategory' => 'video_progress',
					'title' => 'Emails to notify',
					'desc' => 'Enter comma separated email addresses to send the notification to.',
			),
			Settings::OPTION_VIDEO_PROGRESS_NOTIF_SUBJECT => array(
					'type' => Settings::TYPE_STRING,
					'default' => '[[blogname]] Video "[lesson_name]" has been completed by [username]',
					'category' => 'notifications',
					'subcategory' => 'video_progress',
					'title' => 'Email subject',
					'desc' => 'You can use following shortcodes:<br />[blogname] - website\'s name'
						. '<br />[video_name] - name of the video<br />[username]<br />[userlogin]',
			),
			Settings::OPTION_VIDEO_PROGRESS_NOTIF_TEMPLATE => array(
					'type' => Settings::TYPE_TEXTAREA,
					'default' => "Hi,\nuser has completed a video: [video_name].\n\nWebsite: [blogname]\nWebsite URL: [home]\n"
						. "Lesson link: [video_permalink]\nUser name: [username]\nUser login: [userlogin]",
					'category' => 'notifications',
					'subcategory' => 'video_progress',
					'title' => 'Email body template',
					'desc' => 'You can use following shortcodes:<br />[blogname] - website\'s name<br />[home] - website\'s home url'
						. '<br />[video_name] - name the video<br />[video_permalink] - permalink to the video<br />[username]<br />[userlogin]',
			),
				
				
			Settings::OPTION_COURSE_PROGRESS_NOTIF_LACK_SECONDS => array(
				'type' => Settings::TYPE_INT,
				'default' => 1,
				'category' => 'notifications',
				'subcategory' => 'progress_general',
				'title' => 'Send notification even if lacking per video only [seconds]',
				'desc' => 'Set the number of lacking seconds per video under which the notifications will be send. '
					. 'Useful for the issues on the Internet Explorer.',
			),
			Settings::OPTION_LESSONS_PROGRESS_ROUND_UP_SECONDS => array(
				'type' => Settings::TYPE_INT,
				'default' => 1,
				'category' => 'notifications',
				'subcategory' => 'progress_general',
				'title' => 'Round up progress to 100% when lacking per video [seconds]',
				'desc' => 'Set the number of lacking seconds per video under which the video progress will be rounded up to the 100%. '
					. 'Useful for the issues on the Internet Explorer. It doesn\'t affect existing progress statistics.',
			),
		));
	}
	
}
