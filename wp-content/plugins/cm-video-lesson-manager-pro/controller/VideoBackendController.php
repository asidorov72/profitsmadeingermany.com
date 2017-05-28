<?php

namespace com\cminds\videolesson\controller;
use com\cminds\videolesson\lib\VimeoAPI;

use com\cminds\videolesson\App;
use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\lib\WistiaApi;

class VideoBackendController extends Controller {
	
	const PAGE_IMPORT_VIDEOS = 'cmvl-import-videos';
	
	static $filters = array(
		'manage_cmvl_video_posts_columns',
	);
	static $actions = array(
		'admin_menu' => array('priority' => 11),
		'admin_footer',
		'manage_cmvl_video_posts_custom_column' => array('args' => 2),
	);
	static $ajax = array(
		'cmvl_import_videos_load_api',
		'cmvl_import_videos_create',
	);
	
	
	static function admin_menu() {
		$title = 'Import Videos';
		add_submenu_page(App::MENU_SLUG, App::getPluginName() . ' ' . $title, $title, 'manage_options', static::PAGE_IMPORT_VIDEOS, array(get_called_class(), 'renderImportVideosPage'));
		
	}
	
	
	static function manage_cmvl_video_posts_columns($columns) {
		$before['cb'] = $columns['cb'];
		$before['cmvl_thumb_col'] = 'Image';
		unset($columns['cb']);
		$columns['cmvl_channel'] = 'Lesson';
		return $before + $columns;
	}
	
	
	static function manage_cmvl_video_posts_custom_column($column, $postId) {
		if ($column == 'cmvl_thumb_col') {
			if ($video = Video::getInstance($postId)) {
				printf('<img src="%s" alt="Image" />', esc_attr($video->getThumbUri()));
			}
		}
		else if ($column == 'cmvl_channel') {
			if ($video = Video::getInstance($postId)) {
				if ($channel = $video->getChannel()) {
					printf('<a href="%s" title="Edit lessons">%s</a>', esc_attr($channel->getEditUrl()), esc_html($channel->getTitle()));
				} else {
					echo '--';
				}
			}
		}
	}
	
	
	static function renderImportVideosPage() {
		
// 		add_filter('parent_file', create_function('$q', 'return "' . App::MENU_SLUG . '";'), 999);
		
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_script('cmvl-backend-import-videos');
		
		$apiList = array('-- choose --') + Settings::getApiList();
		$nonce = wp_create_nonce(static::PAGE_IMPORT_VIDEOS);
		
		echo static::loadView('backend/template', array(
			'title' => App::getPluginName() . ' - Import Videos',
			'nav' => static::getBackendNav(),
			'content' => static::loadBackendView('import-videos', compact('apiList', 'nonce')),
		));
	}
	
	
	static function admin_footer() {
		
		$title = 'Import Videos';
		$url = static::getImportVideosUrl();
		
		echo '<script>
			jQuery(function() {
				var btn = jQuery("body.post-type-cmvl_video .page-title-action");
				btn.text('. json_encode($title) .');
				btn.attr("href", '. json_encode($url) .');
			});
		</script>';
		
	}
	
	
	static function getImportVideosUrl() {
		return add_query_arg(array(
			'page' => static::PAGE_IMPORT_VIDEOS,
		), admin_url('admin.php'));
	}
	
	
	static function cmvl_import_videos_load_api() {
		if ($nonce = filter_input(INPUT_POST, 'nonce') AND wp_verify_nonce($nonce, static::PAGE_IMPORT_VIDEOS) AND $api = filter_input(INPUT_POST, 'api')) {
			
			try {
				
				switch ($api) {
					case Settings::API_VIMEO:
						$videos = VimeoAPI::getInstance()->getAllVideos();
						break;
					case Settings::API_WISTIA:
						$videos = static::getWistiaVideoList();
						break;
					case Settings::API_YOUTUBE:
						$videos = static::getYoutubeVideoList();
						break;
				}
				
	// 			var_dump($videos);
	
				$channelsOptions = array(0 => '-- do not assign --');
				$channels = Channel::getAll();
				foreach ($channels as $channel) {
					$channelsOptions[$channel->getId()] = $channel->getName();
				}
				
				echo static::loadBackendView('import-videos-list', compact('videos', 'channelsOptions'));
				
			} catch (\Exception $e) {
				echo '<p>' . $e->getMessage() . '</p>';
			}
			
		}
		exit;
	}
	
	
	
