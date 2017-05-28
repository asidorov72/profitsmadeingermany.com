<?php

use com\cminds\videolesson\model\PostSubscription;
use com\cminds\videolesson\helper\TimeHelper;
use com\cminds\videolesson\model\Labels;

?>
<div class="cmvl-channels-list-shortcode">
	<?php if (!empty($channels)): ?>
		<table>
			<thead><tr>
				<th><?php echo Labels::getLocalized('lesson_name'); ?></th>
				<th class="narrow"><?php echo Labels::getLocalized('lesson_videos_num'); ?></th>
				<th class="narrow"><?php echo Labels::getLocalized('lesson_duration'); ?></th>
				<?php if (is_user_logged_in() AND PostSubscription::isAvailable() AND $atts['subscription'] != 'active'):
					printf('<th>%s</th>', Labels::getLocalized('lesson_purchase'));
				endif; ?>
			</tr></thead>
			<tbody>
				<?php foreach ($channels as $channel): ?>
					<tr>
						<td><a href="<?php echo esc_attr($channel->getPermalink()); ?>"><?php echo esc_html($channel->getTitle()); ?></a></td>
						<td class="narrow"><?php echo $channel->getTotalVideos(); ?></td>
						<td class="narrow"><?php echo TimeHelper::niceTimeFormat($channel->getDurationSec()); ?></td>
						<?php do_action('cmvl_channels_list_row', $channel); ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else: ?>
		<p><?php echo Labels::getLocalized('msg_no_lessons'); ?></p>
	<?php endif; ?>
</div>