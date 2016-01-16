$(document).ready(function()
{
	if (fileTypeExtensions === undefined)
		fileTypeExtensions = {};

	$('input[file]').change(function()
	{
		var path     = $(this).val().split('\\');
		var filename = path[(path.length - 1)].split('.');
		var basename = filename[0];
	});

	$('#field-file').change(function()
	{
		var path      = $(this).val().split('\\');
		var filename  = path[(path.length - 1)].split('.');
		var basename  = filename[0];
		var extension = filename[(filename.length - 1)].toLowerCase();
		var name      = Fractal.upperCaseWords(basename.replace(/_/g, ' ').replace(/-/g, ' '));

		$(this).parents('div').find('.file-dummy').val(basename);

		var fileTypeId = false;
		for (var fileTypeIdListed in fileTypeExtensions)
		{
			extensions = fileTypeExtensions[fileTypeIdListed];

			if ($.inArray(extension, extensions) >= 0)
			{
				$('#field-type-id').val(fileTypeIdListed);
				$('#field-file-type-id').val(fileTypeIdListed);

				fileTypeId = fileTypeIdListed;
			}
		}

		if (!fileTypeId)
		{
			$('#field-type-id').val('');
			$('#field-file-type-id').val('');
		}

		$('#field-type-id').select2();
		$('#field-file-type-id').select2();

		$('#field-remove-file').val('');

		Formation.ajaxForSelect({
			type:                 'get',
			url:                  Fractal.createUrl('media/items/get-types-for-file-type/' + (fileTypeId ? fileTypeId : 0)),
			optionValue:          'id',
			optionLabel:          'name',
			targetSelect:         '#field-media-type-id',
			nullOption:           'Select a media type',
			callbackFunction:     'refreshMediaTypeSelect'
		});

		if ($.inArray(extension, ['jpg', 'png', 'gif']) >= 0)
		{
			$('#image-settings-area').removeClass('hidden');
			$('#thumbnail-image-area input').val('').attr('disabled', 'disabled');
			$('#thumbnail-image-area button').attr('disabled', 'disabled');
		}
		else
		{
			$('#image-settings-area').addClass('hidden');
			$('#thumbnail-image-area input').attr('disabled', false);
			$('#thumbnail-image-area button').attr('disabled', false);
		}

		if (update !== true)
		{
			name = name.replace(/  /g, ' ');
			$('#field-name').val(name);
			$('#field-title').val(name);

			var slug = Fractal.strToSlug($('#field-title').val());
			$('#field-slug').val(slug);
		}
	});

	$('#field-file-type-id').change(function()
	{
		Formation.ajaxForSelect({
			type:             'get',
			url:              Fractal.createUrl('media/items/get-types-for-file-type/' + $(this).val()),
			optionValue:      'id',
			optionLabel:      'name',
			targetSelect:     '#field-media-type-id',
			nullOption:       'Select a media type',
			callbackFunction: 'refreshMediaTypeSelect'
		});
	});

	$('#field-thumbnail-image').change(function()
	{
		$('#field-remove-thumbnail-image').val('');
	});

	$('#field-create-thumbnail').click(function()
	{
		if ($(this).prop('checked')) {
			$('#thumbnail-settings-area').removeClass('hidden');
		} else {
			$('#thumbnail-settings-area').addClass('hidden');
		}
	});

});

function refreshMediaTypeSelect() {
	$('#field-media-type-id').select2();
}