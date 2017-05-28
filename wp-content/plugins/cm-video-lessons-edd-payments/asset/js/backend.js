jQuery(function($) {
	
	$('.cmvl-eddpay-costs .cmvl-eddpay-delete').click(function(ev) {
		$(this).parents('tr').first().remove();
	});
	
	
	$('.cmvl-eddpay-costs .cmvl-eddpay-add-price').click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		
		var wrapper = $(this).parents('.cmvl-eddpay-costs');
		var table = wrapper.find('table');
		var template = table.find('tr.template').clone(true, true);
		table.find('tbody').append(template);
		template.removeClass('template');
		
	});
	
	
});