	static function getWistiaVideoList() {
		
		$result = array();
		$wistia = WistiaApi::getInstance();
		$response = $wistia->mediaList($project = null, $page = 1, $perPage = 1000, $full = true);
		
		foreach ($response as $video) {
			if ($video['type'] == 'Video') {
				$row = array(
					'id' => $video['hashed_id'],
					'title' => $video['name'],
					'description' => $video['description'],
	// 				'link' => $video['link'],
					'duration' => floor($video['duration']),
					'release_date' => Date('Y-m-d H:i:s', strtotime($video['created'])),
				);
				
				$createThumb = function($url, $w, $h, $newW) {
					$newH = round($h * $newW / $w);
					return add_query_arg(array('image_crop_resized' => $newW . 'x' . $newH), remove_query_arg('image_crop_resized', $url));;
				};
				$createImageRow = function($url, $w, $h, $newW) use ($createThumb) {
					return array('url' => $createThumb($url,  $w, $h, $newW), 'w' => $newW, 'h' => round($h * $newW / $w));
				};
				
				$pictureBig = $video['thumbnail']['url'];
				$w = $video['thumbnail']['width'];
				$h = $video['thumbnail']['height'];
				$row['thumb'] = $createThumb($pictureBig,  $w, $h, 200);
				$row['images'] = array(
					$createImageRow($pictureBig, $w, $h, 200),
					$createImageRow($pictureBig, $w, $h, 400),
					$createImageRow($pictureBig, $w, $h, 800),
					array('url' => $pictureBig, 'w' => $w, 'h' => $h),
				);
				
				$result[$row['id']] = $row;
				
			}
		}
		
// 		var_dump($result);
		
		return $result;
		
	}
	
	
	static function cmvl_import_videos_create() {
		
		if ($nonce = filter_input(INPUT_POST, 'nonce') AND wp_verify_nonce($nonce, static::PAGE_IMPORT_VIDEOS) AND $api = filter_input(INPUT_POST, 'api')) {
			
			$channelId = filter_input(INPUT_POST, 'channelId');
			if (empty($channelId)) $channelId = null;
			
			$print = function($text) {
				printf('<div>%s</div>', $text);
			};
			
			$import = (isset($_POST['videos']) ? $_POST['videos'] : array());
			
			switch ($api) {
				case Settings::API_VIMEO:
					$videos = VimeoAPI::getInstance()->getAllVideos();
					break;
				case Settings::API_WISTIA:
					$videos = static::getWistiaVideoList();
					break;
				case Settings::API_YOUTUBE:
					$videos = static::getYoutubeVideoList();
					break;
			}
			
			if (!empty($videos)) {
				
				foreach ($import as $id) {
					if (isset($videos[$id])) {
						$title = $videos[$id]['title'];
						try {
							static::importVideo($api, $videos[$id], $channelId);
							$print('Imported: ' . $title);
						} catch (\Exception $e) {
							$print('Error importing: ' . $title);
						}
					}
				}
				
			}
			
			echo '<div>END.</div>';
			exit;
			
		}
		
	}
	
	
	static function importVideo($api, $data, $channelId = null) {
		
		$postarr = array(
			'post_title' => $data['title'],
			'post_content' => (empty($data['description']) ? '' : $data['description']),
			'post_date' => Date('Y-m-d H:i:s', strtotime($data['release_date'])),
			'post_status' => 'publish',
			'post_parent' => $channelId,
		);
		$video = new Video($postarr);
		if ($id = $video->save()) {
			$video->setServiceProvider($api);
			$video->setProvidersId($data['id']);
			$video->setDurationSec($data['duration']);
			$video->setThumbnails($data['images']);
			$video->setAPIRequestData($data);
			return $id;
		} else {
			throw new \Exception('Error when creating a video.');
		}
		
	}
	
	
}