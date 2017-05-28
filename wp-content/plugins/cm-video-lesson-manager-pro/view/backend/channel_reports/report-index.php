<?php

use com\cminds\videolesson\helper\TimeHelper;

if (!empty($data)): ?>

	<p>Duration in minutes has been rounded to a full minute for each video seprarately.</p>

	<table class="wp-list-table widefat fixed cmvl-report-table">
		<thead>
			<tr>
				<th class="cmvl-title-col">Lesson name</th>
				<th>Duration</th>
				<th>Number of videos</th>
				<th>Users viewed</th>
				<th>Total time watched</th>
				<th>Details</th>
			</tr>
		</thead>
		<tbody><?php foreach ($data as $row): ?>
			<tr>
				<td class="cmvl-title-col">
					<a href="<?php echo esc_attr($row['channel']->getEditUrl()); ?>" title="Edit lesson">
						<?php if ($thumbUrl = $row['channel']->getThumbUri()): ?>
							<img src="<?php echo esc_attr($thumbUrl); ?>" class="cmvl-thumb" />
						<?php endif; ?>
						<?php echo $row['title']; ?>
					</a>
				</td>
				<td><?php echo TimeHelper::niceTimeFormat($row['durationSec']); ?></td>
				<td><?php echo $row['videosNumber']; ?></td>
				<td><?php echo $row['usersViewed']; ?></td>
				<td><?php echo TimeHelper::niceTimeFormat($row['seconds']); ?></td>
				<td><a href="<?php echo esc_attr(add_query_arg('channel_id', $row['channelId'], $showVideosUrl)); ?>" class="">Details</a></td>
			</tr>
		<?php endforeach; ?></tbody>
	</table>

<?php else: ?>
	<p>No data.</p>
<?php endif; ?>