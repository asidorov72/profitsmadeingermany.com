<?php

use com\cminds\videolesson\helper\HtmlHelper;
use com\cminds\videolesson\metabox\ChannelSortingVideosBox;

?>
<div class="cmvl-metabox-channel-sorting-videos">
	
	<div class="cmvl-channel-sort">
		<div>Sort by</div>
		<div><?php echo HtmlHelper::renderSelect(ChannelSortingVideosBox::FIELD_SORT, $sortOptions, $channel->getSort()); ?></div>
	</div>
	
	<div class="cmvl-channel-sort-dir">
		<div>Sort direction</div>
		<div><?php echo HtmlHelper::renderSelect(ChannelSortingVideosBox::FIELD_SORT_DIR, $sortDirOptions, $channel->getSortDirection()); ?></div>
	</div>

</div>