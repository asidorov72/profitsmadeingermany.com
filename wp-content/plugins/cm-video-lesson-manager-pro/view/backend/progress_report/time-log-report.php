<?php

use com\cminds\videolesson\controller\ProgressReportController;
use com\cminds\videolesson\model\Video;

?>

<h3>Time Log Report</h3>
<p>The following report shows detailed time log for choosen filters.</p>
<p><a href="#" onclick="history.back();return false">Go back to the progress report</a></p>

<?php echo ProgressReportController::loadBackendView('filter-description', compact('data', 'filter', 'groupBy', 'filterRecords')); ?>

<a href="<?php echo esc_attr($downloadCSVUrl); ?>" class="button cmvl-report-download-csv-btn">Download CSV</a>
<form action="<?php echo esc_attr(remove_query_arg(array('date_from', 'date_to'), filter_input(INPUT_SERVER, 'REQUEST_URI')));
		?>" method="GET" class="cmvl-time-log-filter cmvl-no-print">
	<p>
		<label>Filter date from: <input type="date" name="date_from" value="<?php echo esc_attr(filter_input(INPUT_GET, 'date_from')); ?>" /></label>
		<label>to: <input type="date" name="date_to" value="<?php echo esc_attr(filter_input(INPUT_GET, 'date_to')); ?>" /></label>
		<input type="submit" value="Filter" class="button" />
	</p>
</form>


<?php if (!empty($data)): ?>

	<table class="cmvl-report-table wp-list-table widefat fixed">
	
		<thead>
			<tr>
				<th>Video</th>
				<th>User</th>
				<th>Date Time</th>
				<th class="cmvl-narrow">Duration [sec]</th>
				<th class="cmvl-narrow">Time from [sec]</th>
				<th class="cmvl-narrow">Time to [sec]</th>
			</tr>
		</thead>
		
		<tbody><?php foreach ($data as $row): ?>
		
			<?php
			
			$interval = explode(' ', $row['time_log_interval']);
			$startSec = intval(reset($interval));
			$endSec = intval(end($interval));
			
			?>
		
			<tr>
				
				<td class="cmvl-title-col"><?php
					
					$text = esc_html($row['videoName']);
					if ($video = Video::getInstance($row['videoId']) AND $url = $video->getThumbUri()) {
						$text = sprintf('<img src="%s" class="cmvl-thumb" alt="Image" />', esc_attr($url)) . $text;
					}
					$href = add_query_arg(array('post' => $row['videoId'], 'action' => 'edit'), admin_url('post.php'));
					printf('<a href="%s">%s</a>', esc_attr($href), $text);
				
				?></td>
				
				<td class="cmvl-title-col"><?php
					
					$text = esc_html($row['userDisplayName']);
					if ($url = get_avatar_url($row['userId'])) {
						$text = sprintf('<img src="%s" class="cmvl-thumb" alt="Image" />', esc_attr($url)) . $text;
					}
					$href = add_query_arg('user_id', $row['userId'], admin_url('profile.php'));
					printf('<a href="%s">%s</a>', esc_attr($href), $text);
				
				?></td>
				
				<td><?php echo $row['time_log_date_time']; ?></td>
				<td><?php echo intval($endSec - $startSec); ?></td>
				<td><?php echo $startSec; ?></td>
				<td><?php echo $endSec; ?></td>
				
			</tr>
			
		<?php endforeach; ?></tbody>
		
	</table>
	
<?php else: ?>
	<p>No data to show.</p>
<?php endif; ?>