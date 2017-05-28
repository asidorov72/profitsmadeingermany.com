<?php

use com\cminds\videolesson\model\PostSubscription;

use com\cminds\videolesson\model\Labels;
use com\cminds\videolesson\model\Micropayments;

if (count($costs) == 1) {
	$singleCost = reset($costs);
	$submitLabel = sprintf(Labels::getLocalized('subscription_activate_button_period_for_points'), PostSubscription::period2date($singleCost['period']), $singleCost['cost']);
} else {
	$submitLabel = Labels::getLocalized('subscription_activate_button');
}

?>

<form class="cmvl-channel-paybox-form" data-ajax-url="<?php echo esc_attr(admin_url('admin-ajax.php')); ?>">
	<?php if (count($costs) > 1): ?>
		<div class="cmvl-channel-paybox-costs"><?php foreach ($costs as $cost): ?>
			<label><input type="radio" name="period" value="<?php echo $cost['seconds']; ?>" class="cmvl-price"><?php
			
			printf(Labels::getLocalized('period_for_points'), PostSubscription::period2date($cost['period']), $cost['cost']);
			
			?></label>
		<?php endforeach; ?></div>
	<?php else:
		printf('<input type="hidden" name="period" value="%d" class="cmvl-price">', $singleCost['seconds']);
	endif; ?>
	<div class="cmvl-channel-payment-buttons">
		<input type="hidden" name="channelId" value="<?php echo esc_attr($channelId); ?>" />
		<input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>" />
		<input type="hidden" name="action" value="cmvl_channel_mp_activate" />
		<input type="hidden" name="callbackUrl" value="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>" />
		<input type="submit" value="<?php echo esc_attr($submitLabel); ?>" />
		<ul>
			<?php if (!empty($walletUrl)): ?>
				<li><a href="<?php echo esc_attr($walletUrl); ?>" class="button"><?php echo Labels::getLocalized('mp_wallet_button'); ?></a></li>
			<?php endif; ?>
			<?php if (!empty($checkoutUrl)): ?>
				<li><a href="<?php echo esc_attr($checkoutUrl); ?>" class="button"><?php echo Labels::getLocalized('mp_checkout_button'); ?></a></li>
			<?php endif; ?>
		</ul>
	</div>
</form>