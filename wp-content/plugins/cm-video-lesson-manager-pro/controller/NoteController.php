<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\model\Search;

use com\cminds\videolesson\model\Note;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\model\Labels;

use com\cminds\videolesson\model\Video;

class NoteController extends Controller {
	
	const SEARCH_CONTEXT_NOTES = 'notes';
	
	protected static $filters = array(
// 		array('name' => 'cmvl_search_context', 'args' => 4),
// 		array('name' => 'cmvl_search_score', 'args' => 5),
	);
	protected static $actions = array(array('name' => 'cmvl_video_bottom', 'args' => 1));
	protected static $ajax = array('cmvl_video_set_user_note');
	
	
	static function cmvl_video_set_user_note() {
		if (is_user_logged_in() AND isset($_POST['videoId']) AND isset($_POST['note'])) {
			if (isset($_POST['nonce']) AND wp_verify_nonce($_POST['nonce'], ChannelController::NONCE_AJAX_CHANNEL)) {
				if ($video = Video::getInstance($_POST['videoId'])) {
					Note::createOrUpdateNote($video->getId(), get_current_user_id(), $_POST['note']);
					echo 'ok';
					exit;
				} else {
					die('Video not found.');
				}
			} else {
				die('Invalid nonce.');
			}
		} else {
			die('Invalid params.');
		}
	}
	
	
	static function cmvl_video_bottom($video) {
		if (is_user_logged_in() AND !empty($video) AND $video instanceof Video) {
			if (Settings::getOption(Settings::OPTION_SHOW_VIDEO_NOTE)) {
				$placeholder = Labels::getLocalized('notes_placeholder');
				$note = Note::getNoteContent($video->getId(), get_current_user_id());
				echo self::loadFrontendView('video-bottom', compact('note', 'placeholder'));
			}
		}
	}
	
	
}
