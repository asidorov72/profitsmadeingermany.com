<?php

namespace com\cminds\videolesson\helper;

use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\controller\ChannelController;

class PlayerHelper {
	
	static function getPlayer(Video $video, $playerOptions = null) {
		$out = '';
		switch ($video->getServiceProvider()) {
			case Settings::API_VIMEO:
				$out = static::getPlayerVimeo($video);
				break;
			case Settings::API_WISTIA:
				$out = static::getPlayerWistia($video);
				break;
			case Settings::API_YOUTUBE:
				$out = static::getPlayerYoutube($video);
				break;
		}
		return $out;
	}
	
	
	protected static function getPlayerVimeo(Video $video) {
		if (preg_match('~/videos/([0-9]+)~', $video->getProvidersId(), $match)) {
			$id = $match[1];
		} else {
			$id = explode('/', $video->getProvidersId());
			$id = end($id);
		}
		$out = sprintf('<iframe data-video-id="%d" src="https://player.vimeo.com/video/%s" width="640" height="360" frameborder="0" class="cmvl-player-vimeo"'
				. ' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
				$video->getId(),
				htmlspecialchars(urlencode($id))
			);
// 		var_dump($out);
		return $out;
	}
	
	
	protected static function getPlayerWistia(Video $video) {
		return ChannelController::loadView('frontend/playlist/player-wistia', compact('video'));
	}
	
	
	protected static function getPlayerYoutube(Video $video) {
		return '';
	}
	
}