<?php
use com\cminds\videolesson\helper\TimeHelper;
?>
<p>Report for <?php printf('<a href="%s">%s</a>', esc_attr($channel->getEditUrl()), $channel->getTitle()); ?></p>

<p>Duration in minutes has been rounded to a full minute for each video seprarately.</p>

<?php

if (!empty($data)): ?>

	<table class="wp-list-table widefat fixed cmvl-report-table">
		<thead>
			<tr>
				<th class="cmvl-title-col">Video name</th>
				<th>Duration</th>
				<th>Users viewed</th>
				<th>Total time watched</th>
			</tr>
		</thead>
		<tbody><?php foreach ($data as $row): ?>
			<tr>
				<td class="cmvl-title-col">
					<a href="<?php echo esc_attr($row['video']->getEditUrl()); ?>">
						<?php if ($thumbUrl = $row['video']->getThumbUri()): ?>
							<img src="<?php echo esc_attr($thumbUrl); ?>" class="cmvl-thumb" />
						<?php endif; ?>
						<?php echo esc_html($row['title']); ?>
					</a>
				</td>
				<td><?php echo esc_html(TimeHelper::niceTimeFormat($row['durationSec'])); ?></td>
				<td><?php echo esc_html($row['usersViewed']); ?></td>
				<td><?php echo esc_html(TimeHelper::niceTimeFormat($row['seconds'])); ?></td>
			</tr>
		<?php endforeach; ?></tbody>
	</table>

<?php else: ?>
	<p>No data.</p>
<?php endif; ?>