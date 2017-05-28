<?php

namespace com\cminds\videolesson\helper;

use com\cminds\videolesson\App;

abstract class TermCustomField {
	
	const SLUG = '';
	const NAME = '';
	const FIELD_PRIORITY = 10;
	const SAVE_TERM_PRIORITY = 10;
	
	static protected $supportedTaxonomies = array();
	
	static function bootstrap() {
		foreach (static::$supportedTaxonomies as $taxonomy) {
			add_action($taxonomy . '_add_form_fields', array(__CLASS__, 'displayFields'), 10, 1);
			add_action($taxonomy . '_edit_form_fields', array(__CLASS__, 'displayFields'), 10, 1);
			add_action('edited_' . $taxonomy, array(__CLASS__, 'afterSave'), 10, 2);
			add_action('created_' . $taxonomy, array(__CLASS__, 'afterSave'), 10, 2);
		}
	}
	
	
	static function render($term) {}
	
	static function save($tt_id) {}
	
	
	static function displayFields($term = null) {
		static::render($term);
	}
	
	
	static function afterSave($term_id, $tt_id = null) {
		if (static::validateNonce($tt_id)) {
			static::save($tt_id);
		}
	}
	
	
	static function validateNonce($tt_id) {
		$field = static::getNonceFieldName($tt_id);
		return (!empty($_POST[$field]) AND wp_verify_nonce($_POST[$field], $field));
	}
	
	
	protected static function renderNonceField($term) {
		$field = static::getNonceFieldName($term->term_taxonomy_id);
		printf('<input type="hidden" name="%s" value="%s" />', $field, wp_create_nonce($field));
	}
	
	
	static function getNonceFieldName($tt_id) {
		return static::getId() . '_nonce_' . $tt_id;
	}
	
	static function getId() {
		return App::prefix('-' . static::SLUG);
	}
	
}
