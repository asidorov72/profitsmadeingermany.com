<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\App;

abstract class ValidLicenseController extends Controller {

	static function addHooks() {
		if (App::isLicenseOk()) {
			parent::addHooks();
		}
	}
	
}
