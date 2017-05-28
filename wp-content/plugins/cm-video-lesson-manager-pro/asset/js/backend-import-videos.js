jQuery(function($) {
	
	var filterHandlerTimeout = null;
	var filterHandler = function() {
		var input = $(this);
		var text = input.val().toLowerCase();
		var wrapper = input.parents('.cmvl-video-list');
		var filter = function() {
			wrapper.find('tbody tr').show();
			if (text.length == 0) return;
			wrapper.find('tbody tr').each(function() {
				var row = $(this);
				var title = row.find('td.column-title').text();
				if (title.toLowerCase().indexOf(text) > -1) {
					
				} else {
					row.hide();
				}
			});
		};
		clearTimeout(filterHandlerTimeout);
		filterHandlerTimeout = setTimeout(filter, 500);
	};
	
	var selectAllHandler = function() {
		$(this).parents('.cmvl-video-list').find('tbody tr .check-column input[type=checkbox]').prop('checked', $(this).prop('checked'));
	};
	
	
	var importProceedHandler = function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		var btn = $(this);
		var form = btn.parents(form);
		var api = form.find('select[name=api]').val();
		var nonce = form.find('input[name=nonce]').val();
		var channelId = form.find('select[name=cmvl_channel]').val();
		var videos = form.find('tbody .check-column input[type=checkbox]:checked').map(function() {
			return this.value;
		}).toArray();
		var target = form.find('.cmvl-video-list');
		btn.hide();
		var loader = $('<div/>', {"class":"cmvl-loader-bar"});
		target.text('Importing videos...');
		target.append(loader);
		var data = {action: 'cmvl_import_videos_create', api: api, nonce: nonce, videos: videos, channelId: channelId};
		$.post(ajaxurl, data, function(response) {
			target.html(response);
		});
		
	};
	
	
	
	$('.cmvl-choose-api-btn').click(function() {
		
		var btn = $(this);
		var form = $(this).parents('form').first();
		var api = form.find('select[name=api]').val();
		var nonce = form.find('input[name=nonce]').val();
		
		if (!api || api == 0 || api.length == 0) return;
		
		var target = form.find('.cmvl-video-list');
		btn.hide();
		var loader = $('<div/>', {"class":"cmvl-loader-bar"});
		btn.after(loader);
		
		var data = {action: 'cmvl_import_videos_load_api', api: api, nonce: nonce};
		$.post(ajaxurl, data, function(response) {
			target.html(response);
			loader.remove();
			btn.show();
			$('.cmvl-video-import-search', target).keyup(filterHandler);
			$('.cmvl-select-all', target).click(selectAllHandler);
			$('.cmvl-video-import-proceed').click(importProceedHandler);
		});
		
	});
	
	
});