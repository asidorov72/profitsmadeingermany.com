<?php

namespace com\cminds\videolesson\addon\eddpay\controller;

use com\cminds\videolesson\addon\eddpay\helper\TimeHelper;

use com\cminds\videolesson\addon\eddpay\model\Download;

use com\cminds\videolesson\addon\eddpay\model\Settings;

use com\cminds\videolesson\addon\eddpay\App;

class SettingsController extends Controller {
	
	const FIELD_GLOBAL_COSTS = 'cmvl_eddpay_costs';

	static $actions = array(
		'cmvl_settings_save',
	);
	static $filters = array(
		'cmvl_options_config',
	);

	
	
	static function cmvl_options_config($config) {
		
		if (App::isAvailable()) {
			
			$config[Settings::OPTION_EDD_GLOBAL_COSTS] = array(
				'type' => Settings::TYPE_CUSTOM,
				'title' => 'EDD products',
				'category' => 'eddpay',
				'subcategory' => 'pricing',
				'desc' => 'Those products are used only when chosen the "Single payment for all video contents" payment model.',
				'content' => array(App::namespaced('controller\SettingsController'), 'getGlobalCostsField'),
			);
			
		}
		
		return $config;
		
	}
	
	
	
	static function getGlobalCostsField() {
		wp_enqueue_script('cmeddpay-backend');
		$downloads = Download::getAllGlobal();
		return self::loadBackendView('global-costs-field', compact('downloads'));
	}
	
	
	static function cmvl_settings_save() {
		if (isset($_POST[self::FIELD_GLOBAL_COSTS]) AND !empty($_POST[self::FIELD_GLOBAL_COSTS]['edd_id']) AND is_array($_POST[self::FIELD_GLOBAL_COSTS]['edd_id'])) {
			$ids = $_POST[self::FIELD_GLOBAL_COSTS]['edd_id'];
			array_shift($ids);
			Download::archiveMissingIds(0, $ids);
			foreach ($_POST[self::FIELD_GLOBAL_COSTS]['edd_id'] as $i => $edd_id) {
				if ($i == 0) continue;
				$periodNumber = $_POST[self::FIELD_GLOBAL_COSTS]['period'][$i];
				$periodUnit = $_POST[self::FIELD_GLOBAL_COSTS]['unit'][$i];
				$subscriptionTimeSec = TimeHelper::period2seconds($periodNumber . $periodUnit);
				$price = $_POST[self::FIELD_GLOBAL_COSTS]['price'][$i];
				if ($edd_id) {
					$download = Download::getInstance($edd_id);
					$download->setPrice($price);
					$download->setSubscriptionTime($subscriptionTimeSec);
				} else {
					$download = Download::create($subscriptionTimeSec, $price);
				}
			}
		}
	}
	
	
	
	
}