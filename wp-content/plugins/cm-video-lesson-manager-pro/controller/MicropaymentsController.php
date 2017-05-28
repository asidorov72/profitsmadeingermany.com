<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\App;
use com\cminds\videolesson\model\PostSubscription;
use com\cminds\videolesson\model\Labels;
use com\cminds\videolesson\model\Micropayments;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\model\Channel;

class MicropaymentsController extends Controller {
	
	const NONCE_ACTIVATE = 'cmvl_channel_micropayments_activate';

	protected static $filters = array(
		'cmvl_options_config',
		'cmvl_format_amount_payed' => array('args' => 2),
		'cmvl_channel_can_view' => array('args' => 3),
	);
	protected static $actions = array(
		'add_meta_boxes',
		array('name' => 'save_post', 'args' => 1),
		array('name' => 'cmvl_labels_init', 'priority' => 20),
		'cmvl_access_denied_content' => array('method' => 'displayPaybox', 'args' => 1),
		'cmvl_channels_list_header',
		'cmvl_channels_list_row' => array('args' => 1),
		'cmvl_subscriptions_table_row' => array('args' => 1),
	);
	protected static $ajax = array('cmvl_channel_mp_activate');
	protected static $suspendActions = 0;
	
	
	
	static function cmvl_labels_init() {
		if( Micropayments::isMicroPaymentsAvailable() ) {
			Labels::loadLabelFile(App::path('asset/labels/micropayments.tsv'));
		}
	}
	
	
	static function add_meta_boxes() {
		if (Micropayments::isMicroPaymentsAvailable()) {
			add_meta_box( App::prefix('-micropayments-costs'), 'CM MicroPayments Costs', array(get_called_class(), 'channel_costs_meta_box'),
				Channel::POST_TYPE, 'normal', 'high' );
		}
	}
	
	
	static function channel_costs_meta_box($post) {
		if ($channel = Channel::getInstance($post)) {
			$micropaments = new Micropayments($channel);
			$costs = $micropaments->getCosts();
		} else {
			$costs = array();
		}
		wp_enqueue_script('cmvl-backend');
		echo self::loadBackendView('channel-costs-meta-box', compact('costs'));
	}


	static function save_post($post_id) {
		if (!static::$suspendActions AND $channel = Channel::getInstance($post_id)) {
			static::$suspendActions++;
			
			self::save_post_micropayments_costs($channel);
			
			static::$suspendActions--;
		}
	}
	
	

