<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\App;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\VideoStatistics;
use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\model\User;

class ProgressReportController extends Controller {
	
	const PAGE_TITLE = 'Progress Report';
	
	const ACTION_FETCH_REPORT = 'cmvl_fetch_report';
	const ACTION_REPORT_TIME_LOG = 'cmvl_report_time_log';
	const ACTION_REPORT_TIME_LOG_DOWNLOAD_CSV = 'cmvl_report_time_log_download_csv';
	
	const PARAM_ACTION = 'action';
	const PARAM_GROUP_BY = 'groupby';
	const PARAM_EXCLUDE_WITHOUT_PROGRESS = 'exclude_without_progress';
	const PARAM_FILTER = 'filter';
	const PARAM_FILTER_COURSE = 'course';
	const PARAM_FILTER_LESSON = 'lesson';
	const PARAM_FILTER_VIDEO = 'video';
	const PARAM_FILTER_USER = 'user';
	
	
	protected static $actions = array(
		'admin_menu' => array('priority' => 11),
	);
	

	static function admin_menu() {
		add_submenu_page(App::MENU_SLUG, App::getPluginName() . ' ' . static::PAGE_TITLE, static::PAGE_TITLE, 'manage_options',
				self::getMenuSlug(), array(get_called_class(), 'render'));
	}
	
	
	static function getMenuSlug() {
		return App::MENU_SLUG . '-progress-report';
	}
	
	
	static function render() {
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend');
		echo self::loadView('backend/template', array(
				'title' => App::getPluginName() . ' ' . static::PAGE_TITLE,
				'nav' => self::getBackendNav(),
				'content' => self::getContent(),
		));
	}
	
	
	
