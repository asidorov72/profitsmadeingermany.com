<?php

namespace com\cminds\videolesson\model;

use com\cminds\videolesson\model\Micropayments;

class SubscriptionReport extends Model {
	
	static function getCount($filter) {
		global $wpdb;
		return $wpdb->get_var('SELECT COUNT(*) ' . self::getQuery($filter));
	}
	
	
	static function getData($filter, $limit, $page) {
		global $wpdb;
		$offset = ($page-1)*$limit;
		$sql = 'SELECT
			s.meta_id,
			p.ID AS post_id,
			p.post_title,
			p.post_type,
			pparent.ID AS parent_post_id,
			pparent.post_title AS parent_post_title,
			pparent.post_type AS parent_post_type,
			s.meta_value AS user_id,
			u.display_name AS user_name,
			start.meta_value AS start,
			end.meta_value AS end,
			duration.meta_value AS duration,
			amount.meta_value AS amount,
			paymentplugin.meta_value AS paymentplugin' .
			self::getQuery($filter) . "
			GROUP BY s.meta_id, s.post_id, s.meta_value
			ORDER BY s.meta_id DESC
			LIMIT $limit OFFSET $offset";
		$result = $wpdb->get_results($sql, ARRAY_A);
		foreach ($result as &$row) {
			if (empty($row['paymentplugin'])) {
				$row['paymentplugin'] = Micropayments::PAYMENT_PLUGIN_NAME;
			}
			if (isset($row['end'])) {
				$row['end'] = intval($row['end']);
			}
		}
		return $result;
	}
	
	
	static protected function getQuery($filter) {
		global $wpdb;
		
		$filterMap = array('user_id' => 's.meta_value', 'post_id' => 's.post_id');
		$filterQuery = '';
		foreach ($filter as $key => $val) {
			if (!empty($val) AND isset($filterMap[$key])) {
				$filterQuery .= $wpdb->prepare(' AND '. $filterMap[$key] .' = %s', $val);
			}
		}
		if (!empty($filter['status'])) {
			if ($filter['status'] == 'active') {
				$filterQuery .= ' AND start.meta_value < UNIX_TIMESTAMP() AND end.meta_value > UNIX_TIMESTAMP()';
			} else {
				$filterQuery .= ' AND (start.meta_value > UNIX_TIMESTAMP() OR end.meta_value < UNIX_TIMESTAMP())';
			}
		}
		
		return $wpdb->prepare("
				FROM $wpdb->postmeta s
				JOIN $wpdb->users u ON u.ID = s.meta_value
				JOIN $wpdb->posts p ON p.ID = s.post_id
				LEFT JOIN $wpdb->posts pparent ON p.post_parent = pparent.ID
				JOIN $wpdb->postmeta start ON start.meta_key = CONCAT(%s, s.meta_id)
				JOIN $wpdb->postmeta end ON end.meta_key = CONCAT(%s, s.meta_id)
				JOIN $wpdb->postmeta duration ON duration.meta_key = CONCAT(%s, s.meta_id)
				JOIN $wpdb->postmeta amount ON amount.meta_key = CONCAT(%s, s.meta_id)
				LEFT JOIN $wpdb->postmeta paymentplugin ON paymentplugin.meta_key = CONCAT(%s, s.meta_id)
				WHERE s.meta_key = %s $filterQuery",
			PostSubscription::META_SUBSCRIPTION_START .'_',
			PostSubscription::META_SUBSCRIPTION_END .'_',
			PostSubscription::META_SUBSCRIPTION_DURATION .'_',
			PostSubscription::META_SUBSCRIPTION_AMOUNT_PAID .'_',
			PostSubscription::META_SUBSCRIPTION_PAYMENT_PLUGIN .'_',
			PostSubscription::META_SUBSCRIPTION
		);
		
	}
	
	
	static function getUserSubscriptions($userId) {
		return self::getData(array('user_id' => $userId), 999, 1);
	}
	
	
}