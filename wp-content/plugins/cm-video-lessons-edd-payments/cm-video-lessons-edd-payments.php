<?php
/*
  Plugin Name: CM Video Lessons EDD Payments
  Plugin URI: https://cminds.com/
  Description: CM Video Lessons EDD Payments
  Author: CreativeMindsSolutions
  Version: 2.1.2
 */

if (version_compare('5.3', PHP_VERSION, '>')) {
	die(sprintf('We are sorry, but you need to have at least PHP 5.3 to run this plugin (currently installed version: %s)'
		. ' - please upgrade or contact your system administrator.', PHP_VERSION));
}

require_once dirname(__FILE__) . '/App.php';
com\cminds\videolesson\addon\eddpay\App::bootstrap(__FILE__);