	static function getContent() {
		
		$groupBy = filter_input(INPUT_GET, static::PARAM_GROUP_BY);
		$excludeWithoutProgress = intval(filter_input(INPUT_GET, static::PARAM_EXCLUDE_WITHOUT_PROGRESS));
		$filter = static::getFilter();
 		$filterRecords = static::getFilterRecords($filter);
		
		$groupByOptions = array(
			'user' => 'User',
			'course' => 'Course',
			'lesson' => 'Lesson',
			'video' => 'Video',
		);
		
		$coursesList = Category::getAll(Category::FIELDS_ID_NAME);
		
		if (User::countAllUsers() < 100) {
			$usersList = User::getUsersList();
		} else {
			$usersList = array();
		}
		
		$content = static::loadBackendView('form', compact('groupByOptions', 'filter', 'groupBy', 'coursesList', 'excludeWithoutProgress', 'filterRecords', 'usersList'));
		
		$action = filter_input(INPUT_GET, static::PARAM_ACTION);
		if (static::ACTION_FETCH_REPORT == $action) {
			
			$reportUrl = add_query_arg(array(
				'page' => static::getMenuSlug(),
				static::PARAM_EXCLUDE_WITHOUT_PROGRESS => $excludeWithoutProgress,
			), admin_url('admin.php'));
			$timeReportUrl = add_query_arg(array(
				'page' => static::getMenuSlug(),
				static::PARAM_ACTION => static::ACTION_REPORT_TIME_LOG,
				static::PARAM_GROUP_BY => $groupBy,
			), admin_url('admin.php'));
			$data = static::fetchReport($filter, $groupBy, $excludeWithoutProgress);
			$content .= static::loadBackendView('report', compact('data', 'groupBy', 'filter', 'groupByOptions', 'reportUrl', 'filterRecords', 'timeReportUrl'));
			
		}
		else if (static::ACTION_REPORT_TIME_LOG == $action) {
			
			$data = static::fetchTimeLogReport($filter);
			$downloadCSVUrl = add_query_arg(array(
				static::PARAM_ACTION => static::ACTION_REPORT_TIME_LOG_DOWNLOAD_CSV,
			), $_SERVER['REQUEST_URI']);
			$content .= static::loadBackendView('time-log-report', compact('data', 'groupBy', 'filter', 'groupByOptions', 'reportUrl', 'filterRecords', 'downloadCSVUrl'));
			
		}
		
		return $content;
		
	}
	
	
	static function getFilter() {
		$filter = shortcode_atts(array(
			static::PARAM_FILTER_COURSE => '',
			static::PARAM_FILTER_LESSON => '',
			static::PARAM_FILTER_VIDEO => '',
			static::PARAM_FILTER_USER => '',
		), isset($_GET[static::PARAM_FILTER]) ? $_GET[static::PARAM_FILTER] : array());
		return $filter;
	}
	
	
	static protected function getFilterRecords(array $filter) {
		$result = array();
		foreach ($filter as $key => $value) {
			if (strlen($value) > 0 AND is_numeric($value)) {
				switch ($key) {
					case 'user':
						$user = get_userdata($value);
						if ($user AND !is_wp_error($user)) {
							$result[$key] = $user;
						}
						break;
					case 'course':
						if ($course = Category::getInstance($value)) {
							$result[$key] = $course;
						}
						break;
					case 'lesson':
						if ($lesson = Channel::getInstance($value)) {
							$result[$key] = $lesson;
						}
						break;
					case 'video':
						if ($video = Video::getInstance($value)) {
							$result[$key] = $video;
						}
						break;
				}
			}
		}
		return $result;
	}
	
	
	static protected function fetchReport(array $filters, $groupBy, $excludeWithoutProgress) {
		global $wpdb;
		
		$ttIds = array_filter(array_map(function($term) { return $term->term_taxonomy_id; }, Category::getAll(Category::FIELDS_ALL)));
// 		var_dump($ttIds);exit;

		$where = static::filters2where($filters);

		$groupByFieldsMap = array(
			'u.ID' => 'user',
			'tt.term_taxonomy_id' => 'course',
			'ch.ID' => 'lesson',
			'v.ID' => 'video',
		);
		$groupByField = array_search($groupBy, $groupByFieldsMap);

		$sql = static::prepareBasicSql() . $where . " GROUP BY $groupByField";
// 		var_dump($sql);
		
		$data = $wpdb->get_results($sql, ARRAY_A);
		
		
		if ($excludeWithoutProgress) {
			$data = array_filter($data, function($row) {
				if ($row['totalTimeWatchingSec'] > 0) {
					return $row;
				}
			});
		}
		
// 		var_dump($data);

		return $data;
		
		
	}
	
	
	protected static function filters2where(array $filters) {
		global $wpdb;
		
		$where = '';
		foreach ($filters as $key => $value) {
			if (strlen($value) == 0) continue;
			switch ($key) {
				case 'user':
					if (is_numeric($value)) {
						$where .= $wpdb->prepare(" AND u.ID = %d ", intval($value));
					} else {
						$value = strtr($value, array('*' => '%', '?' => '_'));
						$where .= $wpdb->prepare(' AND (u.user_email LIKE %s OR u.user_login LIKE %s) ', $value, $value, $value);
					}
					break;
				case 'course':
					if (is_numeric($value)) {
						$where .= $wpdb->prepare(" AND tt.term_id = %d ", intval($value));
					} else {
						$value = strtr($value, array('*' => '%', '?' => '_'));
						$where .= $wpdb->prepare(' AND te.name LIKE %s ', $value);
					}
					break;
				case 'lesson':
					if (is_numeric($value)) {
						$where .= $wpdb->prepare(" AND ch.ID = %d ", intval($value));
					} else {
						$value = strtr($value, array('*' => '%', '?' => '_'));
						$where .= $wpdb->prepare(' AND ch.post_title LIKE %s ', $value);
					}
					break;
				case 'video':
					if (is_numeric($value)) {
						$where .= $wpdb->prepare(" AND v.ID = %d ", intval($value));
					} else {
						$value = strtr($value, array('*' => '%', '?' => '_'));
						$where .= $wpdb->prepare(' AND v.post_title LIKE %s ', $value);
					}
					break;
			}
		}
		return $where;
	}
	
	
	protected static function prepareBasicSql($select = '', $join = '') {
		global $wpdb;
		
		$ttIds = array_filter(array_map(function($term) { return $term->term_taxonomy_id; }, Category::getAll(Category::FIELDS_ALL)));
		
		$sql = $wpdb->prepare("SELECT
			
			u.ID AS userId,
			u.user_login AS userLogin,
			u.display_name AS userDisplayName,
				
			v.ID AS videoId,
			v.post_title AS videoName,
				
			ch.ID AS lessonId,
			ch.post_title AS lessonName,
	
			te.name AS courseName,
			te.term_id AS courseId,
				
			IFNULL(SUM(v_duration.meta_value)/COUNT(DISTINCT tt.term_taxonomy_id)/COUNT(DISTINCT u.ID), 0) AS videoDurationSec,
			IFNULL(COUNT(DISTINCT s.user_id), 0) AS numberOfUsers,
			IFNULL(SUM(s.comment_karma)/COUNT(DISTINCT tt.term_taxonomy_id), 0) AS totalTimeWatchingSec,
			LEAST(100, IFNULL(CEIL(100 * SUM(s.comment_karma)/SUM(v_duration.meta_value)), 0)) AS totalTimeWatchingPercent
				
			$select
				
			FROM $wpdb->posts AS v
			JOIN $wpdb->postmeta AS v_duration ON v_duration.post_id = v.ID AND v_duration.meta_key = %s
				
			JOIN $wpdb->users u
				
			JOIN $wpdb->posts AS ch ON ch.ID = v.post_parent AND ch.post_type = %s
				
			JOIN $wpdb->term_relationships tr ON tr.object_id = ch.ID AND tr.term_taxonomy_id IN (". implode(',', $ttIds) .")
			JOIN $wpdb->term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = %s
			JOIN $wpdb->terms te ON te.term_id = tt.term_id
				
			LEFT JOIN $wpdb->comments s ON s.comment_post_ID = v.ID AND s.comment_type = %s AND s.user_id = u.ID ",
				
			Video::META_DURATION_SEC,
			Channel::POST_TYPE,
			Category::TAXONOMY,
			VideoStatistics::COMMENT_TYPE
	
		);
		
		$sql .= PHP_EOL . $join . PHP_EOL;
		$sql .= PHP_EOL . $wpdb->prepare("WHERE v.post_type = %s AND v.post_status = 'publish' AND ch.post_status = 'publish'", Video::POST_TYPE) . PHP_EOL;
		
		return $sql;
		
	}
	
