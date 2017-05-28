<?php

namespace com\cminds\videolesson\metabox;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\controller\VideoBackendController;

class VideoAPIInfoBox extends MetaBox {

	const SLUG = 'cmvl-video-api-info-box';
	const NAME = 'API Information';
	const CONTEXT = 'side';
	const PRIORITY = 'low';
	
	static protected $supportedPostTypes = array(Video::POST_TYPE);
	
	
	static function render($post) {
		
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend');
		
// 		if ('auto-draft' == $post->post_status) {
// 			static::renderImportTool($post);
// 		}

		static::renderInfoBox($post);
		
	}
	
	
	
	static function renderImportTool($post) {
		$apiList = array('-- choose --') + Settings::getApiList();
		static::renderNonceField($post);
		echo VideoBackendController::loadBackendView('metabox-import', compact('post', 'apiList'));
	}
	
	
	static function renderInfoBox($post) {
		$video = Video::getInstance($post);
		echo VideoBackendController::loadBackendView('metabox-info', compact('video'));
	}
	
	
	static function savePost($postId) {
		if ($video = Video::getInstance($postId)) {
// 			if (isset($_POST[InvitationCode::META_CODE_STRING])) {
// 				$codeObj->setCodeString($_POST[InvitationCode::META_CODE_STRING]);
// 			}
		}
	}
	
}