<?php

use com\cminds\videolesson\helper\HtmlHelper;

?>


<?php if (!empty($videos)): ?>

	<p>Assign chosen videos to lesson:<br><?php echo HtmlHelper::renderSelect('cmvl_channel', $channelsOptions, 0); ?></p>

	<p>
		<input type="text" class="cmvl-video-import-search" placeholder="Filter videos" />
		<button class="button button-primary cmvl-video-import-proceed">Import chosen videos</button>
	</p>
	
	<table class="wp-list-table widefat fixed striped cmvl-video-import-list">
	
		<thead>
			<tr id="cb">
				<td class="manage-column column-cb check-column" title="Select all"><input type="checkbox" id="cb-select-all-1" class="cmvl-select-all" /></td>
				<th class="cmvl-col-thumbnail">Thumbnail</th>
				<th>Title</th>
				<th class="cmvl-col-duration">Duration [sec]</th>
				<th class="cmvl-col-date">Release date</th>
			</tr>
		</thead>
		<tbody id="the-list"><?php foreach ($videos as $i => $video): ?>
			<tr>
				<th scope="row" class="check-column"><input type="checkbox" id="cb-select-<?php echo $i; ?>" name="id[]" value="<?php echo esc_attr($video['id']);?>" /></th>
				<td><?php if (!empty($video['thumb'])) printf('<img src="%s" alt="Thumbnail" />', esc_attr($video['thumb'])); ?></td>
				<td class="column-title"><?php printf('<a href="%s">%s</a>', esc_attr($video['link']), esc_html($video['title'])); ?></td>
				<td><?php echo esc_html($video['duration']); ?></td>
				<td><?php echo esc_html($video['release_date']); ?></td>
			</tr>
		<?php endforeach; ?></tbody>
	</table>
	
	<p>
		<button class="button button-primary cmvl-video-import-proceed">Import chosen videos</button>
	</p>
	
<?php else: ?>
	<p>There's no videos obtained from the API.</p>
<?php endif; ?>