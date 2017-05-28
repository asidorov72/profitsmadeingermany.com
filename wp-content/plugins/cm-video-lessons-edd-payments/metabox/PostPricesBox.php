<?php

namespace com\cminds\videolesson\addon\eddpay\metabox;

use com\cminds\videolesson\addon\eddpay\helper\TimeHelper;

use com\cminds\videolesson\addon\eddpay\controller\SettingsController;

use com\cminds\videolesson\addon\eddpay\model\Download;

use com\cminds\videolesson\addon\eddpay\model\Channel;
use com\cminds\videolesson\addon\eddpay\model\Video;

class PostPricesBox extends MetaBox {

	const SLUG = 'cmvl-eddpay-prices';
	const NAME = 'CM Video Lessons EDD Payments';
	
	static protected $supportedPostTypes = array(Channel::POST_TYPE, Video::POST_TYPE);
	
	
	static function render($post) {
		
		wp_enqueue_style('cmeddpay-backend');
		wp_enqueue_script('cmeddpay-backend');
		
		static::renderNonceField($post);
		$downloads = Download::getForPaidPost($post->ID);
		echo SettingsController::loadBackendView('global-costs-field', compact('downloads'));
		
	}
	
	
	static function savePost($post_id) {
		if ($post_id) {
			
			if (isset($_POST[SettingsController::FIELD_GLOBAL_COSTS]) AND !empty($_POST[SettingsController::FIELD_GLOBAL_COSTS]['edd_id']) AND is_array($_POST[SettingsController::FIELD_GLOBAL_COSTS]['edd_id'])) {
				$ids = $_POST[SettingsController::FIELD_GLOBAL_COSTS]['edd_id'];
				array_shift($ids);
				Download::archiveMissingIds($post_id, $ids);
				foreach ($_POST[SettingsController::FIELD_GLOBAL_COSTS]['edd_id'] as $i => $edd_id) {
					if ($i == 0) continue;
					$periodNumber = $_POST[SettingsController::FIELD_GLOBAL_COSTS]['period'][$i];
					$periodUnit = $_POST[SettingsController::FIELD_GLOBAL_COSTS]['unit'][$i];
					$subscriptionTimeSec = TimeHelper::period2seconds($periodNumber . $periodUnit);
					$price = $_POST[SettingsController::FIELD_GLOBAL_COSTS]['price'][$i];
					if ($edd_id) {
						$download = Download::getInstance($edd_id);
						$download->setPrice($price);
						$download->setSubscriptionTime($subscriptionTimeSec);
					} else {
						$download = Download::create($subscriptionTimeSec, $price, $post_id);
					}
				}
			}
			
		}
	}
	
}