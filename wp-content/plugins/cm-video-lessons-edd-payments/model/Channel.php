<?php

namespace com\cminds\videolesson\addon\eddpay\model;

class Channel extends PostType {
	
	const POST_TYPE = 'cmvl_channel';
	
	
	static function registerPostType() {
		// don't
	}
	
	
	/**
	 *
	 * @param unknown $postId
	 * @return Channel
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	
	
}