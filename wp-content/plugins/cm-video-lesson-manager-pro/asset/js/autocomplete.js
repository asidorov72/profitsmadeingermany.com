jQuery(function($) {
	
	$('.cmvl-autocomplete-input').each(function() {
		
		var timeout = null;
		var input = $(this);
		var wrapper = input.parents('.cmvl-autocomplete-wrapper');
		var resultsContainer = wrapper.find('.cmvl-autocomplete-results');
		var callbackFunctionName = input.data('callback');
		input.keyup(function() {
			clearTimeout(timeout);
			timeout = setTimeout(function() {
				
				if (input.val().length == 0) {
					resultsContainer.html('');
					return;
				}
				
				resultsContainer.html('<div class="cmvl-loader-bar"></div>');
				
				var createClickHandler = function(item) {
					return function() {
						window[callbackFunctionName](item, wrapper);
						input.val('');
						resultsContainer.html('');
					};
				};
				
				var data = {'action': input.data('action'), 'model': input.data('model'), 'search': input.val(), 'nonce': input.data('nonce')};
				$.post(input.data('url'), data, function(response) {
					if (response.success) {
						resultsContainer.html('');
						if (response.results.length == 0) {
							resultsContainer.html('No results');
						}
						for (var i=0; i<response.results.length; i++) {
							var item = response.results[i];
							var html = $('<div/>', {"class": "cmvl-autocomplete-item"});
							html.attr('data-id', item.id);
							html.text(item.name);
							html.click(createClickHandler(item));
							resultsContainer.append(html);
						}
					}
				});
				
			}, 500);
		});
		
	});
	
});