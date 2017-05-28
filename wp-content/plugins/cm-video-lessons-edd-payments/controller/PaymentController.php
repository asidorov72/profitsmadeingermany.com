<?php

namespace com\cminds\videolesson\addon\eddpay\controller;

use com\cminds\videolesson\addon\eddpay\model\Download;

use com\cminds\videolesson\addon\eddpay\App;

class PaymentController extends Controller {
	
	const SESSION_TRANSACTION = 'cmvl_eddpay_transaction';
	const PAYMENT_META_DETAILS_KEY		 = 'cm_edd_payment_transaction_details';
	const DEFAULT_EDDPAY_CALLBACK_ACTION = 'cmvl_eddpay_payment_completed';
	
	
	static $actions = array(
		'plugins_loaded',
		'edd_payment_receipt_after' => array('args' => 2),
	);
	
	
	
	public static function plugins_loaded() {
		
		if ( !App::isAvailable() ) {
			return;
		}
		
		// API
		add_filter( 'cmvl_eddpay_init_transaction', array( __CLASS__, 'initTransaction' ), 10, 2 );
		
		// EDD hooks
		add_action( 'edd_update_payment_status', array( __CLASS__, 'onEDDStatusUpdate' ), 100, 3 );
		
	}
	
	
	static function initTransaction($response, $request) {
		if (isset($request['edd_download_id']) AND isset($request['callbackAction']) AND isset($request['paidPostId'])) {
			
			edd_add_to_cart($request['edd_download_id'], array('cmvl_request' => $request));
			\EDD()->session->set( self::PAYMENT_META_DETAILS_KEY, $request );
			
			return array(
				'success' => true,
				'redirectionUrl' => edd_get_checkout_uri(), //self::addToCartUrl($request),
			);
			
		} else {
			return array('success' => false);
		}
	}
	
	
	static function addToCartUrl($request) {
		$args = array(
			'download_id' => $request['edd_download_id'],
			'edd_action' => 'add_to_cart',
		);
		if (isset($request['backlinkUrl'])) {
			$args['backlinkUrl'] = $request['backlinkUrl'];
			if (isset($request['backlinkText'])) {
				$args['backlinkText'] = $request['backlinkText'];
			}
		}
		return add_query_arg($args, edd_get_checkout_uri());
	}
	
	static function onEDDStatusUpdate($payment_id, $new_status, $old_status) {
		/*
		 *  Make sure that payments are only completed once
		*/
		if ( $old_status == 'publish' || $old_status == 'complete' ) {
			return;
		}
		
		/*
		 *  Make sure the payment completion is only processed when new status is complete
		*/
		if ( $new_status != 'publish' && $new_status != 'complete' ) {
			return;
		}
		
// 		$transaction = edd_get_payment_meta( $payment_id, self::PAYMENT_META_DETAILS_KEY, true );

		$payment = new \EDD_Payment($payment_id);
		$userId = $payment->user_id;
		
		// You can add multiple channels or videos to a single payment so process each of them separately:
// 		var_dump($payment->downloads);exit;
		foreach ($payment->downloads as $download) {
			$downloadObj = Download::getInstance($download['id']);
// 			var_dump($downloadObj);
// 			var_dump($downloadObj->isVideoLessonsPrice());
			if ($downloadObj) {
				if ($downloadObj->isVideoLessonsPrice()) { // it's a CMVL item:
					
					$args = array(
						'userId' => $userId,
						'paidPostId' => $downloadObj->getPaidPostId(),
						'subscriptionTimeSec' => $downloadObj->getSubscriptionTimeSec(),
						'eddDownloadId' => $downloadObj->getId(),
						'price' => $downloadObj->getPrice(),
					);
					
					if (isset($download['options']['cmvl_request']['callbackAction'])) {
						$callbackAction = $download['options']['cmvl_request']['callbackAction'];
					} else {
						$callbackAction = static::DEFAULT_EDDPAY_CALLBACK_ACTION;
					}
// 					var_dump($callbackAction);
// 					var_dump($args);
					do_action($callbackAction, $args);
					
				} else {
					// it's not CMVL product
				}
			}
		}
// 		exit;
		
	}
	
	
	static function edd_payment_receipt_after($payment, $edd_receipt_args) {
		$transaction = \EDD()->session->get( self::PAYMENT_META_DETAILS_KEY );
		if ($transaction AND isset($transaction[ 'backlinkUrl' ]) AND isset($transaction[ 'backlinkText' ])) {
			printf('<p><a href="%s" class="cmvl-eddpay-backlink">%s</a></p>', esc_attr($transaction[ 'backlinkUrl' ]), $transaction[ 'backlinkText' ]);
		}
	}
	
	
}
