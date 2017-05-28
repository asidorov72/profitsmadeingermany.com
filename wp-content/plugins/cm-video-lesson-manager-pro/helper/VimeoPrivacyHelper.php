<?php

namespace com\cminds\videolesson\helper;

use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\lib\VimeoAPI;

class VimeoPrivacyHelper {
	
	protected $video;
	
	
	function __construct(Video $video) {
		
		if ($video->getServiceProvider() != Settings::API_VIMEO) {
			throw new \Exception('Video must be provided by Vimeo.');
		}
		
		$this->video = $video;
		
		$vimeo = VimeoAPI::getInstance();
		$result = $vimeo->request($this->getVimeoUri(), $params = array(), $method = 'GET', $json_body = true, $cacheExpiration = 0);
		if (!empty($result['body']['data'])) {
			$this->rawVideo = $result['body']['data'];
		}
		
	}
	
	
	
	function getRawVideo() {
		return $this->rawVideo;
	}
	
	
	function getVimeoUri() {
		return $this->video->getProvidersId();
	}
	


	function getPrivacyView() {
		$video = $this->getRawVideo();
		return $video['privacy']['view'];
	}
	
	
	function setPrivacyView($value) {
		VimeoAPI::getInstance()->request($this->getVimeoUri(), array('privacy' => array('view' => $value)), 'PATCH');
		return $this;
	}
	
	
	function getPrivacyEmbed() {
		$video = $this->getRawVideo();
		return $video['privacy']['embed'];
	}
	
	
	function setPrivacyEmbed($value) {
		VimeoAPI::getInstance()->request($this->getVimeoUri(), array('privacy' => array('embed' => $value)), 'PATCH');
// 		$this->clearCache();
		return $this;
	}
	
	
	function getPrivacyDomains() {
		$results = array();
		$vimeo = VimeoAPI::getInstance();
		$cacheExpiration = 0;
		$domains = $vimeo->request($this->getVimeoUri() . '/privacy/domains', $params = array(), $method = 'GET', $json_body = true, $cacheExpiration);
		if (!empty($domains['body']['data'])) foreach ($domains['body']['data'] as $domain) {
			$results[] = $domain['domain'];
		}
		return $results;
	}
	
	
	function addPrivacyDomain($domain = null) {
		if (is_null($domain)) {
			$domain = preg_replace('/^www./', '', $_SERVER['HTTP_HOST']);
		}
		$result = VimeoAPI::getInstance()->request($this->getVimeoUri() . '/privacy/domains/'. urlencode($domain), array(), 'PUT');
		VimeoAPI::getInstance()->removeCachedRequest($this->getVimeoUri() . '/privacy/domains');
		return $this;
	}
	
	
	function unlock() {
		$vimeo = VimeoAPI::getInstance();
		// This is no longer needed:
		// 		if ($this->video->getPrivacyView() != 'anybody') {
		// 			$this->video->setPrivacyView('anybody');
		// 		}
		$embedStatus = $this->getPrivacyEmbed();
		if ('private' == $embedStatus) {
			$this->setPrivacyEmbed('whitelist');
			$embedStatus = 'whitelist';
		}
		if ('whitelist' == $embedStatus) {
			$domain = preg_replace('/^www./', '', $_SERVER['HTTP_HOST']);
			if (!in_array($domain, $this->getPrivacyDomains())) {
				$this->addPrivacyDomain($domain);
				return true;
			}
		}
		return false;
	}

	
}
