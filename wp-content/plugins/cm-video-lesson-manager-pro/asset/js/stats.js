jQuery(document).bind('CMVL.videoReady', function(ev) {
	
	var $ = jQuery;
	var iframe = $(ev.target);
	var videoId = iframe.attr('data-video-id');
	var listener = new CMVL_StatsListener(videoId);
	
	console.log('stats init for video id = ' + videoId);
	
	// Vimeo handler
	// ======================================================================
	if (iframe.hasClass('cmvl-player-vimeo')) {
		
		var player = new Vimeo.Player(iframe[0]);
		player.on('play', function(ev) {
//			console.log('play event')
			listener.setState('playing');
			listener.startTracking(ev.seconds);
		});
		player.on('pause', function(ev) {
//			console.log('pause event')
			listener.updateProgress(ev.seconds);
			listener.registerInterval();
			listener.setState('stop');
		});
		player.on('ended', function(ev) {
//			console.log('end event')
			listener.updateProgress(ev.seconds);
			listener.registerInterval();
			listener.setState('stop');
		});
		player.on('seeked', function(ev) {
//			console.log('seeked event', ev)
			if (listener.getState() == 'playing') {
				// Remove last time registered by timeupdate event since it's the new time after seeking (Vimeo bug?)
				listener.timeStack.pop();
				listener.registerInterval();
			}
			listener.startTracking(ev.seconds);
		});
		player.on('timeupdate', function(ev) {
//			console.log('timeupdate event', ev)
			listener.updateProgress(ev.seconds);
		});
		
	}
	
	// Wistia handler
	// ======================================================================
	if (iframe.hasClass('cmvl-player-wistia')) {
		var providersId = iframe.attr('data-providers-video-id');
		window._wq = window._wq || [];
		_wq.push({ id: providersId, onReady: function(video) {
			video.bind('play', function() {
				console.log('play event')
				listener.setState('playing');
				listener.startTracking(video.time());
			});
			video.bind('pause', function() {
				console.log('pause event')
				listener.updateProgress(video.time());
				listener.registerInterval();
				listener.setState('stop');
			});
			video.bind('end', function() {
				console.log('end event')
				listener.updateProgress(video.time());
				listener.registerInterval();
				listener.setState('stop');
			});
			video.bind("seek", function(currentTime, lastTime) {
				console.log('seek event')
				if (listener.getState() == 'playing') {
					// Remove last time registered by timechange event since it's the new time after seeking
					listener.timeStack.pop();
					listener.registerInterval();
				}
				listener.startTracking(currentTime);
			});
			video.bind('timechange', function(time) {
				listener.updateProgress(time);
			});
		}});
	}
	
});



// Listener class
// ============================================================================================================================================

CMVL_StatsListener = function(videoId) {
	this.videoId = videoId;
	this.timeStack = [];
	this.state = 'stop';
}


CMVL_StatsListener.prototype.startTracking = function(time) {
	this.timeStack = [];
	this.timeStack.push(Math.floor(time));
	return this;
};


CMVL_StatsListener.prototype.updateProgress = function(time) {
	this.timeStack.push(Math.ceil(time));
	return this;
};


CMVL_StatsListener.prototype.registerInterval = function() {
	console.log('state = '+ this.state)
	var startTime = this.timeStack[0];
	var stopTime = this.timeStack[this.timeStack.length-1];
//	console.log('startTime', startTime);
//	console.log('stopTime', stopTime);
	if (startTime < stopTime && startTime != stopTime) {
		
		console.log('registered from '+ startTime + ' to ' + stopTime);
		
		var data = {
				action: 'cmvl_video_watching_stats',
				nonce: CMVLSettings.ajaxNonce,
				start: startTime,
				stop: stopTime,
				videoId: this.videoId
//				channelId: currentVideo.data('channelId')
			};
			jQuery.post(CMVLSettings.ajaxUrl, data, function(response) {
//				console.log('add success');
//				if (playlist.statsAjaxSuccessCallback) {
//					playlist.statsAjaxSuccessCallback();
//				}
			});
		
	} else {
//		console.log('didnt registered 2')
	}
};

CMVL_StatsListener.prototype.setState = function(state) {
	this.state = state;
	return this;
};

CMVL_StatsListener.prototype.getState = function() {
	return this.state;
};