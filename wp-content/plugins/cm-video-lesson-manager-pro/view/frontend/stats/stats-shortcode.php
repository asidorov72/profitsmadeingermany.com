<?php

use com\cminds\videolesson\model\Labels;
use com\cminds\videolesson\helper\TimeHelper;

$categoryPrefix = ucfirst(Labels::getLocalized('course')) . ': ';


?>

<table class="cmvl-stats-user-table">
	<caption><?php echo Labels::getLocalized('stats_shortcode_table_caption') ?></caption>
	<thead>
		<tr>
			<th><?php echo ucfirst(Labels::getLocalized('lesson')); ?></th>
			<th><?php echo Labels::getLocalized('stats_percentage'); ?></th>
			<th><?php echo Labels::getLocalized('stats_time_watched'); ?></th>
			<th><?php echo Labels::getLocalized('stats_lesson_duration'); ?></th>
			<th><?php echo Labels::getLocalized('stats_details'); ?></th>
		</tr>
	</thead>
	<tbody><?php foreach ($categories as $category):
		$channels = $category->getChannels();
		$displayedChannels = 0;
		foreach ($channels as $channel):
			
			$channelId = $channel->getId();
			$channelPercent = min(100, isset($channelData[$channelId]) ? $channelData[$channelId]['percent'] : 0);
			$channelMinutes = (isset($channelData[$channelId]) ? $channelData[$channelId]['minutes'] : 0);
			$channelSeconds = (isset($channelData[$channelId]) ? $channelData[$channelId]['seconds'] : 0);
			
			if ($channelPercent > 0): ?>
				<?php if ($displayedChannels == 0): ?>
					<tr class="cmvl-category">
						<th colspan="5">
							<?php if ($atts['permalinks']):
								$link = sprintf('<a href="%s" title="Watch">%s</a>', esc_attr($category->getFirstChannelPermalink()), $categoryPrefix . $category->getName());
								echo apply_filters('cmvl_stats_shortcode_category_link', $link, $category);
							else: ?>
								<?php echo $categoryPrefix . $category->getName(); ?>
							<?php endif; ?>
						</th>
					</tr>
				<?php endif; ?>
				<tr class="cmvl-channel">
					<td>
						<?php if ($atts['permalinks']):
							$link = sprintf('<a href="%s" title="Watch">%s</a>', esc_attr($channel->getPermalink()), $channel->getTitle());
							echo apply_filters('cmvl_stats_shortcode_channel_link', $link, $channel, $category);
						else: ?>
							<?php echo $channel->getTitle(); ?>
						<?php endif; ?>
					</td>
					<td><?php echo $channelPercent; ?>%</td>
					<td><?php echo TimeHelper::niceTimeFormat($channelSeconds); ?></td>
					<td><?php echo TimeHelper::niceTimeFormat($channel->getDurationSec()); ?></td>
					<td><a href="" class="cmvl-details" data-channel-id="<?php echo $channelId;
						?>" data-category-id="<?php echo $category->getId(); ?>"><?php echo Labels::getLocalized('stats_details'); ?></a></td>
				</tr>
				<?php foreach ($channel->getVideos() as $video):
					
					$videoId = $video->getId();
					
					$videoPercent = (isset($videoData[$videoId]) ? $videoData[$videoId]['percent'] : 0);
					$videoMinutes = (isset($videoData[$videoId]) ? $videoData[$videoId]['minutes'] : 0);
					$videoSeconds = (isset($videoData[$videoId]) ? $videoData[$videoId]['seconds'] : 0);
				
					?>
					<tr class="cmvl-video" data-channel-id="<?php echo $channelId;
						?>" data-category-id="<?php echo $category->getId(); ?>">
						<td>
							<?php if ($atts['permalinks']):
								$link = sprintf('<a href="%s" title="Watch">%s</a>', esc_attr($video->getPermalink()), $video->getTitle());
								echo apply_filters('cmvl_stats_shortcode_video_link', $link, $video, $channel, $category);
							else: ?>
								<?php echo $video->getTitle(); ?>
							<?php endif; ?>
						</td>
						<td><?php echo $videoPercent; ?>%</td>
						<td><?php echo TimeHelper::niceTimeFormat($videoSeconds); ?></td>
						<td><?php echo TimeHelper::niceTimeFormat($video->getDurationSec()); ?></td>
						<td></td>
					</tr>
					
				<?php endforeach; ?>
				<?php $displayedChannels++; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endforeach; ?></tbody>
</table>