<?php

use com\cminds\videolesson\helper\FormHtml;

?>
<div class="cmvl-video-metabox-import">

<div class="cmvl-choose-api">
	Choose API: <?php FormHtml::selectBox('api', $apiList, null); ?>
</div>

</div>