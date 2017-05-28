<?php

$filterValues = array_filter($filter);

?>
<p>Found <?php echo count($data); ?> results
	<span class="cmvl-remark">
		<?php if (empty($filterValues)): echo 'without filter,'; ?>
		<?php else: ?>
			for
			<?php $first = true; ?>
			<?php foreach ($filterValues as $key => $value): ?>
				<?php if (strlen($value) > 0): ?>
					<?php if ($first) { $first = false; } else { echo ', '; }?>
					<?php echo esc_html($key); ?>:
					<?php echo esc_html(isset($filterRecords[$key]) ? ($key == 'user' ? $filterRecords[$key]->user_login : $filterRecords[$key]->getName()) : $value); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if (!empty($groupBy)) echo ', Group by: ' . $groupBy; ?>
	</span>
</p>