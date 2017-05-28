<?php

$template = '<div>
	<label>Time: <input type="text" name="cmvl-mp-number[]" value="%s" />
		<select name="cmvl-mp-unit[]">
			<option value="min"%s>minutes</option>
			<option value="h"%s>hours</option>
			<option value="d"%s>days</option>
			<option value="w"%s>weeks</option>
			<option value="m"%s>months</option>
			<option value="y"%s>years</option>
		</select>
	</label>
	<label>Cost: <input type="text" name="cmvl-mp-cost[]" value="%s" /></label>
	<input type="button" value="Remove" class="cmvl-cost-remove" />
</div>';

if (!empty($costs) AND is_array($costs)) foreach ($costs as $cost) {
	printf($template,
		$cost['number'],
		selected($cost['unit'], 'min', false),
		selected($cost['unit'], 'h', false),
		selected($cost['unit'], 'd', false),
		selected($cost['unit'], 'w', false),
		selected($cost['unit'], 'm', false),
		selected($cost['unit'], 'y', false),
		$cost['cost']
	);
}
	
?>

<p>
	<input type="button" value="Add new" data-template="<?php echo esc_attr($template); ?>" class="cmvl-cost-add" />
	<input type="hidden" name="cmvl-channel-mp-nonce" value="<?php echo wp_create_nonce('cmvl-channel-mp-nonce'); ?>" />
</p>