<?php

namespace com\cminds\videolesson\metabox;

use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\controller\SettingsController;
use com\cminds\videolesson\helper\HtmlHelper;

class CourseProgressNotification extends TermCustomField {
	
	const FIELD_PROGRESS_NOTIF_STATUS = 'cmvl_course_progress_notif_status';
	
	static protected $supportedTaxonomies = array(Category::TAXONOMY);
	
	static protected $fields = array(
		self::FIELD_PROGRESS_NOTIF_STATUS => 'Send notification when user completed this course:',
	);
	
	
	static function displayFields($term = null) {
		SettingsController::loadAssets();
		parent::displayFields($term);
	}
	
	
	static function get_field_cmvl_course_progress_notif_status($fieldName, $term = null) {
		$currentValue = Category::NOTIFICATION_STATUS_GLOBAL;
		if ($term AND $course = Category::getInstance($term)) {
			$currentValue = $course->getProgressNotificationStatus();
		}
		$options = array(
			Category::NOTIFICATION_STATUS_GLOBAL => 'follow global settings',
			Category::NOTIFICATION_STATUS_DISABLED => 'disabled',
			Category::NOTIFICATION_STATUS_ENABLED => 'enabled',
		);
		return '<p>'. HtmlHelper::renderSelect($fieldName, $options, $currentValue) .'</p>';
	}
	
	
	static function save($term_id) {
		if ($course = Category::getInstance($term_id)) {
			$status = filter_input(INPUT_POST, static::FIELD_PROGRESS_NOTIF_STATUS);
			$course->setProgressNotificationStatus($status);
		}
	}
	
}