	static protected function save_post_micropayments_costs(Channel $channel) {
		$nonceField = 'cmvl-channel-mp-nonce';
		if (!empty($_POST[$nonceField]) AND wp_verify_nonce($_POST[$nonceField], $nonceField)) {
			$costs = array();
			if (!empty($_POST['cmvl-mp-number']) AND is_array($_POST['cmvl-mp-number'])) {
				foreach ($_POST['cmvl-mp-number'] as $i => $number) {
					if (!empty($_POST['cmvl-mp-cost'][$i]) AND !empty($_POST['cmvl-mp-unit'][$i])) {
						$unit = $_POST['cmvl-mp-unit'][$i];
						if ($seconds = PostSubscription::period2seconds($number . $unit)) { // valid period
							$costs[$seconds] = array(
								'period' => $number .' '. $unit,
								'number' => $number,
								'unit' => $unit,
								'seconds' => $seconds,
								'cost' => $_POST['cmvl-mp-cost'][$i]
							);
						}
					}
				}
			}
			$micropayments = new Micropayments($channel);
			$micropayments->setCosts($costs);
		}
	}


	
	static function displayPaybox(Channel $channel = null) {
		if ($channel AND Micropayments::isMicroPaymentsAvailable() AND $micropaments = new Micropayments($channel) AND $micropaments->isPayed()) {
			ChannelController::loadAssets();
			if (is_user_logged_in()) {
				if ($subscription = new PostSubscription($channel) AND !$subscription->isSubscriptionActive()) {
					$form = self::getPayboxForm($channel);
					echo self::loadFrontendView('paybox', compact('form'));
				}
			} else {
				echo self::loadFrontendView('paybox-guest');
			}
		}
	}
	
	
	static function getPayboxForm(Channel $channel = null) {
		if ($channel AND Micropayments::isMicroPaymentsAvailable() AND $micropaments = new Micropayments($channel) AND $micropaments->isPayed()) {
			if (is_user_logged_in()) {
				ChannelController::loadAssets();
				$costs = $micropaments->getCosts();
				$channelId = $channel->getId();
				$nonce = wp_create_nonce(self::NONCE_ACTIVATE);
				if ($walletPage = Settings::getOption(Settings::OPTION_MP_WALLET_PAGE)) {
					$walletUrl = get_permalink($walletPage);
				} else $walletUrl = null;
				if ($checkoutPage = Settings::getOption(Settings::OPTION_MP_CHECKOUT_PAGE)) {
					$checkoutUrl = get_permalink($checkoutPage);
				} else $checkoutUrl = null;
				if ($subscription = new PostSubscription($channel) AND !$subscription->isSubscriptionActive()) {
					return self::loadFrontendView('paybox-form', compact('costs', 'channelId', 'nonce', 'walletUrl', 'checkoutUrl'));
				}
			}
		}
	}
	
	
	static function cmvl_channel_mp_activate() {
		
		header('content-type: application/json');
			
		try {
			
			if (!is_user_logged_in()) throw new \Exception('User is not logged in.');
			if (empty($_POST['channelId'])) throw new \Exception('Missing lesson ID.');
			$channel = Channel::getInstance($_POST['channelId']);
			if (!$channel) throw new \Exception('Missing lesson.');
			$subscription = new PostSubscription($channel);
			if (!$subscription) throw new \Exception('Invalid PostSubscription instance.');
			$micropaments = new Micropayments($channel);
			if (!$micropaments) throw new \Exception('Invalid Micropayments instance.');
			if (!$micropaments->isPayed()) throw new \Exception('Lesson is not payed.');
			if (empty($_POST['nonce'])) throw new \Exception('Missing nonce.');
			if (!wp_verify_nonce($_POST['nonce'], self::NONCE_ACTIVATE)) throw new \Exception('Invalid nonce.');
			$costs = $micropaments->getCosts();
			if (!$costs) throw new \Exception('Missing Micropayments costs.');
			if (empty($_POST['period'])) throw new \Exception('Missing period param.');
			if (!isset($costs[$_POST['period']])) throw new \Exception('Invalid period.');
			if ($subscription->isSubscriptionActive()) throw new \Exception('Subscription is already active.');
			
			$cost = $costs[$_POST['period']];
			$userId = get_current_user_id();
			$points = $cost['cost'];
			
			if (Micropayments::chargeUserWallet($userId, -$points)) {
				$subscription->addSubscription($userId, $cost['seconds'], $points, Micropayments::PAYMENT_PLUGIN_NAME);
				$response = array(
					'success' => true,
					'msg' => sprintf(Labels::getLocalized('mp_subscription_activate_success'), $points),
					'channelUrl' => $channel->getPermalink(),
				);
			} else {
				throw new \Exception('Failed to charge user\' wallet.');
			}
		} catch (\Exception $e) {
			$response = array('success' => false, 'msg' => Labels::getLocalized($e->getMessage()));
		}
		
		echo json_encode($response);
		exit;
		
	}
	
	
	static function cmvl_channel_can_view($result, Channel $channel, $userId) {
		if ($result AND Micropayments::isMicroPaymentsAvailable()) {
			$micropayments = new Micropayments($channel);
			if ($micropayments->isPayed()) {
				$subscription = new PostSubscription($channel);
				$result = $subscription->isSubscriptionActive($userId);
			}
		}
		return $result;
	}
	
	
	static function cmvl_options_config($config) {
		if( Micropayments::isMicroPaymentsAvailable() ) {
			return array_merge($config, array(
				// Notifications
				Settings::OPTION_NEW_SUB_ADMIN_NOTIF_ENABLE => array(
					'type' => Settings::TYPE_BOOL,
					'default' => 0,
					'category' => 'notifications',
					'subcategory' => 'sub',
					'title' => 'Enable notifications',
				),
				Settings::OPTION_NEW_SUB_ADMIN_NOTIF_EMAILS => array(
					'type' => Settings::TYPE_CSV_LINE,
					'category' => 'notifications',
					'subcategory' => 'sub',
					'title' => 'Emails to notify',
					'desc' => 'Enter comma separated email addresses to send the notification to.',
				),
				Settings::OPTION_NEW_SUB_ADMIN_NOTIF_SUBJECT => array(
					'type' => Settings::TYPE_STRING,
					'category' => 'notifications',
					'subcategory' => 'sub',
					'title' => 'Email subject',
					'desc' => 'You can use following shortcodes:<br />[blogname], [lessonname], [username], [userlogin], [startdate], [enddate], [duration], [points]',
					'default' => '[[blogname]] New subscription for [duration] ([points] points)',
				),
				Settings::OPTION_NEW_SUB_ADMIN_NOTIF_TEMPLATE => array(
					'type' => Settings::TYPE_TEXTAREA,
					'category' => 'notifications',
					'subcategory' => 'sub',
					'title' => 'Email body template',
					'desc' => 'You can use following shortcodes:<br />[blogname], [home], [lessonname], [permalink], [username], [userlogin], [startdate], [enddate],'
						. ' [duration], [points], [reportlink]',
					'default' => "Hi,\nnew subscription has appeared.\n\nWebsite: [blogname]\nWebsite URL: [home]\n"
							. "Lesson: [lessonname]\nLesson link: [permalink]\nUser name: [username]\n"
							. "User login: [userlogin]\nStart date: [startdate]\nEnd date: [enddate]\nDuration: [duration]\nPoints charged: [points]"
								. "\n\nSee the Subscription Report: [reportlink]",
				),
				Settings::OPTION_MP_WALLET_PAGE => array(
					'type' => Settings::TYPE_SELECT,
					'options' => Settings::getPagesOptions() + array(Settings::PAGE_CREATE_KEY => '-- create new page --'),
					'category' => 'micropayments',
					'subcategory' => 'navigation',
					'title' => 'Wallet page',
					'desc' => 'Select page which will display the Micropayments wallet ballance and history (using the cm_user_wallet shortcode) or choose '
						. '"-- create new page --" to create a such page.',
					Settings::PAGE_DEFINITION => array(
						'post_title' => 'Micropayments Wallet',
						'post_content' => '[cm_user_wallet]',
					),
				),
				Settings::OPTION_MP_CHECKOUT_PAGE => array(
					'type' => Settings::TYPE_SELECT,
					'options' => Settings::getPagesOptions() + array(Settings::PAGE_CREATE_KEY => '-- create new page --'),
					'category' => 'micropayments',
					'subcategory' => 'navigation',
					'title' => 'Checkout points page',
					'desc' => 'Select page which will display the Micropayments checkout points form (using the cm_micropayment_checkout shortcode) or choose '
						. '"-- create new page --" to create a such page.',
					Settings::PAGE_DEFINITION => array(
						'post_title' => 'Checkout points',
						'post_content' => '[cm_micropayment_checkout]',
					),
				),
			));
		} else {
			return $config;
		}
	}
	
	static function cmvl_channels_list_header() {
		if (Micropayments::isAvailable()) {
			printf('<th>%s</th>', Labels::getLocalized('lesson_purchase'));
		}
	}
	
	
	static function cmvl_channels_list_row(Channel $channel) {
		if ($channel AND Micropayments::isMicroPaymentsAvailable() AND $micropaments = new Micropayments($channel) AND $micropaments->isPayed()) {
			if (is_user_logged_in()) {
				echo '<td>' . self::getPayboxForm($channel) . '</td>';
			}
		}
	}
	
	
	static function cmvl_subscriptions_table_row(array $row) {
		self::cmvl_channels_list_row($row['channel']);
	}
	
	
	static function cmvl_format_amount_payed($amount, $plugin) {
		if ($plugin == Micropayments::PAYMENT_PLUGIN_NAME) {
			$amount = sprintf(Labels::getLocalized('mp_amount_payed_format'), $amount);
		}
		return $amount;
	}
	
}
