<?php

?>

<div class="cmvl-update-wrapper" data-action="cmvl_upgrade_2_0_0" data-nonce="<?php echo esc_attr($nonce); ?>">

	<h1>Ready to upgrade your CM Video Lessons?</h1>
	
	<p>You have to proceed the upgrade process to migrate the video lessons plugin data to the 2.x version.</p>
	
	<h3>What's new?</h3>
	<ul>
		<li>Video list will be now imported to your Wordpress in order to avoid multiple API connections in the future.
		Video clips will be still streamed directly from Vimeo.</li>
		<li><strong>Notice that you'll have to modify your shortcodes:</strong> you'll have to use the video's post ID instead the Vimeo ID
			in the shortcodes as <code>[cmvl-playlist video=<strong>123</strong>]</code> - the 123 should be changed into the video's post ID.</li>
		<li>Introducing Wistia support.</li>
		<li>Using course and lessons terminology.</li>
		<li>Better reporting system.</li>
		<li>Support for certifications.</li>
		<li>Visual dashboard improvments.</li>
	</ul>
	
	<div class="cmvl-bottom">
		<a href="#" class="button button-primary cmvl-upgrade-btn">Proceed Upgrade</a>
		<div class="cmvl-result">
			<div class="cmvl-success">Upgraded successfuly.</div>
			<div class="cmvl-error">
				<strong>There was an error during the upgrade:</strong>
				<div class="cmvl-error-details"></div>
				<p>Please try again or install the previous plugin version and contact us at <a href="mailto:support@cminds.com">support@cminds.com</a>.</p>
			</div>
		</div>
	</div>
	

</div>