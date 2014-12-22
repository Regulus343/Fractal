$(document).ready(function(){

	$('.download-media-item').on('mousedown', function(){
		$.ajax({ url: mediaUrl + '/log-download/' + $(this).attr('data-media-item-id') });
	});

	$('.image-gallery').lightGallery();

	audiojs.events.ready(function() {
		audiojs.createAll();
	});

});