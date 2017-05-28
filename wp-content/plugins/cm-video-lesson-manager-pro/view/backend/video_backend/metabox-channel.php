<?php

use com\cminds\videolesson\controller\AutocompleteController;
use com\cminds\videolesson\metabox\VideoChannelBox;

/**
 * @var $video Video
 */

?>
<div class="cmvl-metabox-video-channel" data-channel-exists="<?php echo (empty($channel) ? 0 : 1); ?>">

	<span class="cmvl-no-channel">No lesson associated.</span>
	
	<p class="cmvl-channel">
		<?php printf('<a href="%s">%s</a>', esc_attr($channel ? $channel->getEditUrl() : ''), esc_html($channel ? $channel->getTitle() : '')); ?>
		<?php printf('<input type="hidden" name="%s" value="%d" />', VideoChannelBox::FIELD_CHANNEL_ID, $channel ? $channel->getId() : ''); ?>
		<span class="dashicons dashicons-no-alt cmvl-channel-remove" title="Detatch from lesson"></span>
	</p>
	
	<?php echo AutocompleteController::getFieldAdmin('Find lesson...', 'Channel', 'cmvl_video_edit_set_channel'); ?>

</div>