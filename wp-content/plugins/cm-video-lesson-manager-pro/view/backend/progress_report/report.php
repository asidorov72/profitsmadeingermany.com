<?php

use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\model\Channel;
use com\cminds\videolesson\model\Video;
use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\helper\TimeHelper;
use com\cminds\videolesson\controller\ProgressReportController;

$createSubreportLink = function($groupBy, $filterKey, $filterValue) use ($reportUrl) {
	$reportUrl = remove_query_arg(array(ProgressReportController::PARAM_FILTER, ProgressReportController::PARAM_GROUP_BY), $reportUrl);
	$reportUrl = add_query_arg(array(
			ProgressReportController::PARAM_GROUP_BY => $groupBy,
			ProgressReportController::PARAM_FILTER . '[' . $filterKey .']' => $filterValue,
			ProgressReportController::PARAM_ACTION => ProgressReportController::ACTION_FETCH_REPORT,
	), $reportUrl);
	return $reportUrl;
};


?>

<?php echo ProgressReportController::loadBackendView('filter-description', compact('data', 'filter', 'groupBy', 'filterRecords')); ?>

<?php if (!empty($data)): ?>

	<table class="cmvl-report-table wp-list-table widefat fixed">
	
		<thead><tr>
			<th><?php echo $groupByOptions[$groupBy]; ?></th>
			<th class="cmvl-narrow">Progress</th>
			<th class="cmvl-narrow">Total duration</th>
			<th class="cmvl-narrow">Total time watching</th>
			<th class="cmvl-narrow cmvl-no-print">Filter by row and group by:</th>
			<?php if (Settings::getOption(Settings::OPTION_VIDEO_STATS_DETAILED_LOG_ENABLE)): ?>
				<th class="cmvl-narrow cmvl-no-print">Time Log</th>
			<?php endif; ?>
		</tr></thead>
		
		<tbody><?php foreach ($data as $row): ?>
		
			<tr>
				
				<td class="cmvl-title-col">
					<?php switch ($groupBy):
						case 'user':
							$text = esc_html($row['userDisplayName']);
							if ($url = get_avatar_url($row['userId'])) {
								$text = sprintf('<img src="%s" class="cmvl-thumb" alt="Image" />', esc_attr($url)) . $text;
							}
							$href = add_query_arg('user_id', $row['userId'], admin_url('profile.php'));
							$filterValue = $row['userId'];
							break;
						case 'video':
							$text = esc_html($row['videoName']);
							if ($video = Video::getInstance($row['videoId']) AND $url = $video->getThumbUri()) {
								$text = sprintf('<img src="%s" class="cmvl-thumb" alt="Image" />', esc_attr($url)) . $text;
							}
							$href = add_query_arg(array('post' => $row['videoId'], 'action' => 'edit'), admin_url('post.php'));
							$filterValue = $row['videoId'];
							break;
						case 'lesson':
							$text = esc_html($row['lessonName']);
							if ($channel = Channel::getInstance($row['lessonId']) AND $url = $channel->getThumbUri()) {
								$text = sprintf('<img src="%s" class="cmvl-thumb" alt="Image" />', esc_attr($url)) . $text;
							}
							$href = add_query_arg(array('post' => $row['lessonId'], 'action' => 'edit'), admin_url('post.php'));
							$filterValue = $row['lessonId'];
							break;
						case 'course':
							$text = esc_html($row['courseName']);
							$href = add_query_arg(array('taxonomy' => Category::TAXONOMY, 'tag_ID' => $row['courseId'], 'post_type' => Channel::POST_TYPE), admin_url('term.php'));
							$filterValue = $row['courseId'];
							break;
					endswitch; ?>
					<?php printf('<a href="%s">%s</a>', esc_attr($href), $text); ?>
				</td>
				
				<td><?php echo $row['totalTimeWatchingPercent']; ?>%</td>
				<td><?php echo TimeHelper::niceTimeFormat($row['videoDurationSec']); ?></td>
				<td><?php echo TimeHelper::niceTimeFormat($row['totalTimeWatchingSec']); ?></td>
				
				
				<td class="cmvl-no-print">
				
					<?php $first = true; ?>
					<?php foreach ($groupByOptions as $key => $label): ?>
						<?php if ($key != $groupBy): ?>
							<?php if ($first) $first = false; else echo '|'; ?>
							<?php printf('<a href="%s">%s</a>', esc_attr($createSubreportLink($key, $groupBy, $filterValue)), ucfirst($key)); ?>
						<?php endif; ?>
					<?php endforeach; ?>
								
				</td>
				
				
				<?php if (Settings::getOption(Settings::OPTION_VIDEO_STATS_DETAILED_LOG_ENABLE)):
					
					
					$url = add_query_arg(array(
						'filter' => array_merge($filter, array($groupBy => $row[$groupBy . 'Id'])),
// 						'groupBy' => $groupBy,
					), $timeReportUrl);
				
					?>
					<td class="cmvl-no-print">
						<a href="<?php echo esc_attr($url); ?>">Time Log</a>
					</td>
				<?php endif; ?>
				
			</tr>
		
		<?php endforeach; ?></tbody>
	
	</table>

<?php else: ?>
	<p>No data to show.</p>
<?php endif; ?>