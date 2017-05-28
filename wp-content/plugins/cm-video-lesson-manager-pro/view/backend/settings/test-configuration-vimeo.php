<?php

?>

<div class="cmvl-test-configuration">

	<?php if ($videos['status'] == 200): ?>
		<div class="cmvl-success">Test passed - configuration works fine</div>
	<?php else: ?>
		<div class="cmvl-error">Test failed - configuration doesn't work</div>
	<?php endif; ?>
	
	<a href="#" class="button cmvl-show-details">Show details</a>
	
	<div class="cmvl-hidden-details">
	
		<div class="cmvl-test-videos">
			<h3>Videos: <?php echo (isset($videos['body']['total']) ? $videos['body']['total'] : 0); ?></h3>
			<?php if ($videos['status'] == 200): ?>
				<div class="cmvl-success">Success</div>
			<?php endif; ?>
			<?php if (empty($videos['body']['data'])): ?>
				<p>No videos.</p>
			<?php else: ?>
				<ul class="cmvl-videos-list"><?php foreach ($videos['body']['data'] as $item): ?>
					<li><?php echo esc_html($item['name']); ?></li>
				<?php endforeach; ?></ul>
			<?php endif; ?>
			<?php if (!empty($videos['body']['error'])): ?>
				<p class="cmvl-error"><?php echo $videos['body']['error']; ?></p>
			<?php endif; ?>
			<textarea><?php var_export($videos); ?></textarea>
		</div>
			
	</div>

</div>