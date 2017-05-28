<?php

use com\cminds\videolesson\controller\ChannelController;

use com\cminds\videolesson\controller\NoteController;

use com\cminds\videolesson\controller\BookmarkController;

use com\cminds\videolesson\controller\SearchController;

use com\cminds\videolesson\model\Labels;

?>
<form action="<?php echo esc_attr(get_home_url()); ?>" method="get" class="cmvl-search">
	<div class="left">
		<input type="text" name="s" required placeholder="<?php echo esc_attr(Labels::getLocalized('search_placeholder')); ?>" value="<?php echo esc_attr($search); ?>" />
		<span class="cmvl-search-clear" title="<?php echo esc_attr(Labels::getLocalized('search_clear_button')); ?>">&times;</span>
	</div>
	<div class="right">
		<select name="cmvl-search">
			<option value="<?php echo SearchController::SEARCH_CONTEXT_VIDEOS; ?>"><?php echo esc_html(Labels::getLocalized('videos')); ?></option>
			<option value="<?php echo BookmarkController::SEARCH_CONTEXT_BOOKMARKS; ?>"><?php echo esc_html(Labels::getLocalized('bookmarks')); ?></option>
			<option value="<?php echo NoteController::SEARCH_CONTEXT_NOTES; ?>"><?php echo esc_html(Labels::getLocalized('notes')); ?></option>
			<option value="<?php echo SearchController::SEARCH_CONTEXT_ALL; ?>"><?php echo esc_html(Labels::getLocalized('all')); ?></option>
		</select><input type="submit" value="OK" />
	</div>
</form>