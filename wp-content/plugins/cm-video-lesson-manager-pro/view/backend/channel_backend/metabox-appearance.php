<?php

use com\cminds\videolesson\helper\HtmlHelper;
use com\cminds\videolesson\metabox\ChannelAppearanceBox;

?>
<div class="cmvl-metabox-channel-appearance">
	
	<div class="cmvl-channel-page-template cmvl-field">
		<div>Page template:</div>
		<div><?php echo HtmlHelper::renderSelect(ChannelAppearanceBox::FIELD_PAGE_TEMPLATE, $pageTemplatesOptions, $channel->getPageTemplate()); ?></div>
	</div>
	
	<div class="cmvl-channel-videos-layout cmvl-field">
		<div>Videos layout:</div>
		<div><?php echo HtmlHelper::renderRadioGroup(ChannelAppearanceBox::FIELD_VIDEOS_LAYOUT, $videosLayoutsOptions, $channel->getVideosLayout()); ?></div>
	</div>
	
	<div class="cmvl-channel-playlist-layout cmvl-field">
		<div>Playlist layout:</div>
		<div><?php echo HtmlHelper::renderRadioGroup(ChannelAppearanceBox::FIELD_PLAYLIST_LAYOUT, $playlistLayoutsOptions, $channel->getPlaylistLayout()); ?></div>
		<em>Choose the playlist layout only when selected the videos layout: playlist.</em>
	</div>

</div>