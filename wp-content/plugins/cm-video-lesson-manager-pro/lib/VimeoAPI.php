<?php

namespace com\cminds\videolesson\lib;
use com\cminds\videolesson\App;
use com\cminds\videolesson\model\Settings;

require_once App::path('lib/Vimeo/Vimeo.php');

class VimeoAPI extends \Vimeo\Vimeo {
	
	const TRANSIENT_PREFIX = 'cmvlcache_vimeo_';
	
	static protected $instance;
	static $log;
	

	/**
	 * Get instance.
	 *
	 * @return VimeoAPI
	 */
	static function getInstance() {
		if (empty(static::$instance)) {
			static::$instance = new self(
				Settings::getOption(Settings::OPTION_VIMEO_CLIENT_ID),
				Settings::getOption(Settings::OPTION_VIMEO_CLIENT_SECRET),
				Settings::getOption(Settings::OPTION_VIMEO_ACCESS_TOKEN)
			);
			// 			register_shutdown_function(function() {
			// 				echo '<pre>';
			// 				print_r(Vimeo::$log);
			// 			});
		}
		return static::$instance;
	}
	
	
	
	function getAllVideos($uri = '/me/videos') {
		
		$vimeo = $this;
		$fields = 'uri,name,description,link,duration,width,language,height,release_time,pictures';
		
		$result = array();
		$page = 0;
		$maxPages = 100;
		
		do {
				
			$page++;
			$response = $vimeo->request($uri, array('sort' => 'date', 'direction' => 'desc', 'fields' => $fields, 'per_page' => 50, 'page' => $page));
				
			if (!empty($response) AND !empty($response['status']) AND $response['status'] == '200' AND !empty($response['body'])) {
				
// 				var_dump($response);
		
				foreach ($response['body']['data'] as $video) {
					$row = array(
							'id' => $video['uri'],
							'title' => $video['name'],
							'description' => $video['description'],
							'link' => $video['link'],
							'duration' => $video['duration'],
							'release_date' => Date('Y-m-d H:i:s', strtotime($video['release_time'])),
					);
					if (isset($video['pictures']['sizes'][1])) {
						$row['thumb'] = $video['pictures']['sizes'][1]['link'];
					} else {
						$row['thumb'] = '';
					}
					$row['images'] = array();
					if (!empty($video['pictures']['sizes'])) {
						// 						var_dump($video['pictures']['sizes']);exit;
						foreach ($video['pictures']['sizes'] as $image) {
							$row['images'][] = array(
									'url' => $image['link'],
									'w' => $image['width'],
									'h' => $image['height'],
							);
						}
					}
					$result[$row['id']] = $row;
				}
		
			}
				
			if ($page > $maxPages) break;
				
		} while (!empty($response['body']['paging']['next']));
		
		return $result;
	}
	

	/**
	 * (non-PHPdoc)
	 * @see \Vimeo\Vimeo::request()
	 */
	public function request($url, $params = array(), $method = 'GET', $json_body = true, $cacheExpiration = null) {
		if (is_null($cacheExpiration)) {
			$cacheExpiration = Settings::getOption(Settings::OPTION_VIMEO_CACHE_SEC);
		}
		$cache = ($cacheExpiration > 0 AND $method == 'GET');
		$transient = static::TRANSIENT_PREFIX . '_' . md5(implode('___', array($url, serialize($params), $method, $json_body)));
	
		if ($cache) {
			$result = get_transient($transient);
		}
		
		if (empty($result) OR empty($result['body'])) {
				
			// 			var_dump($url);
			// 			var_dump($method);
			// 			var_dump($cacheExpiration);
			// 			var_dump($cache);
			// 			var_dump($transient);
			// 			var_dump($result);
				
			$type = 'http';
			$result = parent::request($url, $params, $method, $json_body);
			// 			var_dump($url);exit;
			$re = set_transient($transient, $result, $cacheExpiration);
			
		} else {
			$type = 'cache';
		}
		static::$log[] = compact('type', 'url', 'params', 'method', 'json_body');
		return $result;
	}
	
	
	static function clearCache() {
		global $wpdb;
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s", '\\_transient\\_'. static::TRANSIENT_PREFIX .'\\_%'));
	}
	
	
}
