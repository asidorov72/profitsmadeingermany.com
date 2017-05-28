<?php 

use com\cminds\videolesson\helper\TimeHelper;

?><p><a href="<?php echo esc_attr(admin_url('admin.php?page=cmvl-stats&user_id='. $user->ID)); ?>">&laquo; Back to the user's stats</a></p>

<h3>Details for user <a href="<?php echo esc_attr(admin_url('profile.php?user_id='. $user->ID));
	?>" title="Show user profile"><?php echo $user->display_name; ?></a>
	and lesson <a href="<?php echo esc_attr($channel->getEditUrl());
	?>" title="Edit lesson"><?php echo $channel->getTitle(); ?></a>
</h3>

<p>Duration in minutes has been rounded to a full minute for each video seprarately.</p>

<table class="wp-list-table widefat fixed cmvl-stats-user-table cmvl-report-table">
	<thead>
		<tr>
			<th class="cmvl-title-col">Video</th>
			<th>Percentage</th>
			<th>Time watched</th>
			<th>Video duration [min]</th>
		</tr>
	</thead>
	<tbody><?php foreach ($videos as $video): ?>
		<?php $videoId = $video->getId(); ?>
		<?php if (!empty($data[$videoId])): ?>
			<tr class="cmvl-video">
				<td class="cmvl-title-col">
					<a href="<?php echo esc_attr($video->getPermalink()); ?>" title="Permalink">
						<?php if ($thumbUrl = $video->getThumbUri()): ?>
							<img src="<?php echo esc_attr($thumbUrl); ?>" class="cmvl-thumb" />
						<?php endif; ?>
						<?php echo $video->getTitle(); ?>
					</a>
				</td>
				<td><?php echo min(100, isset($data[$videoId]) ? $data[$videoId]['percent'] : 0); ?>%</td>
				<td><?php echo TimeHelper::niceTimeFormat(isset($data[$videoId]) ? $data[$videoId]['seconds'] : 0); ?></td>
				<td><?php echo TimeHelper::niceTimeFormat($video->getDurationSec()); ?></td>
			</tr>
		<?php endif; ?>
	<?php endforeach; ?></tbody>
</table>