	static function fetchTimeLogReport(array $filters) {
		global $wpdb;
		
		$select = ", log.comment_date AS time_log_date_time, log.comment_content AS time_log_interval ";
		$join = "JOIN $wpdb->comments AS log ON log.comment_parent = s.comment_ID";
		$where = static::filters2where($filters);
		
		// Date filter
		$dateFrom = filter_input(INPUT_GET, 'date_from');
		$dateTo = filter_input(INPUT_GET, 'date_to');
		if (strlen($dateFrom) > 0) {
			$where .= $wpdb->prepare(' AND log.comment_date >= %s', Date('Y-m-d 00:00:00', strtotime($dateFrom)));
		}
		if (strlen($dateTo) > 0) {
			$where .= $wpdb->prepare(' AND log.comment_date <= %s', Date('Y-m-d 23:59:59', strtotime($dateTo)));
		}
		
		$sql = static::prepareBasicSql($select, $join) . $where;
		$sql .= ' GROUP BY log.comment_ID ORDER BY log.comment_date ASC';
		
// 		var_dump($sql);
		
		$data = $wpdb->get_results($sql, ARRAY_A);
		
		return $data;
		
	}
	
	
	
	static function processRequest() {
		
		$page = filter_input(INPUT_GET, 'page');
		$action = filter_input(INPUT_GET, static::PARAM_ACTION);
		if ($page == static::getMenuSlug() AND static::ACTION_REPORT_TIME_LOG_DOWNLOAD_CSV == $action) {
			
			$filter = static::getFilter();
			$data = static::fetchTimeLogReport($filter);
// 			var_dump($data);exit;
			
			// output headers so that the file is downloaded rather than displayed
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=video-lessons-time-log-'. Date('YmdHis') .'.csv');
			
			$columns = array('userId', 'userLogin', 'userDisplayName', 'videoId', 'videoName', 'lessonId', 'lessonName', 'courseId', 'courseName',
				'videoDurationSec', 'totalTimeWatchingSec', 'totalTimeWatchingPercent', 'time_log_date_time');
			
			$output = fopen('php://output', 'w');
			fputcsv($output, array_merge($columns, array('time_log_duration', 'time_log_start_sec', 'time_log_end_sec')));
			foreach ($data as $record) {
				$row = array();
				foreach ($columns as $key) {
					$row[] = $record[$key];
				}
				
				$interval = explode(' ', $record['time_log_interval']);
				$start = intval(reset($interval));
				$end = intval(end($interval));
				$duration = intval($end - $start);
				
				$row[] = $duration;
				$row[] = $start;
				$row[] = $end;
				
				fputcsv($output, $row);
				
			}
			
			fclose($output);
			exit;
			
			
		}
		
	}
	
	
}