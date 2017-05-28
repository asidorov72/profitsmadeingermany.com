<?php

namespace com\cminds\videolesson\lib;

use com\cminds\videolesson\lib\Wistia\Wistia;
use com\cminds\videolesson\model\Settings;

class WistiaApi extends Wistia {
	
	const TRANSIENT_PREFIX = 'cmvlcache_wistia_';
	
	
	static function getInstance() {
		return new static(Settings::getOption(Settings::OPTION_API_WISTIA_ACCESS_TOKEN));
	}
	
	
	protected function __send($url) {
		$cacheExpiration = 3600;
		$transient = self::TRANSIENT_PREFIX . '_' . md5(serialize(array(__METHOD__, $url, $this->apiKey)));
		$result = get_transient($transient);
		if (empty($result)) {
			$result = parent::__send($url);
			$re = set_transient($transient, $result, $cacheExpiration);
		}
		return $result;
	}
	
	
}