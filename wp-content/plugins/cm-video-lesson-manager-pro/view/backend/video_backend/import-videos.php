<?php

use com\cminds\videolesson\helper\FormHtml;

?>

<p style="max-width:50em;">To use videos which you have stored in Vimeo or Wistia you need first to import them over to your site so the plugin will be able to use them.
	The import proccess only takes the video credentials and create a slug for it and does not copy the video itself.
	After import is complete you can include each video in one lesson.
	To include the same video in more than one lesson you would need to import it again.</p>

<form class="cmvl-import-videos">
	
	Choose API: <?php echo FormHtml::selectBox('api', $apiList, null); ?> <input type="button" value="Show videos to import" class="cmvl-choose-api-btn" />
	
	<div class="cmvl-video-list"></div>
	
	<input type="hidden" name="nonce" value="<?php echo $nonce; ?>" />
		
</form>