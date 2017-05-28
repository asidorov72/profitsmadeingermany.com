<?php

use com\cminds\videolesson\model\Settings;
use com\cminds\videolesson\model\Labels;
use com\cminds\videolesson\helper\PlayerHelper;

if (!isset($atts)) $atts = array();

?>

<section class="cmvl-playlist"><?php
	if (empty($videos)): ?><p class="cmvl-no-videos"><?php echo Labels::getLocalized('msg_no_videos'); ?></p><?php
	else:
		echo $paginationView;
	
		if (!empty($currentCategory) AND !empty($atts['coursedesc'])): ?>
				<?php echo apply_filters('cmvl_get_course_description', '', $currentCategory); ?>
			<?php endif; ?>
			<?php if (!empty($channel) AND !empty($atts['lessondesc'])): ?>
				<?php echo apply_filters('cmvl_get_lesson_description', '', $channel); ?>
		<?php endif;
		
		?><div class="cmvl-tiles"><?php foreach ($videos as $video):
			$channel = $video->getChannel();
			$channelId = ($channel ? $channel->getId() : 0);
			printf('<figure class="cmvl-video" data-video-id="%s" data-channel-id="%s">',
					$video->getId(),
					$channelId
				); ?>
				<div class="cmvl-player-outer fluid-width-video-wrapper">
					<?php echo apply_filters('cmvl_video_player_html', PlayerHelper::getPlayer($video), $video, $atts); ?>
				</div>
				<header>
					<ul class="cmvl-controls"><?php echo apply_filters('cmvl_video_controls', '', $video); ?></ul>
					<h2><?php echo esc_html($video->getTitle()); ?></h2>
				</header>
				<?php if (Settings::getOption(Settings::OPTION_SHOW_VIDEO_DESCRIPTION)): ?>
					<figcaption>
						<div class="cmvl-description-inner"><?php echo nl2br($video->getDescription()); ?></div>
					</figcaption>
				<?php endif; ?>
				<?php do_action('cmvl_video_bottom', $video);
			echo '</figure>';
		endforeach; ?></div><?php
		echo $paginationView;
	endif; ?>
</section>