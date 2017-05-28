<?php

namespace com\cminds\videolesson\controller;
use com\cminds\videolesson\App;
use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\model\Labels;

class CourseController extends Controller {
	
	protected static $filters = array(
		'cmvl_get_course_description' => array('args' => 2),
	);
	protected static $actions = array(
		'admin_menu' => array('priority' => 11),
	);
	
	
	static function admin_menu() {
		if (App::isPro()) {
			$title = ucfirst(Labels::getLocalized('courses'));
			$url = htmlspecialchars(add_query_arg(urlencode_deep(array('taxonomy' => Category::TAXONOMY, 'post_type' => Channel::POST_TYPE)), 'edit-tags.php'));
			add_submenu_page(App::MENU_SLUG, App::getPluginName() . ' ' . $title, $title, 'manage_options', $url);
			if( isset($_GET['taxonomy']) && $_GET['taxonomy'] == Category::TAXONOMY && isset($_GET['post_type']) && $_GET['post_type'] == Channel::POST_TYPE ) {
				add_filter('parent_file', create_function('$q', 'return "' . App::MENU_SLUG . '";'), 999);
			}
		}
	}
	
	
	static function cmvl_get_course_description($content, Category $category) {
		return static::loadFrontendView('description', compact('category'));
	}
	
	
	
}