<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\model\PostSubscription;

use com\cminds\videolesson\model\Channel;

use com\cminds\videolesson\model\SubscriptionReport;

use com\cminds\videolesson\model\Labels;

use com\cminds\videolesson\model\Settings;

use com\cminds\videolesson\App;


class SubscriptionsController extends Controller {

	const TITLE = 'Subscriptions';
	const NONCE_ADD = 'cmvl_subscription_add';
	const NONCE_ACTION = 'cmvl_subscription_action';
	
	protected static $actions = array(
		'init',
		array('name' => 'admin_menu', 'priority' => 12),
		'cmvl_channel_playlist_load' => array('args' => 1),
	);
	protected static $ajax = array('cmvl_user_suggest', 'cmvl_post_suggest');
	protected static $filters = array(
		'heartbeat_received' => array('args' => 2),
// 		'cmvl_render_playlist' => array('args' => 2),
	);
	
	
	
	static function init() {
		
	}
	

	static function admin_menu() {
		if (PostSubscription::isAvailable()) {
			add_submenu_page(App::MENU_SLUG, App::getPluginName() . ' ' . static::TITLE, static::TITLE, 'manage_options',
				self::getMenuSlug(), array(get_called_class(), 'render'));
		}
	}
	
	
	static function getMenuSlug() {
		return App::MENU_SLUG . '-subscriptions';
	}


	static function getUrl() {
		return admin_url('admin.php?page='. self::getMenuSlug());
	}
	
	
	
	static function render() {
		
		if ($timezone = get_option('timezone_string')) {
			date_default_timezone_set($timezone);
		}
		
		$filter = array(
			'user_id' => (empty($_GET['user_id']) ? null : $_GET['user_id']),
			'post_id' => (empty($_GET['post_id']) ? null : $_GET['post_id']),
			'status' => (empty($_GET['status']) ? null : $_GET['status']),
		);
		
		$pageUrl = self::getUrl();
		$currentUrl = add_query_arg(urlencode_deep($_GET), $pageUrl);
		
		$limit = 20;
		$page = (empty($_GET['p']) ? 1 : $_GET['p']);
		
		$firstPageArgs = $_GET;
		unset($firstPageArgs['p']);
		$pagination = array(
			'page' => $page,
			'count' => SubscriptionReport::getCount($filter),
			'firstPageUrl' => add_query_arg(urlencode_deep($firstPageArgs), $pageUrl),
		);
		$pagination['lastPage'] = ceil($pagination['count']/$limit);
		
		$viewData = array(
			'pageUrl' => $pageUrl,
			'currentUrl' => $currentUrl,
			'data' => SubscriptionReport::getData($filter, $limit, $page),
			'pageMenuSlug' => self::getMenuSlug(),
			'filter' => $filter,
			'pagination' => $pagination,
			'nonceAdd' => wp_create_nonce(self::NONCE_ADD),
			'nonceAction' => wp_create_nonce(self::NONCE_ACTION),
		);
		$viewData['addForm'] = self::loadBackendView('add', $viewData);
		
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend');
		wp_enqueue_script('suggest');
		wp_enqueue_script('user-suggest');
		echo self::loadView('backend/template', array(
			'title' => App::getPluginName() . ' '. static::TITLE,
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('report', $viewData),
		));
		
	}
	
	
	static function cmvl_user_suggest() {
		
		$field = 'user_login';
		$return = array();
		
		$users = get_users( array(
			'blog_id' => false,
			'search'  => '*' . $_REQUEST['term'] . '*',
// 			'include' => $include_blog_users,
// 			'exclude' => $exclude_blog_users,
			'search_columns' => array( 'user_login', 'user_nicename', 'user_email' ),
		) );
		
		foreach ( $users as $user ) {
			$return[] = array(
				/* translators: 1: user_login, 2: user_email */
				'label' => sprintf( __( '%1$s (%2$s)' ), $user->user_login, $user->user_email ),
				'value' => $user->$field,
			);
		}
		
		wp_die( wp_json_encode( $return ) );
		
	}
	
	
	
