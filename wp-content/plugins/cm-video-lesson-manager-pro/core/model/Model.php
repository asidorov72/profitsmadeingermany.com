<?php

namespace com\cminds\videolesson\model;

abstract class Model {
	
	const INIT_PRIORITY = 5;
	
	static function bootstrap() {
		add_action('init', array(get_called_class(), 'init'), static::INIT_PRIORITY);
	}
	
	static function init() {}
	
}
