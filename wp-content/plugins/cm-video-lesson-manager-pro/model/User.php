<?php

namespace com\cminds\videolesson\model;

class User extends Model {
	
	static function getById($id) {
		return get_userdata($id);
	}
	
	
	static function countAllUsers() {
		global $wpdb;
		return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->users");
	}
	
	
	static function getUsersList($labelField = 'user_login') {
		global $wpdb;
		$users = $wpdb->get_results("SELECT ID, $labelField FROM $wpdb->users ORDER BY $labelField", ARRAY_A);
		$results = array();
		foreach ($users as $user) {
			$results[$user['ID']] = $user[$labelField];
		}
		return $results;
	}
	
		
}
