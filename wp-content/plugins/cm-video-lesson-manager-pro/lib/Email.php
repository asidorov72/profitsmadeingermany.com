<?php

namespace com\cminds\videolesson\lib;

class Email {
	
	static function send($receivers, $subject, $body, array $vars = array(), array $headers = array()) {
		
		$hasReceivers = false;
		if (!is_array($receivers)) {
			$mailTo = $receivers;
			$hasReceivers = true;
		} else {
			$mailTo = null;
			foreach ($receivers as $email) {
				$email = trim($email);
				if (is_email($email)) {
					$headers[] = ' Bcc: '. $email;
					$hasReceivers = true;
				}
			}
		}
		
		if ($hasReceivers) {
			$headers = apply_filters('cmvl_email_headers', $headers, $mailTo, $subject, $body, $vars);
			return wp_mail($mailTo, strtr($subject, $vars), strtr($body, $vars), $headers);
		} else {
			return false;
		}
		
	}
	
	
	static function getBlogVars() {
		return array(
			'[blogname]' => get_bloginfo('blogname'),
			'[siteurl]' => site_url(),
		);
	}
	
	
	static function getUserVars($userId) {
		if ($user = get_userdata($userId)) {
			return array(
				'[userdisplayname]' => $user->display_name,
				'[userlogin]' => $user->user_login,
				'[useremail]' => $user->user_email,
			);
		} else {
			return array();
		}
	}
	
	
}
