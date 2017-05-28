<?php

namespace com\cminds\videolesson\addon\eddpay\controller;

use com\cminds\videolesson\addon\eddpay\helper\TimeHelper;

use com\cminds\videolesson\addon\eddpay\model\Download;

use com\cminds\videolesson\addon\eddpay\App;

class PluginController extends Controller {
	
// 	static $actions = array('init');
	static $filters = array(
		'cm_edd_pay_available' => array('method' => 'isAvailable'),
		'cmvl_eddpay_global_prices',
		'cmvl_eddpay_post_prices' => array('args' => 2),
		'cmvl_eddpay_get_price_by_id' => array('args' => 2),
		'cmvl_eddpay_get_subscription_time_by_download_id' => array('args' => 2),
	);
	
	
	
	
	static function isAvailable($result) {
		return App::isAvailable();
	}
	
	
	static function displayTheInstructions() {
		$content = '';
		ob_start();
		?>
		
			<h2>Video Lessons EDD Payments Usage Instructions</h2>
	
			<?php echo do_shortcode('[cminds_free_ads]'); ?>
			<div><p>This plugin is a addon to the CM Video Lessons Manager plugin. To make it work you need first to
				<a href="http://downloads.wordpress.org/plugin/easy-digital-downloads.latest-stable.zip">install EDD plugin</a>.
				After EDD installation and configuration the plugin will work with CM Video Lessons Manager plugin to support direct
				payment using EDD cart and checkout. Direct payments means that you can pay  for viewing a video channel in
				<a href="https://www.cminds.com/store/purchase-cm-video-lessons-manager-plugin-for-wordpress/">CM Video lessons manager</a>
				while using all EDD services such as coupons, reporting and more.</p><p>The CM Video Lessons EDD payments does not have
				any settings. All you need to do is activated the license for it to work properly. </div>
	
			<?php
			$content = ob_get_clean();
			echo $content;
	}
	
	

	static function cmvl_eddpay_global_prices($result) {
		if (!is_array($result)) $result = array();
		$downloads = Download::getAllGlobal();
		foreach ($downloads as $price) {
			$seconds = $price->getSubscriptionTimeSec();
			$periodNumber = $price->getSubscriptionPeriodNumber();
			$unit = $price->getSubscriptionPeriodUnit();
			$result[$seconds] = array(
				'period' => $periodNumber . $unit,
				'periodLabel' => TimeHelper::seconds2period($seconds),
				'number' => $periodNumber,
				'unit' => $unit,
				'seconds' => $seconds,
				'cost' => $price->getPrice(),
				'edd_download_id' => $price->getId(),
			);
		}
		return $result;
	}
	
	

	static function cmvl_eddpay_post_prices($result, $paidPostId) {
		if (!is_array($result)) $result = array();
		$downloads = Download::getForPaidPost($paidPostId);
		foreach ($downloads as $price) {
			$seconds = $price->getSubscriptionTimeSec();
			$periodNumber = $price->getSubscriptionPeriodNumber();
			$unit = $price->getSubscriptionPeriodUnit();
			$result[$seconds] = array(
				'period' => $periodNumber . $unit,
				'periodLabel' => TimeHelper::seconds2period($seconds),
				'number' => $periodNumber,
				'unit' => $unit,
				'seconds' => $seconds,
				'cost' => $price->getPrice(),
				'edd_download_id' => $price->getId(),
			);
		}
		return $result;
	}
	
	
	static function cmvl_eddpay_get_price_by_id($result, $eddDownloadId) {
		if ($download = Download::getInstance($eddDownloadId)) {
			$result = $download->getPrice();
		}
		return $result;
	}
	
	
	static function cmvl_eddpay_get_subscription_time_by_download_id($result, $eddDownloadId) {
		if ($download = Download::getInstance($eddDownloadId)) {
			$result = $download->getSubscriptionTimeSec();
		}
		return $result;
	}
	
}
