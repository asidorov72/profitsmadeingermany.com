<?php

namespace com\cminds\videolesson\model;

class User extends Model {
	

	static function getSomeAdminUserId() {
		$admins = get_users(array('role' => 'administrator'));
		return ($admins[0]->ID);
	}
	
}