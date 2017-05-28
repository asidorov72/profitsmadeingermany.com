<?php

use com\cminds\videolesson\model\Labels;

$content = $category->getDescription();
$contentWithoutTags = strip_tags($content);

if (strlen($contentWithoutTags) > 0):

?>
<div class="cmvl-course-description">
	<h3 class="cmvl-course-name"><?php echo $category->getName(); ?></h3>
	<h4 class="cmvl-course-description-header"><?php echo Labels::getLocalized('course_description'); ?></h4>
	<div class="cmvl-course-description-content"><?php echo $content; ?></div>
</div>

<?php endif; ?>