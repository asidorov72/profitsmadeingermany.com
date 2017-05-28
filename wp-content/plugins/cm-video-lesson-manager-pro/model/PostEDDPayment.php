<?php

namespace com\cminds\videolesson\model;

use com\cminds\videolesson\controller\InstantPaymentsController;

class PostEDDPayment extends Model implements IPaymentMethod {
	
	const PAYMENT_PLUGIN_NAME = 'CM Instant EDD Payments';
	
	const META_TRANSACTIONS_PREFIX = 'cmvl_eddpay_transaction_';
	
	protected $post;
	

	function __construct(PostType $post) {
		$this->post = $post;
	}
	
	
	function isPayed() {
		$cost = $this->getCosts();
		return !empty($cost);
	}
	
	
	function getCosts() {
		switch (Settings::getOption(Settings::OPTION_EDD_PAYMENT_MODEL)) {
			case Settings::EDDPAY_MODEL_ALL_CHANNELS:
				return static::getGlobalCosts();
				break;
			case Settings::EDDPAY_MODEL_PER_CHANNEL:
			default:
				return $this->getPostCosts();
		}
	}
	
	
	function getPostCosts() {
		$result = apply_filters('cmvl_eddpay_post_prices', array(), $this->post->getId());
		return $result;
	}
	
	
	static function getGlobalCosts() {
		$result = apply_filters('cmvl_eddpay_global_prices', array());
		return $result;
	}
	
	
	function setCosts($cost) {
		
	}
	
	
	function initPayment($eddDownloadId, $callbackUrl) {
		$price = apply_filters('cmvl_eddpay_get_price_by_id', 0, $eddDownloadId);
		$subscriptionTime = apply_filters('cmvl_eddpay_get_subscription_time_by_download_id', '', $eddDownloadId);
		$periodLabel = PostSubscription::seconds2period($subscriptionTime);
		$subscription = array(
			'userId' => get_current_user_id(),
			'cost' => $price,
			'initTime' => time(),
			'paidPostId' => $this->post->getId(),
		);
		$request = array(
			'paidPostId' => $this->post->getId(),
			'userId' => get_current_user_id(),
			'edd_download_id' => $eddDownloadId,
			'label' => $this->getTransactionLabel($periodLabel),
			'callbackAction' => InstantPaymentsController::EDDPAY_CALLBACK_ACTION,
			'callbackUrl' => $callbackUrl,
			'backlinkUrl' => $callbackUrl,
			'backlinkText' => sprintf(Labels::getLocalized('eddpay_receipt_backlink'), $this->post->getTitle()),
		);
		$response = apply_filters('cmvl_eddpay_init_transaction', false, $request);
		if ($response AND is_array($response) AND !empty($response['success']) AND !empty($response['redirectionUrl'])) {
			return $response['redirectionUrl'];
		}
	}
	
	
	protected function registerTransaction($subscription, $request, $response) {
		add_post_meta(
			$this->post->getId(),
			self::META_TRANSACTIONS_PREFIX . $response['transactionId'],
			compact('request', 'response', 'subscription'),
			$unique = false
		);
	}
	
	
	static function getTransaction($transactionId) {
		global $wpdb;
		$meta = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key = %s", self::META_TRANSACTIONS_PREFIX . $transactionId), ARRAY_A);
		if ($meta AND $post = get_post($meta['post_id'])) {
			$transaction = unserialize($meta['meta_value']);
			if ($post->post_type == Channel::POST_TYPE) {
				$post = Channel::getInstance($post);
			}
			else if ($post->post_type == Video::POST_TYPE) {
				$post = Video::getInstance($post);
			}
			if (empty($post)) return null;
			return compact('meta', 'post', 'transaction');
		}
	}
	
	
	function getTransactionLabel($periodLabel) {
		switch (Settings::getOption(Settings::OPTION_EDD_PAYMENT_MODEL)) {
			case Settings::EDDPAY_MODEL_ALL_CHANNELS:
				return sprintf(Labels::getLocalized('eddpay_transaction_all_at_once_label'), $periodLabel);
				break;
			case Settings::EDDPAY_MODEL_PER_CHANNEL:
			default:
				return sprintf(Labels::getLocalized('eddpay_transaction_label'), $this->post->getTitle(), $periodLabel);
		}
	}
	
	
	static function isAvailable() {
		return apply_filters('cm_edd_pay_available', false);
	}
	
}