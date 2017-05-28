<?php

?>

<div class="cmvl-test-configuration">

	<div class="cmvl-success">Test passed - configuration works fine</div>
	
	<a href="#" class="button cmvl-show-details">Show details</a>
	
	<div class="cmvl-hidden-details">
	
		<div class="cmvl-test-videos">
			<h3>Videos: <?php echo (is_array($videos) ? count($videos) : 0); ?></h3>
			<div class="cmvl-success">Success</div>
			<?php if (empty($videos)): ?>
				<p>No videos.</p>
			<?php else: ?>
				<ul class="cmvl-videos-list"><?php foreach ($videos as $item): ?>
					<li><?php echo esc_html($item['name']); ?></li>
				<?php endforeach; ?></ul>
			<?php endif; ?>
			<textarea><?php var_export($videos); ?></textarea>
		</div>
			
	</div>

</div>