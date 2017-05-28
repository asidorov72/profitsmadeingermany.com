<?php

use com\cminds\videolesson\addon\eddpay\controller\SettingsController;

use com\cminds\videolesson\addon\eddpay\helper\HtmlHelper;
use com\cminds\videolesson\addon\eddpay\model\Download;

$eddCurrency = (function_exists('edd_get_currency') ? edd_get_currency() : '');
$units = array('min' => 'minutes', 'h' => 'hours', 'd' => 'days', 'w' => 'weeks', 'm' => 'months', 'y' => 'years');
$postEditUrl = add_query_arg(array('post' => '{{POST_ID}}', 'action' => 'edit'), admin_url('post.php'));

$writeRow = function($download) use ($units, $eddCurrency, $postEditUrl) {
	?><tr class="<?php echo ($download ? 'row' : 'template'); ?>">
		<td class="col-period">
			<input type="number" name="<?php echo SettingsController::FIELD_GLOBAL_COSTS; ?>[period][]" value="<?php echo ($download ? $download->getSubscriptionPeriodNumber() : ''); ?>" placeholder="Time" min="1" />
			<?php echo HtmlHelper::renderSelect(SettingsController::FIELD_GLOBAL_COSTS . '[unit][]', $units, ($download ? $download->getSubscriptionPeriodUnit() : 'd')); ?>
		</td>
		<td class="col-price">
			<input type="number" name="<?php echo SettingsController::FIELD_GLOBAL_COSTS; ?>[price][]" value="<?php echo ($download ? $download->getPrice() : ''); ?>" placeholder="Price" min="0" step="0.01" />
			<?php echo $eddCurrency; ?>
		</td>
		<td class="col-edd col-narrow">
			<?php if ($download): ?>
				<a href="<?php echo esc_attr($download ? $download->getEditUrl() : $postEditUrl); ?>">EDD</a>
			<?php endif; ?>
			<input type="hidden" name="<?php echo SettingsController::FIELD_GLOBAL_COSTS; ?>[edd_id][]" value="<?php echo ($download ? $download->getId() : 0); ?>" />
		</td>
		<td class="col-delete col-narrow">
			<span class="dashicons dashicons-no-alt cmvl-eddpay-delete" title="Delete price"></span>
		</td>
	</tr><?php
};

?>
<div class="cmvl-eddpay-costs">
	<table>
		<thead>
			<tr>
				<th>Time</th>
				<th>Price</th>
				<th class="col-narrow">EDD</th>
				<th class="col-narrow"></th>
			</tr>
		</thead>
		<tbody>
			<?php $writeRow(null); // write template ?>
			<?php foreach ($downloads as $download): ?>
				<?php $writeRow($download); ?>
			<?php endforeach; ?>
		</tbody>
	</table>
	<a href="#" class="button cmvl-eddpay-add-price">Add price</a>
</div>


<style type="text/css">
.cmvl-eddpay-costs table {min-width: 200px;}
.cmvl-eddpay-costs tr.template {display: none;}
.cmvl-eddpay-costs th {text-align: left !important;}
.cmvl-eddpay-costs th, .cmvl-eddpay-costs td {padding: 5px 10px !important; width: auto !important;}
.cmvl-eddpay-costs input[type=number] {width: 5em;}
.cmvl-eddpay-costs  td {white-space: nowrap;}
.cmvl-eddpay-costs thead, .cmvl-eddpay-costs tbody tr:nth-child(odd) {background: #f5f5f5;}
.cmvl-eddpay-costs .col-narrow {padding-left: 10px !important; width: 50px !important;}
.cmvl-eddpay-delete {cursor: pointer;}
</style>