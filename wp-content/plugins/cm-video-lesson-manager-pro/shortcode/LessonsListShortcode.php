<?php

namespace com\cminds\videolesson\shortcode;

use com\cminds\videolesson\model\PostSubscription;

use com\cminds\videolesson\controller\ChannelController;

use com\cminds\videolesson\model\Channel;

class LessonsListShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cmvl-lessons-list';
	
	
	static function shortcode($atts = array()) {
		
		$atts = shortcode_atts(array(
			'subscription' => '',
		), $atts);
		
		$channels = Channel::getAll();
		
		if ($atts['subscription'] == 'active' OR $atts['subscription'] == 'inactive') {
			$channels = array_filter($channels, function(Channel $channel) use ($atts) {
				$subscription = new PostSubscription($channel);
				return ($subscription->isPayed() AND ($atts['subscription'] == 'active') == $subscription->isSubscriptionActive());
			});
		}
		
		ChannelController::loadAssets();
		return ChannelController::loadFrontendView('channels-list', compact('channels', 'atts'));
	}
	
	
}
