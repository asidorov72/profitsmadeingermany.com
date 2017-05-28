<?php

namespace com\cminds\videolesson\controller;
use com\cminds\videolesson\App;
use com\cminds\videolesson\model\IAutocompleteModel;

class AutocompleteController extends Controller {
	
	const NONCE_ADMIN = 'cmvl_autocomplete_admin_nonce';
	const NONCE_FRONTEND = 'cmvl_autocomplete_frontend_nonce';
	
	static $ajax = array(
		'cmvl_autocomplete_admin',
		'cmvl_autocomplete_frontend',
	);
	
	
	
	static function cmvl_autocomplete_admin() {
		if (is_user_logged_in() AND current_user_can('manage_options')) {
			if ($nonce = filter_input(INPUT_POST, 'nonce') AND wp_verify_nonce($nonce, static::NONCE_ADMIN)) {
				static::processAutocompleteRequest();
			}
		}
	}
	
	
	protected static function processAutocompleteRequest() {
		
		header('content-type: application/json');
		
		$model = preg_replace('~[^a-z]~i', '', filter_input(INPUT_POST, 'model'));
		$search = filter_input(INPUT_POST, 'search');
		$orderby = filter_input(INPUT_POST, 'orderby');
		$order = filter_input(INPUT_POST, 'order');
		$limit = filter_input(INPUT_POST, 'limit') ?: 10;
		
		$className = App::namespaced('model\\' . $model);
		if (class_exists($className) AND is_a($className, App::namespaced('model\IAutocompleteModel'), true)) {
			$results = call_user_func(array($className, 'getAutocompleteResults'), $search, $orderby, $order, $limit);
			echo json_encode(array('success' => true, 'results' => $results));
		}
		
		exit;
		
	}
	
	
	
	static function getFieldAdmin($placeholder, $model, $callbackFunctionName) {
		wp_enqueue_script('cmvl-autocomplete');
		$nonce = wp_create_nonce(static::NONCE_ADMIN);
		$action = 'cmvl_autocomplete_admin';
		return static::loadBackendView('search-field', compact('placeholder', 'model', 'nonce', 'action', 'callbackFunctionName'));
	}
	
	
}