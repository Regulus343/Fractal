$(document).ready(function()
{
	$('.download-media-item').on('mousedown', function()
	{
		$.ajax({ url: mediaUrl + '/log-download/' + $(this).attr('data-media-item-id') });
	});

	$('.image-gallery').lightGallery();

	$('a.modal-image').click(function(e)
	{
		e.preventDefault();

		var image = '<img src="'+$(this).attr('href')+'" alt="'+$(this).find('img').attr('alt')+'" ';
		image    += 'title="'+$(this).find('img').attr('title')+'" />';

		var title = $(this).find('img').attr('title') != "" ? $(this).find('img').attr('title') : "Image";

		$('#modal .modal-title').html(title);
		$('#modal .modal-body').html(image);
		$('#modal .modal-footer').hide();
		$('#modal').modal('show');
	});

	audiojs.events.ready(function()
	{
		audiojs.createAll();
	});
});