<?php

use com\cminds\videolesson\helper\HtmlHelper;
use com\cminds\videolesson\controller\ProgressReportController;
use com\cminds\videolesson\controller\AutocompleteController;

?>
<form action="<?php echo esc_attr(filter_input(INPUT_SERVER, 'REQUEST_URI')); ?>" method="GET" class="cmvl-report-form cmvl-no-print">

	<p>
		<label>Group by: <?php echo HtmlHelper::renderSelect(ProgressReportController::PARAM_GROUP_BY, $groupByOptions,
				filter_input(INPUT_GET, ProgressReportController::PARAM_GROUP_BY)); ?></label>
		<label>Filter course:
			<?php echo HtmlHelper::renderSelect(ProgressReportController::PARAM_FILTER . '['. ProgressReportController::PARAM_FILTER_COURSE .']',
				(array('' => '') + $coursesList), $filter[ProgressReportController::PARAM_FILTER_COURSE]); ?></label>
		<label>Filter lesson:
			<?php printf('<input type="text" name="%s" value="%s" placeholder="name or id">',
					esc_attr(ProgressReportController::PARAM_FILTER . '['. ProgressReportController::PARAM_FILTER_LESSON .']'),
					esc_attr(isset($filterRecords['lesson']) ? $filterRecords['lesson']->getName() : $filter[ProgressReportController::PARAM_FILTER_LESSON])); ?></label>
		<label>Filter video:
			<?php printf('<input type="text" name="%s" value="%s" placeholder="name or id">',
					esc_attr(ProgressReportController::PARAM_FILTER . '['. ProgressReportController::PARAM_FILTER_VIDEO .']'),
					esc_attr(isset($filterRecords['video']) ? $filterRecords['video']->getName() : $filter[ProgressReportController::PARAM_FILTER_VIDEO])); ?></label>
		<label>Filter user:
			<?php if (!empty($usersList)): ?>
				<?php echo HtmlHelper::renderSelect('filter[user]', array('' => '') + $usersList, $filter['user']); ?>
			<?php else: ?>
				<?php printf('<input type="text" name="%s" value="%s" placeholder="name, email or id">',
						esc_attr(ProgressReportController::PARAM_FILTER . '['. ProgressReportController::PARAM_FILTER_USER .']'),
						esc_attr(isset($filterRecords['user']) ? $filterRecords['user']->user_login : $filter['user'])); ?></label>
			<?php endif; ?>
					
		<label><input type="checkbox" name="<?php echo esc_attr(ProgressReportController::PARAM_EXCLUDE_WITHOUT_PROGRESS);
			?>" value="1"<?php checked($excludeWithoutProgress, 1); ?>> Exclude rows without progress</label>
		
		<input type="hidden" name="page" value="<?php echo esc_attr(ProgressReportController::getMenuSlug()); ?>">
		<input type="hidden" name="<?php echo ProgressReportController::PARAM_ACTION; ?>" value="<?php echo ProgressReportController::ACTION_FETCH_REPORT; ?>">
		<input type="submit" value="Show report" class="button button-primary">
		
	</p>
	
	<?php if (ProgressReportController::ACTION_FETCH_REPORT != filter_input(INPUT_GET, ProgressReportController::PARAM_ACTION)): ?>
		<p>In filters you can use * as a multi-character wildcard and ? as a single-character wildcard.</p>
	<?php endif; ?>
	

</form>