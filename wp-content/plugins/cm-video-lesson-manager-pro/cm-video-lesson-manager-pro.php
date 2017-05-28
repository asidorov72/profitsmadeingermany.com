<?php
/*
  Plugin Name: CM Video Lesson Manager Pro
  Plugin URI: https://plugins.cminds.com/cm-video-lessons-manager-plugin-for-wordpress/
  Description: Manage video lesson while allowing user and admin to track progress, leave notes and mark favourites. Support payment per viewing lesson per a define time period.
  Author: CreativeMindsSolutions
  Version: 2.1.7
 */

if (version_compare('5.3', PHP_VERSION, '>')) {
	die(sprintf('We are sorry, but you need to have at least PHP 5.3 to run this plugin (currently installed version: %s)'
		. ' - please upgrade or contact your system administrator.', PHP_VERSION));
}

require_once dirname(__FILE__) . '/App.php';
com\cminds\videolesson\App::bootstrap(__FILE__);
