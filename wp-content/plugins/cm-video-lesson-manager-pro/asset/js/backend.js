jQuery(function($) {
	
	$('.cmvl-confirm-click').click(function(ev) {
		var msg = $(this).data('msg');
		if (!msg) msg = 'Do you confirm this action?';
		if (!confirm(msg)) {
			ev.stopPropagation();
			ev.preventDefault();
		}
	});
	
	// Settings tabs handler
	$('.cmvl-settings-tabs a').click(function() {
		var match = this.href.match(/\#tab\-([^\#]+)$/);
		$('#settings .settings-category.current').removeClass('current');
		$('#settings .settings-category-'+ match[1]).addClass('current');
		$('.cmvl-settings-tabs a.current').removeClass('current');
		$('.cmvl-settings-tabs a[href="#tab-'+ match[1] +'"]').addClass('current');
		this.blur();
	});
	if (location.hash.length > 0) {
		$('.cmvl-settings-tabs a[href="'+ location.hash +'"]').click();
	} else {
		$('.cmvl-settings-tabs li:first-child a').click();
	}
	
	
	$('.cmvl-admin-notice .cmvl-dismiss').click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		var btn = $(this);
		var data = {action: btn.data('action'), nonce: btn.data('nonce'), id: btn.data('id')};
		$.post(btn.attr('href'), data, function(response) {
			btn.parents('.cmvl-admin-notice').fadeOut('slow');
		});
	});
	

	// Unlock private Vimeo videos
	$('.cmvl-vimeo-unlock-private-videos').click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		var overlay = window.CMVL.Utils.overlay('<div class="cmvl-loader"></div>');
		var data = {action: 'cmvl_unlock_private_videos', nonce: CMVLBackend.ajaxNonce};
		$.post(CMVLBackend.ajaxUrl, data, function(response) {
			overlay.find('#cmvl-overlay-content').html(response);
		});
	});
	
	// Test Vimeo Configuration
	$('.cmvl-test-configuration').click(function(ev) {
		console.log('aaaaaaaa');
		ev.preventDefault();
		ev.stopPropagation();
		var btn = $(this);
		var overlay = window.CMVL.Utils.overlay('<div class="cmvl-loader"></div>');
		var data = {action: 'cmvl_test_configuration', api: btn.data('api')};
		$.post(CMVLBackend.ajaxUrl, data, function(response) {
			overlay.find('#cmvl-overlay-content').html(response);
			$('.cmvl-show-details', overlay).click(function(ev) {
				ev.preventDefault();
				ev.stopPropagation();
				$(this).hide();
				$('.cmvl-hidden-details', overlay).show();
			});
		});
	});
	
	// Micropayments
	$('.cmvl-cost-add').click(function() {
		var button = $(this);
		var p = button.parents('p').first();
		p.before(button.data('template').replace(/\%s/g, ''));
		p.prev().find('.cmvl-cost-remove').click(mpCostRemove);
		return false;
	});
	
	var mpCostRemove = function() {
		var button = $(this);
		button.parents('div').first().remove();
		return false;
	};
	$('.cmvl-cost-remove').click(mpCostRemove);
	
	var mpPriceRemove = function() {
		var button = $(this);
		button.parents('.cmmp-price').first().remove();
		return false;
	};
	$('.cmmp-price-remove').click(mpPriceRemove);
	
	var mpGroupRemove = function() {
		var button = $(this);
		button.parents('.cmmp-group').first().remove();
		return false;
	};
	$('.cmmp-group-remove').click(mpGroupRemove);
	
	$('.cmmp-group-add').click(function(e) {
		var button = $(this);
		var groups = button.parents('td').first().find('.cmmp-groups').first();
		var newGroupIndex = generateNewMpGroupIndex(button.parents('td').first());
		var source = button.data('template').replace(/__group_index__/g, newGroupIndex).replace('%s', '');
		groups.append(source);
		$('.cmmp-group-remove', groups).last().click(mpGroupRemove);
		$('.cmmp-price-add', groups).last().click(mpPriceAdd);
		return false;
	});
	
	var mpPriceAdd = function(e) {
		var button = $(this);
		var prices = button.parents('.cmmp-group').find('.cmmp-prices').first();
		var newPriceIndex = getNewmpPriceIndex(button.parents('.cmmp-group').first());
		var source = button.data('template').replace(/__item_index__/g, newPriceIndex).replace('%s', '');
		prices.append(source);
		$('.cmmp-price-remove', prices).last().click(mpPriceRemove);
		return false;
	};
	$('.cmmp-price-add').click(mpPriceAdd);
	
	var generateNewMpGroupIndex = function(container) {
		var max = $('#settings').data('maxMpGroupIndex');
		max = max ? max : 0;
		var groups = container.find('.cmmp-group');
		for (var i=0; i<groups.length; i++) {
			var index = parseInt($(groups[i]).data('groupIndex'));
			if (index > max) {
				max = index;
			}
		}
		max++;
		$('#settings').data('maxMpGroupIndex', max);
		return max;
	};
	
	var getNewmpPriceIndex = function(container) {
		var max = 0;
		var prices = container.find('.cmmp-price');
		for (var i=0; i<prices.length; i++) {
			var index = $(prices[i]).data('priceIndex');
			if (index > max) {
				max = index;
			}
		}
		max++;
		return max;
	};
	
	
	
	// Stats
	$('.cmvl-report-filter select').change(function() {
		$(this).parents('form').submit();
	});
	

	$('.cmvl-report-table .cmvl-actions a[data-confirm]').click(function() {
		return confirm($(this).data('confirm'));
	});
	
	
	$('.cmvl-subscription-add-form').each(function() {
		var form = $(this);
		
		form.find('a.add').click(function() {
			form.find('.inner').show();
			$(this).blur();
			return false;
		});
		
		var loginInput = form.find('input[name=user_login]');
		loginInput.autocomplete({
			source:    ajaxurl + '?action=cmvl_user_suggest',
			delay:     500,
			minLength: 2,
//			position:  position,
			open: function() {
				$( this ).addClass( 'open' );
			},
			close: function() {
				$( this ).removeClass( 'open' );
			}
		});
		
		var postInput = form.find('input[name=post_find]');
		postInput.autocomplete({
			source:    ajaxurl + '?action=cmvl_post_suggest',
			delay:     500,
			minLength: 2,
//			position:  position,
			open: function() {
				$( this ).addClass( 'open' );
			},
			close: function() {
				$( this ).removeClass( 'open' );
			},
			select: function( event, ui ) {
				postInput.val('');
				form.find('.cmvl-subscription-add-post span').text(ui.item.label);
				form.find('.cmvl-subscription-add-post input').val(ui.item.value);
				form.find('.cmvl-subscription-add-find-post').hide();
				form.find('.cmvl-subscription-add-post').show();
			}
		});
		
		form.find('.cmvl-subscription-add-post-remove').click(function() {
			postInput.val('');
			form.find('.cmvl-subscription-add-post span').text('');
			form.find('.cmvl-subscription-add-post input').val(0);
			form.find('.cmvl-subscription-add-find-post').show();
			form.find('.cmvl-subscription-add-post').hide();
			postInput.focus();
			return false;
		});
		
		
	});
	


	// After submit post-channel edit/add form
	$('form#post').submit(function(e) {
		var setError = function(msg) {
			e.preventDefault();
			e.stopPropagation();
			alert(msg);
		};
		// Force to choose at least one category for channel.
		if ($('#cmvl_categorychecklist input[type=checkbox]').length > 0 && $('#cmvl_categorychecklist input[type=checkbox]:checked').length == 0) {
			setError('Please select at least one course.');
		}
	});
	
	
	// Video Dashboard tabs shortcode settings
	$('.cmvl-settings-dashboard-tabs-outer').sortable({});
	var dashboardTabRemove = function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		var wrapper = $(this).parents('tr').first().find('.cmvl-settings-dashboard-tabs-outer');
		if (wrapper.find('.cmvl-settings-dashboard-tab').length > 1) {
			$(this).parents('.cmvl-settings-dashboard-tab').remove();
		}
	};
	$('.cmvl-settings-dashboard-tabs-outer .cmvl-remove').click(dashboardTabRemove);
	$('.cmvl-settings-dashboard-tabs-add-btn').click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		var wrapper = $(this).parents('tr').first().find('.cmvl-settings-dashboard-tabs-outer');
		var item = wrapper.find('.cmvl-settings-dashboard-tab').first().clone();
		wrapper.append(item);
		item.find('input[type=text], textarea').val('');
		$('.cmvl-remove', item).click(dashboardTabRemove);
	});
	
	
	// Metabox
	
	$('.cmvl-metabox-video-channel .cmvl-channel-remove').click(function() {
		var btn = $(this);
		var wrapper = btn.parents('.cmvl-metabox-video-channel');
		wrapper.attr('data-channel-exists', '0');
		wrapper.find('.cmvl-channel input').val('');
	});
	
	
	$( ".cmvl-metabox-channel-videos-list .cmvl-videos-list" ).sortable({
      placeholder: "cmvl-ui-state-highlight"
    });
    $( ".cmvl-metabox-channel-videos-list .cmvl-videos-list" ).disableSelection();
    
    
    $('.cmvl-metabox-channel-videos-list .cmvl-remove').click(function() {
    	$(this).parents('li').first().remove();
    });
    
    
    
    $('.cmvl-upgrade-btn').click(function(ev) {
    	ev.stopPropagation();
    	ev.preventDefault();
    	
    	var btn = $(this);
    	var wrapper = btn.parents('.cmvl-update-wrapper');
    	var loader = $('<div/>', {'class':'cmvl-loader-bar'});
    	btn.hide();
    	btn.after(loader);
    	
    	var data = {action: wrapper.data('action'), nonce: wrapper.data('nonce')};
//    	console.log(data);
    	
    	$.post(ajaxurl, data, function(response) {
    		loader.remove();
    		if (response == 'ok') {
    			$('.cmvl-success', wrapper).show();
    		} else {
    			$('.cmvl-error', wrapper).show();
    			$('.cmvl-error-details', wrapper).html(response);
    			btn.show();
    		}
    	});
    	
    });
    
    
    $('.cmvl-time-log-filter').submit(function(ev) {
    	ev.stopPropagation();
    	ev.preventDefault();
    	var form = $(this);
    	var url = form.attr('action');
    	url += '&date_from=' + encodeURIComponent(form.find('input[name=date_from]').val())
    		+ '&date_to=' + encodeURIComponent(form.find('input[name=date_to]').val());
    	location.href = url;
    });
	
	
});


function cmvl_video_edit_set_channel(channel, autocomplete) {
	var container = autocomplete.parents('.cmvl-metabox-video-channel').find('.cmvl-channel');
	container.find('a').attr('href', channel.editUrl).text(channel.name);
	container.find('input').val(channel.id);
	autocomplete.parents('.cmvl-metabox-video-channel').attr('data-channel-exists', '1');
}


function cmvl_channel_edit_add_video(video, autocomplete) {
	var container = autocomplete.parents('.cmvl-metabox-channel-videos-list').find('.cmvl-videos-list');
	var item = container.find('li[data-id=0]').clone();
	item.find('a').text(video.name).attr('href', video.editUrl);
	item.attr('data-id', video.id);
	item.find('input').val(video.id);
	item.find('img').attr('src', video.thumb);
	container.append(item);
}