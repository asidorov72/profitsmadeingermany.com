<?php

namespace com\cminds\videolesson\addon\eddpay\model;

class Video extends PostType {
	
	const POST_TYPE = 'cmvl_video';
	
	
	static function registerPostType() {
		// don't
	}
	
	
	/**
	 *
	 * @param unknown $postId
	 * @return Video
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	
	
}