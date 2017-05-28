<?php

use com\cminds\videolesson\model\Settings;

use com\cminds\videolesson\model\Labels;
use com\cminds\videolesson\helper\PlayerHelper;

if (!isset($atts)) $atts = array();

?>


<section class="cmvl-playlist cmvl-playlist-layout-<?php echo $layout; ?>">
	<?php if (empty($videos)): ?>
		<p class="cmvl-no-videos"><?php echo Labels::getLocalized('msg_no_videos'); ?></p>
	<?php else: ?>
		<div class="cmvl-ajax-content">
		
			<?php if (!empty($currentCategory) AND !empty($atts['coursedesc'])): ?>
				<?php echo apply_filters('cmvl_get_course_description', '', $currentCategory); ?>
			<?php endif; ?>
			<?php if (!empty($channel) AND !empty($atts['lessondesc'])): ?>
				<?php echo apply_filters('cmvl_get_lesson_description', '', $channel); ?>
			<?php endif; ?>
			
			<?php
			
// 			printf('<div style="background-image:url(%s)" class="cmvl-video-background"></div>', esc_attr($currentVideo->getScreenshot()));
			
			printf('<figure class="cmvl-video" data-video-id="%s" data-channel-id="%s">', $currentVideo->getId(), $currentVideo->getChannel()->getId()); ?>
				
				<?php echo apply_filters('cmvl_video_player_html', '<div class="cmvl-player-outer fluid-width-video-wrapper" style="'
						. ($currentVideo->getServiceProvider() == Settings::API_WISTIA ? 'padding-top:0 !important;' : '') .'">'
					. PlayerHelper::getPlayer($currentVideo, $playerOptions). '</div>', $currentVideo, $atts); ?>
						
				<header>
					<h2><?php echo esc_html($currentVideo->getTitle()); ?></h2>
					<ul class="cmvl-controls">
						<li class="cmvl-video-duration"><?php echo $currentVideo->getDurationFormatted(); ?></li>
						<?php echo apply_filters('cmvl_video_controls', '', $currentVideo); ?>
					</ul>
				</header>
				<?php if (Settings::getOption(Settings::OPTION_SHOW_VIDEO_DESCRIPTION) AND $currentVideo->canView()): ?>
					<figcaption><div class="cmvl-description-inner"><?php echo nl2br($currentVideo->getDescription()); ?></div></figcaption>
				<?php endif; ?>
				<?php do_action('cmvl_video_bottom', $currentVideo); ?>
			<?php echo '</figure>'; ?>
		</div>
		<nav class="cmvl-video-list">
			<?php if ($layout != Settings::PLAYLIST_VIDEOS_LIST_BOTTOM AND !empty($channel)): ?>
				<header class="cmvl-toc">
					<h3><?php echo Labels::getLocalized('playlist_table_of_content'); ?></h3>
					<div class="cmvl-channel-info-btn"><?php echo Labels::getLocalized('playlist_lesson_desc_btn'); ?></div>
				</header>
			<?php endif; ?>
			<ul><?php foreach ($videos as $video):
				$controls = apply_filters('cmvl_video_controls', '', $video);
				printf('<li class="cmvl-video%s" data-video-id="%s" data-channel-id="%s"><a href="%s">
						<img src="%s" alt="Thumb" />
						<header><h3>%s</h3></header>
						<ul class="cmvl-controls"><li class="cmvl-video-duration">%s</li>%s</ul>
					</a></li>',
					($video->getId() == $currentVideo->getId() ? ' current' : ''),
					$video->getId(),
					$video->getChannel()->getId(),
					esc_attr($video->getPermalink()),
					esc_attr($video->getThumbUri()),
					esc_html($video->getTitle()),
					$video->getDurationFormatted(),
					$controls
				);
			endforeach; ?></ul>
		</nav>
	<?php endif; ?>
</section>