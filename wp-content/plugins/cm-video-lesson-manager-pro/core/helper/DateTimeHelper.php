<?php

namespace com\cminds\videolesson\helper;

class DateTimeHelper {
	
	static function getMysqlDatetime($timestamp = null) {
		if (is_null($timestamp)) $timestamp = time();
		return Date('Y-m-d H:i:s', $timestamp);
	}
	
}
