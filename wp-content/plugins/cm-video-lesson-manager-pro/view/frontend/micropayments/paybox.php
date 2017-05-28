<?php

use com\cminds\videolesson\model\Labels;

?>

<div class="cmvl-channel-paybox cmvl-channel-micropayments">
	<h3><?php echo Labels::getLocalized('activate_subscription_header'); ?></h3>
	<p><?php echo Labels::getLocalized('activate_subscription_text'); ?></p>
	<?php echo $form; ?>
</div>