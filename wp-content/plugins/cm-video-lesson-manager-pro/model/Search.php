<?php

namespace com\cminds\videolesson\model;

class Search extends Model {

	protected $video;


	function __construct(Video $video) {
		$this->video = $video;
	}


	static function search($str, $type, $context = array()) {
		
		$words = self::parseWords($str);
		$context = apply_filters('cmvl_search_context', $context, $type, $words, $str);
		
		foreach ($context as &$video) {
			$score = apply_filters('cmvl_search_score', 0, $video, $type, $words, $str);
			$video = array(
				'video' => $video,
				'score' => $score,
			);
		}
		
		uasort($context, function($a, $b) {
			return ($a['score'] < $b['score']);
		});
		
		$result = array_filter(array_map(function($video) use ($str) {
// 			if (empty($str)) return $video['video'];
			if ($video['score'] > 0) return $video['video'];
			else return null;
		}, $context));
		
		return apply_filters('cmvl_search_results', $result, $context, $type, $words, $str);
		
	}
	
	
	static function parseWords($searchStr) {
		$words = explode(' ', $searchStr);
		foreach ($words as &$word) {
			$word = preg_replace('/[^\pL\pN.]+/u', '', trim($word));
		}
		$words = array_filter($words);
		if (empty($words) OR (count($words) == 1 AND strlen($words[0]) < 3 )) return array();
		else return $words;
	}
	
	
	static function countScore($text, $words) {
		$score = 0;
		foreach ($words as $word) {
			$score += intval(stripos($text, $word) !== false);
		}
		return $score;
	}
	

}
