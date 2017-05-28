<?php

namespace com\cminds\videolesson\addon\eddpay\controller;

use com\cminds\videolesson\addon\eddpay\helper\TimeHelper;

use com\cminds\videolesson\addon\eddpay\model\Channel;

use com\cminds\videolesson\addon\eddpay\model\Download;

class UpdateController extends Controller {
	
	const OPTION_NAME = 'cmvleddpay_update_methods';
	
	static $actions = array('plugins_loaded');

	static function plugins_loaded() {
		global $wpdb;
		
		if (defined('DOING_AJAX') AND DOING_AJAX) return;
		
		
		$updates = get_option(static::OPTION_NAME);
		if (empty($updates)) $updates = array();
		$count = count($updates);
		
		$methods = get_class_methods(__CLASS__);
		foreach ($methods as $method) {
			if (preg_match('/^update((_[0-9]+)+)/', $method, $match)) {
				if (!in_array($method, $updates)) {
					call_user_func(array(__CLASS__, $method));
					$updates[] = $method;
				}
			}
		}
		
		if ($count != count($updates)) {
			update_option(static::OPTION_NAME, $updates);
		}
		
		
		if (isset($_GET['cmvl_edd_action']) AND md5($_GET['cmvl_edd_action'] . 'cm') == '571bfd7b5bc114ae1cadceff8a531f63') {
			self::update_2_0_0();
		}
		
		
	}
	
	
	/**
	 * Migrate costs settings from wp_options and post_meta into EDD Downloads (Products).
	 */
	static function update_2_0_0() {
		global $wpdb;
		
		$OPTION_EDD_PAYMENT_MODEL = 'cmvl_edd_payment_model';
		$OPTION_EDD_PRICING_GROUPS = 'cmvl_edd_pricing_groups';
		
		// Migrate channel prices
		$channelsCosts = $wpdb->get_results($wpdb->prepare("SELECT m.post_id, m.meta_value FROM $wpdb->postmeta m
			JOIN $wpdb->posts p ON p.ID = m.post_id
			WHERE m.meta_key = %s AND p.post_type = %s
			", 'cmvl_eddpay_cost', Channel::POST_TYPE), ARRAY_A);
		foreach ($channelsCosts as $channelCost) {
			$costs = unserialize($channelCost['meta_value']);
			if ($costs AND is_array($costs)) {
				foreach ($costs as $cost) {
					Download::create($cost['seconds'], $cost['cost'], $channelCost['post_id']);
				}
			}
		}
		
		
		// Migrate global prices
		$costs = get_option($OPTION_EDD_PRICING_GROUPS);
		if (!empty($costs) AND is_array($costs)) {
			$costs = reset($costs);
			if (!empty($costs['prices'])) {
				foreach ($costs['prices'] as $cost) {
					$subscriptionTimeSec = TimeHelper::period2seconds($cost['number'] . $cost['unit']);
					Download::create($subscriptionTimeSec, $cost['price']);
				}
			}
		}
			
		
	}
	
	
	static function update_2_1_0() {
		global $wpdb;
		
		$sql = $wpdb->prepare("SELECT pm.meta_id
				FROM $wpdb->postmeta pm
				JOIN $wpdb->posts p ON p.ID = pm.post_id
				WHERE pm.meta_key = %s AND p.post_type = %s",
				'cmvl_channel_id', 'download');
		$ids = $wpdb->get_col($sql);
		
		if ($ids) {
			$res = $wpdb->query($wpdb->prepare("UPDATE $wpdb->postmeta SET meta_key = %s WHERE meta_id IN (". implode(',', $ids) .")", 'cmvl_paid_post_id'));
		}
		
		
	}
	
		
}