	static function cmvl_post_suggest() {
		
		$return = array();
		
		$posts = get_posts(array(
			's' => $_GET['term'],
			'post_type' => Channel::POST_TYPE,
			'numberposts' => 10,
			'nopaging' => true,
		));
		
		foreach ( $posts as $post ) {
			if ($channel = Channel::getInstance($post)) {
				$subscription = new PostSubscription($channel);
				if ($subscription->isPayed()) {
					$return[] = array(
						'label' => sprintf('[%d] %s', $channel->getId(), $channel->getTitle()),
						'value' => $channel->getId(),
					);
				}
			}
		}
		
		wp_die( wp_json_encode( $return ) );
		
	}
	
	
	static protected function canViewPage() {
		$page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		return (is_admin()
			AND current_user_can('manage_options')
			AND $page == 'admin.php'
			AND !empty($_GET['page'])
			AND $_GET['page'] == self::getMenuSlug()
		);
	}
	
	
	static function processRequest() {
		if (self::canViewPage()) {
			if (!empty($_POST) AND !empty($_POST['nonce']) AND wp_verify_nonce($_POST['nonce'], self::NONCE_ADD)) {
				self::processAddRequest();
			}
			else if (!empty($_GET['action']) AND !empty($_GET['nonce']) AND wp_verify_nonce($_GET['nonce'], self::NONCE_ACTION)
				AND !empty($_GET['id']) AND is_numeric($_GET['id'])) {
				switch ($_GET['action']) {
					case 'deactivate':
						self::processDeactivateRequest($_GET['id']);
						break;
					case 'remove':
						self::processRemoveRequest($_GET['id']);
						break;
				}
			}
		}
	}
	
	

	static protected function processDeactivateRequest($metaId) {
		global $wpdb;
		$metaKey = PostSubscription::META_SUBSCRIPTION_END .'_'. $metaId;
		$wpdb->query($wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = UNIX_TIMESTAMP() WHERE meta_key = %s", $metaKey));
		self::redirectAfterAction('Subscription has been deactivated.');
	}
	
	
	static protected function processRemoveRequest($metaId) {
		global $wpdb;
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_id = %d OR meta_key IN (%s, %s, %s, %s)",
			$metaId,
			PostSubscription::META_SUBSCRIPTION_START .'_'. $metaId,
			PostSubscription::META_SUBSCRIPTION_END .'_'. $metaId,
			PostSubscription::META_SUBSCRIPTION_DURATION .'_'. $metaId,
			PostSubscription::META_SUBSCRIPTION_AMOUNT_PAID .'_'. $metaId
		));
		self::redirectAfterAction('Subscription has been removed.');
	}
	
	
	
	static protected function processAddRequest() {
				
		$response = array('success' => 0, 'msg' => 'Failed to add subscription.');
		
		if (!empty($_POST['user_login']) AND $user = get_user_by('login', $_POST['user_login'])) {
			if (!empty($_POST['post_id']) AND $channel = Channel::getInstance($_POST['post_id'])
					AND $subscription = new PostSubscription($channel) AND $subscription->isPayed()) {
				if (!empty($_POST['number']) AND is_numeric($_POST['number'])) {
					if (!empty($_POST['unit'])) {
						
						$seconds = PostSubscription::period2seconds($_POST['number'] . $_POST['unit']);
						
						try {
							$subscription->addSubscription($user->ID, $seconds, 0, PostSubscription::PAYMENT_PLUGIN_ADMIN);
							$response = array(
								'success' => 1,
								'msg' => Labels::__('Subscription has been added.'),
							);
						} catch (\Exception $e) {
							$response['msg'] = $e->getMessage();
						}
						
						
					} else $response['msg'] = 'Invalid unit.';;
				} else $response['msg'] = 'Invalid number.';
			} else $response['msg'] = 'Unknown post.';
		} else $response['msg'] = 'Unknown user login.';
		
		$response['page'] = self::getMenuSlug();
		
		wp_redirect(admin_url('admin.php?' . http_build_query($response)));
		exit;
				
	}
	
	
	static protected function redirectAfterAction($msg) {
		$params = $_GET;
		unset($params['action']);
		unset($params['nonce']);
		unset($params['id']);
		$params['success'] = 1;
		$params['msg'] = $msg;
		wp_redirect(admin_url('admin.php?' . http_build_query($params)));
		exit;
	}
	
	

	static function enqueueLogoutHandler() {
		if (Settings::getOption(Settings::OPTION_RELOAD_EXPIRED_SUBSCRIPTION)) {
			wp_enqueue_script( 'cmvl-logout-heartbeat' );
		}
	}
	
	
	static function heartbeat_received($response, $data) {
		if ( isset($data['cmvl_check_post']) AND is_array($data['cmvl_check_post']) ) {
			$data['cmvl_check_post'] = array_unique($data['cmvl_check_post']);
			$checkPost = true;
			$reload = array();
			foreach ($data['cmvl_check_post'] as $postId) {
				if ($post = Channel::getInstance($postId) AND $sub = new PostSubscription($post) AND $sub->isPayed()) {
// 					$response['cmvl_payed'] = true;
// 					$response['cmvl_loged'] = is_user_logged_in();
// 					$response['cmvl_active'] = $sub->isSubscriptionActive();
					if (!is_user_logged_in() OR !$sub->isSubscriptionActive()) {
						$checkPost = false;
						$reload[] = array('channelId' => $postId, 'url' => $post->getPermalink());
// 						break;
					}
				}
			}
			$response['cmvl_check_post'] = $reload;
		}
		return $response;
	}
	
	
	static function cmvl_channel_playlist_load($channel) {
		self::enqueueLogoutHandler();
	}
	
	
}
