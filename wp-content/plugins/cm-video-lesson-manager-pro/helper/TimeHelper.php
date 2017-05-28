<?php

namespace com\cminds\videolesson\helper;

class TimeHelper {
	
	static function period2seconds($period) {
		$period = preg_replace('/\s/', '', $period);
		$units = array('min' => 60, 'h' => 3600, 'd' => 3600*24, 'w' => 3600*24*7, 'm' => 3600*24*30, 'y' => 3600*24*365);
		$unit = preg_replace('/[0-9]/', '', $period);
		if (isset($units[$unit])) {
			$number = preg_replace('/[^0-9]/', '', $period);
			return $number * $units[$unit];
		}
	}
	
	
	static function seconds2period($seconds) {
		$units = array('minute' => 60, 'hour' => 3600, 'day' => 3600*24, 'week' => 3600*24*7, 'month' => 3600*24*30, 'year' => 3600*24*365);
		$result = $seconds;
		$lastUnit = 'second';
		foreach ($units as $unit => $sec) {
			if ($seconds/$sec < 1) {
				break;
			} else {
				$result = $seconds/$sec;
				$lastUnit = $unit;
			}
		}
		return $result .' '. \__($lastUnit . ($result == 1 ? '' : 's'));
	}
	
	
	static function period2date($period) {
		$units = array('min' => 'minute', 'h' => 'hour', 'd' => 'day', 'w' => 'week', 'm' => 'month', 'y' => 'year');
		$unit = preg_replace('/[0-9\s]/', '', $period);
		if (isset($units[$unit])) {
			$number = preg_replace('/[^0-9]/', '', $period);
			return $number .' '. \__($units[$unit] . ($number == 1 ? '' : 's'));
		}
	}
	
	
	static function niceTimeFormat($sec) {
		$hours = floor($sec/3600);
		$sec %= 3600;
		$minutes = floor($sec/60);
		$sec %= 60;
		return sprintf('%02d:%02d:%02d', $hours, $minutes, $sec);
	}
	
}