<?php

namespace com\cminds\videolesson\model;

use com\cminds\videolesson\App;


class Micropayments extends Model implements IPaymentMethod {
	
	const META_MP_COSTS = 'cmvl_mp_costs';
	const META_MP_SUBSCRIPTION = 'cmvl_mp_subscription';
	const META_MP_SUBSCRIPTION_START = 'cmvl_mp_subscription_start';
	const META_MP_SUBSCRIPTION_END = 'cmvl_mp_subscription_end';
	const META_MP_SUBSCRIPTION_DURATION = 'cmvl_mp_subscription_duration';
	const META_MP_SUBSCRIPTION_POINTS = 'cmvl_mp_subscription_points';
	
	const PAYMENT_PLUGIN_NAME = 'CM MicroPayments';

	protected $post;

	protected static $checkConfigFilters = array(
		'cm_micropayments_are_points_defined' => 'Points prices have to be defined to purchase points by users.',
		'cm_micropayments_are_wallets_assigned' => 'The "Assign wallets to customers" option has to be enabled.',
		'cm_micropayments_are_paypal_settings_defined' => 'PayPal settings are not defined.',
		'cm_micropayments_is_wallet_page' => 'The Wallet page is not defined.',
		'cm_micropayments_is_checkout_page' => 'The Checkout page is not defined.',
	);


	static function init() {
		
		parent::init();
		
		if (function_exists('CMMicropaymentPlatformInit')) {
			CMMicropaymentPlatformInit();
		}

		if( static::isMicroPaymentsAvailable() ) { // Setup backend hooks
			
			add_filter('cmvl_settings_pages', function($pages) {
				$lastVal = end($pages);
				$lastKey = key($pages);
				array_pop($pages);
				$pages['micropayments'] = 'MicroPayments';
				$pages[$lastKey] = $lastVal;
				return $pages;
			});

			add_filter('cmvl_settings_pages_groups', function($subcategories) {
				$subcategories['micropayments']['navigation'] = 'Navigation';
				return $subcategories;
			});
			
			if (static::isAvailable()) { // Setup frontend hooks

				

			} else {
				add_action( 'admin_notices', function() {
					if (Micropayments::isMicroPaymentsAvailable()) {
						Micropayments::displayAdminWarning();
					}
				});
			}
						
		}

	}
	
	
	function __construct(PostType $post) {
		$this->post = $post;
	}

	

	function setCosts($costs) {
		update_post_meta($this->post->getId(), self::META_MP_COSTS, $costs);
		return $this;
	}
	
	
	function getCosts() {
		return get_post_meta($this->post->getId(), self::META_MP_COSTS, $single = true);
	}
	
	
	function isPayed() {
		$micropaymentsCosts = $this->getCosts();
		return (!empty($micropaymentsCosts));
	}
	
		
	
	static function isAvailable() {
		return (static::isMicroPaymentsConfigured());
	}
	
	
	

	static function displayAdminWarning($class = null) {
		if (empty($class)) $class = 'error';
		$reasons = '';
		foreach (static::$checkConfigFilters as $filter => $msg) {
			if (!apply_filters($filter, FALSE)) {
				$reasons .= sprintf('<li>%s</li>', __($msg));
			}
		}
		if ($reasons) {
			printf('<div class="%s"><p>%s</p><ul style="list-style:disc;margin:0 0 1em 2em;">%s</ul><p>%s</p></div>',
			esc_attr($class),
			sprintf(__('<strong>%s</strong> would not integrate with the <strong>CM Micropayments</strong> plugin because of the following reasons:'), App::getPluginName()),
			$reasons,
			sprintf('<a href="%s" class="button">%s</a>',
			esc_attr(admin_url('admin.php?page=cm-micropayment-platform-settings')),
			__('CM Micropayments Settings')
			)
			);
		}
	}


	/**
	 * Check whether MicroPayments platform is available and configured.
	 *
	 * @return boolean
	 */
	static function isMicroPaymentsAvailable()
	{
		return apply_filters('cm_micropayments_is_working', FALSE);
	}


	static function isMicroPaymentsConfigured() {
		if (static::isMicroPaymentsAvailable()) {
			foreach (static::$checkConfigFilters as $filter => $msg) {
				if (!apply_filters($filter, FALSE)) return false;
			}
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Check if wallet assigned to given user ID exists.
	 *
	 * @param int $userId
	 * @return boolean
	 */
	static function checkUsersWalletExists($userId)
	{
		$userWallet = apply_filters('cm_micropayments_user_wallet_id', $userId);
		return !empty($userWallet);
	}

	/**
	 * Check if user has enough points.
	 *
	 * @param int $userId
	 * @param int $points
	 */
	static function hasUserEnoughPoints($userId, $points)
	{
		if( $user = get_user_by('id', $userId) )
		{
			$result = apply_filters('user_has_enough_points', array('username' => $user->user_login, 'points' => abs($points)));
			return (!empty($result['success']));
		}
		return false;
	}

	/**
	 * Charge user wallet.
	 *
	 * @param int $userId
	 * @param int $points Positive or negative integer or zero.
	 * @throws com\cminds\videolesson\model\NotEnoughPointsException
	 */
	static function chargeUserWallet($userId, $points)
	{
		if( !static::checkUsersWalletExists($userId) )
		{
			throw new MissingUserWalletException;
		}
		if( $points < 0 )
		{
			if( !static::hasUserEnoughPoints($userId, abs($points)) )
			{
				throw new NotEnoughPointsException;
			}
		}
		$args = array('user_id' => $userId, 'amount' => $points);
		$result = apply_filters('charge_user_wallet', $args);
		if( $result )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function getUsersWalletURL()
	{
		return apply_filters('cm_micropayments_user_wallet_url', array());
	}

	public function getPointsPurchaseURL()
	{
		return apply_filters('cm_micropayments_checkout_url', array());
	}


}

// ------------------------------------------------------------------------------------------------------------------------------
// Exceptions


class MicropaymentsException extends \Exception
{
	const ERROR_MSG = 'An error occured in the CM MicroPayments module. Please try again.';

	function __construct()
	{
		parent::__construct(Labels::getLocalized(static::ERROR_MSG));
	}

}

class NotEnoughPointsException extends MicropaymentsException
{
	const ERROR_MSG = 'mp_error_not_enough_points';

}

class MissingUserWalletException extends MicropaymentsException
{
	const ERROR_MSG = 'mp_error_wallet_not_exists';

}
