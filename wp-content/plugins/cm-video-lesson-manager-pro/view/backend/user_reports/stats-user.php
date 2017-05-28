<?php

use com\cminds\videolesson\helper\TimeHelper;

?><p><a href="<?php echo esc_attr(admin_url('admin.php?page=cmvl-stats')); ?>">&laquo; Back to users list</a></p>

<h3>Details for user <a href="<?php echo esc_attr(admin_url('profile.php?user_id='. $user->ID));
	?>" title="Show user profile"><?php echo $user->display_name; ?></a></h3>

<p>Duration in minutes has been rounded to a full minute for each video seprarately.</p>

<table class="wp-list-table widefat fixed cmvl-stats-user-table cmvl-report-table">
	<thead>
		<tr>
			<th class="cmvl-title-col">Lesson</th>
			<th>Percentage</th>
			<th>Time watched</th>
			<th>Lesson duration</th>
			<th>Details</th>
		</tr>
	</thead>
	<tbody><?php foreach ($categories as $category): ?>
		<?php $categoryHeaderDisplayed = false; ?>
		<?php $channels = $category->getChannels(); ?>
		<?php foreach ($channels as $channel): ?>
			<?php $channelId = $channel->getId(); ?>
			<?php if (!empty($data[$channelId])): ?>
				<?php if (!$categoryHeaderDisplayed): ?>
					<tr class="cmvl-category">
						<th colspan="5">Course: <a href="<?php echo esc_attr($category->getEditUrl());
							?>" title="Edit course"><?php echo $category->getName(); ?></a></th>
					</tr>
					<?php $categoryHeaderDisplayed = true; ?>
				<?php endif; ?>
				<tr class="cmvl-channel">
					<td class="cmvl-title-col">
						<a href="<?php echo esc_attr($channel->getEditUrl()); ?>" title="Edit lesson">
							<?php if ($thumbUrl = $channel->getThumbUri()): ?>
								<img src="<?php echo esc_attr($thumbUrl); ?>" class="cmvl-thumb" />
							<?php endif; ?>
							<?php echo $channel->getTitle(); ?>
						</a>
					</td>
					<td><?php echo min(100, isset($data[$channelId]) ? $data[$channelId]['percent'] : 0); ?>%</td>
					<td><?php echo TimeHelper::niceTimeFormat(isset($data[$channelId]) ? $data[$channelId]['seconds'] : 0); ?></td>
					<td><?php echo TimeHelper::niceTimeFormat(isset($data[$channelId]) ? $data[$channelId]['durationSec'] : $channel->getDurationSec()); ?></td>
					<td><a href="<?php echo esc_attr(add_query_arg(array('channel_id' => $channelId), $detailsUrl));
						?>" title="Show details">Details</a></td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endforeach; ?></tbody>
</table>