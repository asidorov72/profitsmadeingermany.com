<?php

use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\controller\NotificationsController;

$options = array(
	Channel::NOTIFICATION_STATUS_GLOBAL => 'follow global settings',
	Channel::NOTIFICATION_STATUS_DISABLED => 'disabled',
	Channel::NOTIFICATION_STATUS_ENABLED => 'enabled'
);

?>

<p><label>Send notification when a user completed this lesson:<br><select name="cmvl_channel_notifications"><?php foreach ($options as $name => $val):
	printf('<option value="%s"%s>%s</option>', $name, selected($val, $channelNotificationsStatus, false), $val);
endforeach; ?></select></label></p>

<p><label>Send notification when a user completed a video of this lesson:<br><select name="cmvl_videos_notifications"><?php foreach ($options as $name => $val):
	printf('<option value="%s"%s>%s</option>', $name, selected($val, $videosNotificationsStatus, false), $val);
endforeach; ?></select></label></p>

<?php printf('<input type="hidden" name="%s" value="%s" />', esc_attr(NotificationsController::NONCE_CHANNEL_NOTIFICATIONS), esc_attr($nonce));
