<?php

use com\cminds\videolesson\controller\AutocompleteController;



$videoItem = function($id, $title, $thumb, $editUrl) {
	return sprintf('<li data-id="%d">
		<img src="%s" alt="Video" title="Move" /><a href="%s" title="Edit">%s</a>
		<span class="dashicons dashicons-no-alt cmvl-remove" title="Remove"></span>
		<input type="hidden" name="cmvl_videos[]" value="%d" />
	</li>', $id, esc_attr($thumb), esc_attr($editUrl), esc_html($title), $id);
};


?>
<div class="cmvl-metabox-channel-videos-list" data-has-videos="<?php echo (empty($videos) ? 0 : 1); ?>">
	
	<p>Sort videos by draging it by image.</p>
	
	<p class="cmvl-no-videos">No videos on list</p>
	<ul class="cmvl-videos-list">
		<?php echo $videoItem(0, '', '', ''); ?>
		<?php foreach ($videos as $video): ?>
			<?php echo $videoItem($video->getId(), $video->getTitle(), $video->getThumbUri(), $video->getEditUrl()); ?>
		<?php endforeach; ?>
	</ul>
	
	<div class="cmvl-add-video">
		<div><b>Add new video:</b> <?php echo AutocompleteController::getFieldAdmin('Find video...', 'Video', 'cmvl_channel_edit_add_video'); ?></div>
		<span class="cmvl-hint">Notice: a Video can be assigned only to one lesson. If you need to include it in more than one lesson you need to import it again.</span>
	</div>

</div>