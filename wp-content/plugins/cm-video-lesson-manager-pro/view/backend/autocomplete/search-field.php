<?php

?>
<div class="cmvl-autocomplete-wrapper">
	<p><input type="text" class="cmvl-autocomplete-input" placeholder="<?php echo esc_attr($placeholder);
		?>" data-model="<?php echo esc_attr($model);
		?>" data-nonce="<?php echo esc_attr($nonce);
		?>" data-url="<?php echo esc_attr(admin_url('admin-ajax.php'));
		?>" data-action="<?php echo esc_attr($action);
		?>" data-callback="<?php echo esc_attr($callbackFunctionName);
		?>" /></p>
	<div class="cmvl-autocomplete-results"></div>
</div>