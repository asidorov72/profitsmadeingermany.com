<?php

?>
<div class="wistia_responsive_padding" style="padding:56.25% 0 0 0;position:relative;">
	<div class="wistia_responsive_wrapper" style="height:100%;left:0;position:absolute;top:0;width:100%;">
		<iframe mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen width="100%" height="100%" data-video-id="<?php echo esc_attr($video->getId());
			?>" data-providers-video-id="<?php echo esc_attr($video->getProvidersId());
			?>" src="//fast.wistia.net/embed/iframe/<?php echo htmlspecialchars(urlencode($video->getProvidersId()));
			?>?videoFoam=true" allowtransparency="true" frameborder="0" scrolling="no" class="cmvl-player-wistia wistia_embed" name="wistia_embed" allowfullscreen></iframe>
	</div>
</div>

<script type="text/javascript">


</script>

<script src="//fast.wistia.net/assets/external/E-v1.js" async></script>