<?php

use com\cminds\videolesson\model\Labels;

$content = $channel->getContent();
$contentWithoutTags = strip_tags($content);

if (strlen($contentWithoutTags) > 0):

?>
<div class="cmvl-lesson-description">
	<h3 class="cmvl-lesson-name"><?php echo $channel->getName(); ?></h3>
	<h4 class="cmvl-lesson-description-header"><?php echo Labels::getLocalized('lesson_description'); ?></h4>
	<div class="cmvl-lesson-description-content"><?php echo $content; ?></div>
</div>

<?php endif; ?>