<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\model\Settings;

use com\cminds\videolesson\lib\InstantPayment;

use com\cminds\videolesson\model\Labels;

use com\cminds\videolesson\App;

use com\cminds\videolesson\model\PostType;
use com\cminds\videolesson\model\PostSubscription;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\PostEDDPayment;

class InstantPaymentsController extends Controller {
	
	const NONCE_ACTIVATE = 'cmvl_eddpay_init';
	const NONCE_SET_COSTS = 'cmvl_eddpay_costs_nonce';
	const EDDPAY_CALLBACK_ACTION = 'cmvl_eddpay_payment_completed';
	
	protected static $filters = array(
		'cmvl_format_amount_payed' => array('args' => 2),
		'cmvl_options_config',
		'cmvl_settings_pages',
		'cmvl_settings_pages_groups',
		'cmvl_channel_can_view' => array('args' => 3),
		'cmvl_video_can_view' => array('args' => 3),
		'cmvl_video_player_html' => array('args' => 3),
	);
	
	protected static $actions = array(
		array('name' => 'cmvl_labels_init', 'priority' => 20),
		'cmvl_access_denied_content' => array('method' => 'displayPaybox', 'args' => 1),
		'cmvl_channels_list_row' => array('args' => 1),
		'cmvl_subscriptions_table_row' => array('args' => 1),
		'cmvl_channels_list_header',
		self::EDDPAY_CALLBACK_ACTION => array('args' => 1),
	);
	
	protected static $ajax = array('cmvl_eddpay_purchase');
	
	protected static $suspendActions = 0;
	
	
	
	static function cmvl_labels_init() {
		if (PostEDDPayment::isAvailable()) {
			Labels::loadLabelFile(App::path('asset/labels/instantpayments.tsv'));
		}
	}
	
	
	
	static function cmvl_channel_can_view($result, Channel $channel, $userId) {
		if ($result AND PostEDDPayment::isAvailable()) {
			$instantPayment = new PostEDDPayment($channel);
			if ($instantPayment->isPayed()) {
				$subscription = new PostSubscription($channel);
				$result = $subscription->isSubscriptionActive($userId);
			}
		}
		return $result;
	}
	
	
	static function displayPaybox(Channel $channel = null) {
		echo static::getPayboxView($channel);
	}
	
	
	static function getPayboxView(PostType $post = null) {
		if ($post AND PostEDDPayment::isAvailable() AND $instantPayment = new PostEDDPayment($post) AND $instantPayment->isPayed()) {
			ChannelController::loadAssets();
			if (is_user_logged_in()) {
				if ($subscription = new PostSubscription($post) AND !$subscription->isSubscriptionActive()) {
					$form = self::getPayboxForm($post);
					return self::loadFrontendView('paybox', compact('form'));
				}
			} else {
				return self::loadFrontendView('paybox-guest');
			}
		}
		return '';
	}
	
	
	static function getPayboxForm(PostType $post = null) {
		if ($post AND PostEDDPayment::isAvailable() AND $instantPayment = new PostEDDPayment($post) AND $instantPayment->isPayed()) {
			if (is_user_logged_in()) {
				ChannelController::loadAssets();
				$costs = $instantPayment->getCosts();
				$postId = $post->getId();
				$nonce = wp_create_nonce(self::NONCE_ACTIVATE);
				if ($subscription = new PostSubscription($post) AND !$subscription->isSubscriptionActive()) {
					return self::loadFrontendView('paybox-form', compact('costs', 'postId', 'nonce'));
				}
			}
		}
	}

	
	static function cmvl_eddpay_purchase() {
		header('content-type: application/json');
		
		try {
			
			if (!is_user_logged_in()) throw new \Exception('User is not logged in.');
			if (empty($_POST['callbackUrl'])) throw new \Exception('Missing callback URL.');
			if (empty($_POST['postId'])) throw new \Exception('Missing post ID.');
			$post = PostType::getInstance($_POST['postId']);
			if (!$post) throw new \Exception('Missing post.');
			$subscription = new PostSubscription($post);
			if (!$subscription) throw new \Exception('Invalid PostSubscription instance.');
			$instantPayments = new PostEDDPayment($post);
			if (!$instantPayments) throw new \Exception('Invalid InstantPayments instance.');
			if (!$instantPayments->isPayed()) throw new \Exception('Post is not paid.');
			if (empty($_POST['nonce'])) throw new \Exception('Missing nonce.');
			if (!wp_verify_nonce($_POST['nonce'], self::NONCE_ACTIVATE)) throw new \Exception('Invalid nonce.');
			$costs = $instantPayments->getCosts();
			if (!$costs) throw new \Exception('Missing costs data.');
			if (empty($_POST['edd_download_id'])) throw new \Exception('Missing EDD product ID param.');
			if ($subscription->isSubscriptionActive()) throw new \Exception('Subscription is already active.');
			
			if ($url = $instantPayments->initPayment($_POST['edd_download_id'], $_POST['callbackUrl'])) {
				$response = array('success' => true, 'msg' => Labels::getLocalized('eddpay_checkout_redirection'), 'redirect' => $url);
			} else {
				throw new \Exception('Failed to initialize transaction.');
			}
		} catch (\Exception $e) {
			$response = array('success' => false, 'msg' => Labels::getLocalized($e->getMessage()));
		}
		
		echo json_encode($response);
		exit;
	}
	
	
	static function cmvl_eddpay_payment_completed($args) {
		if (isset($args['subscriptionTimeSec']) AND isset($args['userId'])) {
			
			if (!empty($args['paidPostId'])) {
				$post = PostType::getInstance($args['paidPostId']);
			} else {
				// it's a global purchase for all videos so get any video
				$videos = get_posts(array('post_type' => Video::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => 1));
// 				var_dump($videos);
				$video = reset($videos);
				$post = PostType::getInstance($video);
// 				var_dump($post);
			}
			
			$subscriptionModel = new PostSubscription($post);
			try {
				$subscriptionModel->addSubscription(
					$args['userId'],
					$args['subscriptionTimeSec'],
					$args['price'],
					PostEDDPayment::PAYMENT_PLUGIN_NAME
				);
			} catch (Exception $e) {
			
			}
		}
	}
	
	
// 	static function cmvl_eddpay_callback($params) {
// 		if (isset($params['transactionId']) AND $transactionData = PostEDDPayment::getTransaction($params['transactionId'])) {
// 			$requestedSubscription = $transactionData['transaction']['subscription'];
// 			$subscriptionModel = new PostSubscription($transactionData['post']);
// 			try {
// 				$subscriptionModel->addSubscription(
// 					$requestedSubscription['userId'],
// 					$requestedSubscription['cost']['seconds'],
// 					$transactionData['transaction']['request']['amount'],
// 					PostEDDPayment::PAYMENT_PLUGIN_NAME
// 				);
// 			} catch (Exception $e) {
				
// 			}
// 		}
// 	}
	
	
	static function cmvl_channels_list_header() {
		if (PostEDDPayment::isAvailable()) {
			printf('<th>%s</th>', Labels::getLocalized('lesson_purchase'));
		}
	}
	
