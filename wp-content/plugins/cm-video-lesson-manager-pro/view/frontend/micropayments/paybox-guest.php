<?php

use com\cminds\videolesson\model\Labels;
use com\cminds\videolesson\model\Micropayments;

?>
<form class="cmvl-channel-paybox cmvl-channel-micropayments">
	<h3><?php echo Labels::getLocalized('activate_subscription_header'); ?></h3>
	<p><?php echo Labels::getLocalized('activate_subscription_text_guest'); ?></p>
</form>