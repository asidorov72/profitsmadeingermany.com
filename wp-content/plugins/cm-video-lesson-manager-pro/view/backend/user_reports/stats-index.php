<?php

use com\cminds\videolesson\helper\TimeHelper;

if (!empty($data)): ?>

	<p>Duration in minutes has been rounded to a full minute for each video seprarately.</p>

	<table class="wp-list-table widefat fixed">
		<thead>
			<tr>
				<th>User</th>
				<th>Percentage</th>
				<th>Time watched</th>
				<th>Total duration</th>
				<th>Details</th>
			</tr>
		</thead>
		<tbody><?php foreach ($data as $row): ?>
			<tr>
				<td><a href="<?php echo esc_attr(admin_url('profile.php?user_id='. $row['userId']));
					?>" title="Show user profile"><?php echo $row['display_name']; ?></a></td>
				<td><?php echo min(100, $row['percent']); ?>%</td>
				<td><?php echo TimeHelper::niceTimeFormat($row['seconds']); ?></td>
				<td><?php echo TimeHelper::niceTimeFormat($channelsSummaryDurationSec); ?></td>
				<td><a href="<?php echo esc_attr(add_query_arg(array('user_id' => $row['userId']), $userDetailedStatsUrl));
					?>" title="Show details">Details</a></td>
			</tr>
		<?php endforeach; ?></tbody>
	</table>
	
	<?php if ($pagination['lastPage'] > 1): ?>
		<ul class="cmvl-pagination"><?php for ($page=1; $page<=$pagination['lastPage']; $page++): ?>
			<li<?php if ($page == $pagination['page']) echo ' class="current-page"';
				?>><a href="<?php echo esc_attr(add_query_arg('p', $page, $pagination['firstPageUrl'])); ?>"><?php echo $page; ?></a></li>
		<?php endfor; ?></ul>
	<?php endif; ?>

<?php else: ?>
	<p>No data.</p>
<?php endif; ?>