	static function cmvl_channels_list_row(Channel $channel) {
		if ($channel AND PostEDDPayment::isAvailable() AND $instantPayment = new PostEDDPayment($channel) AND $instantPayment->isPayed()) {
			if (is_user_logged_in()) {
				echo '<td>' . self::getPayboxForm($channel) . '</td>';
			}
		}
	}
	
	static function cmvl_subscriptions_table_row(array $row) {
		self::cmvl_channels_list_row($row['channel']);
	}
	
	

	static function cmvl_format_amount_payed($amount, $plugin) {
		if ($plugin == PostEDDPayment::PAYMENT_PLUGIN_NAME) {
			$amount = sprintf(Labels::getLocalized('eddpay_amount_payed_format'), $amount);
		}
		return $amount;
	}
	
	

	static function cmvl_settings_pages($categories) {
		if (PostEDDPayment::isAvailable()) {
			$categories['eddpay'] = 'EDD Payments';
		}
		return $categories;
	}
	
	
	static function cmvl_settings_pages_groups($subcategories) {
		if (PostEDDPayment::isAvailable()) {
			$subcategories['eddpay']['eddpay'] = 'EDD Payments';
			$subcategories['eddpay']['pricing'] = 'Price for all video contents';
		}
		return $subcategories;
	}
	
	
	
	static function cmvl_options_config($config) {
		
		if (!PostEDDPayment::isAvailable()) return $config;
		
		if (function_exists('\edd_get_currency')) {
			$currency = \edd_get_currency();
		} else {
			$currency = '';
		}
		
		$config[Settings::OPTION_EDD_PAYMENT_MODEL] = array(
			'type' => Settings::TYPE_RADIO,
			'category' => 'eddpay',
			'subcategory' => 'eddpay',
			'options' => array(
				Settings::EDDPAY_MODEL_ALL_CHANNELS => 'Single payment for all video contents',
				Settings::EDDPAY_MODEL_PER_CHANNEL => 'Separate payment for each lesson or video',
			),
			'default' => Settings::EDDPAY_MODEL_PER_CHANNEL,
			'title' => 'Payments model',
			'desc' => 'Decide whether user is paying for each lesson or video separately or for all lessons and videos at once.'
						. '<br>If chosen "Separate payment" you need to specify the price on each lesson or video edit page.'
						. '<br>If chosen "Single payment" then you can specify the price in the setting below.',
		);
// 		$config[Settings::OPTION_EDD_PRICING_GROUPS] = array(
// 			'type' => Settings::TYPE_MP_PRICE_GROUPS,
// 			'category' => 'eddpay',
// 			'subcategory' => 'pricing',
// 			'title' => 'EDD pricing',
// 			'desc' => 'Those prices are used only when chosen the "All lessons at once" payment model.',
// 			'currency' => $currency,
// 			'currencyStep' => 0.01,
// 		);
		return $config;
	}
	
	
	static function cmvl_video_player_html($html, $video, $atts) {
		
		if ($html AND PostEDDPayment::isAvailable()) {
			$instantPayment = new PostEDDPayment($video);
			if ($instantPayment->isPayed()) {
				$subscription = new PostSubscription($video);
				if (!$subscription->isSubscriptionActive()) {
					$html = static::getPayboxView($video);
				}
			}
		}
		
		return $html;
	}
	
	
	static function cmvl_video_can_view($result, Video $video, $userId) {
		if ($result AND PostEDDPayment::isAvailable()) {
			$instantPayment = new PostEDDPayment($video);
			if ($instantPayment->isPayed()) {
				$subscription = new PostSubscription($video);
				$result = $subscription->isSubscriptionActive($userId);
			}
		}
		return $result;
	}
	
	
	
}