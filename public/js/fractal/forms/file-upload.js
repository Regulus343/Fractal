$(document).ready(function(){

	if (fileTypeExtensions === undefined)
		fileTypeExtensions = {};

	$('#field-file').change(function(){
		var path      = $(this).val().split('\\');
		var filename  = path[(path.length - 1)].split('.');
		var basename  = filename[0];
		var extension = filename[(filename.length - 1)].toLowerCase();
		var name      = upperCaseWords(basename.replace(/_/g, ' ').replace(/-/g, ' '));

		$('#field-name').val(name);
		$('#field-title').val(name);

		var fileTypeId = false;
		for (var fileTypeIdListed in fileTypeExtensions) {
			extensions = fileTypeExtensions[fileTypeIdListed];

			if ($.inArray(extension, extensions) >= 0) {
				$('#field-type-id').val(fileTypeIdListed);
				$('#field-file-type-id').val(fileTypeIdListed);

				fileTypeId = fileTypeIdListed;
			}
		}

		if (!fileTypeId) {
			$('#field-type-id').val('');
			$('#field-file-type-id').val('');
		}

		$('#field-type-id').select2();
		$('#field-file-type-id').select2();

		Formation.ajaxForSelect({
			type:                 'get',
			url:                  baseUrl + '/media/items/get-types-for-file-type/' + (fileTypeId ? fileTypeId : 0),
			optionValue:          'id',
			optionLabel:          'name',
			targetSelect:         '#media-type-id',
			nullOption:           'Select a media type',
			callbackFunction:     'refreshMediaTypeSelect'
		});

		if ($.inArray(extension, ['jpg', 'png', 'gif']) >= 0) {
			$('#image-settings-area').removeClass('hidden');
			$('#thumbnail-image-area input').val('').attr('disabled', 'disabled');
			$('#thumbnail-image-area button').attr('disabled', 'disabled');
		} else {
			$('#image-settings-area').addClass('hidden');
			$('#thumbnail-image-area input').attr('disabled', false);
			$('#thumbnail-image-area button').attr('disabled', false);
		}

		$('#field-title').val($('#field-title').val().replace(/  /g, ' '));

		var slug = strToSlug($('#field-title').val());
		$('#field-slug').val(slug);
	});

	$('#field-create-thumbnail').click(function(){
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