$(document).ready(function(){

	if (fileTypeExtensions === undefined)
		fileTypeExtensions = {};

	$('#file').change(function(){
		var path      = $(this).val().split('\\');
		var filename  = path[(path.length - 1)].split('.');
		var basename  = filename[0];
		var extension = filename[(filename.length - 1)].toLowerCase();
		var name      = upperCaseWords(basename.replace(/_/g, ' ').replace(/-/g, ' '));

		$('#name').val(name);
		$('#title').val(name);

		var typeSet = false;
		for (var fileTypeId in fileTypeExtensions) {
			extensions = fileTypeExtensions[fileTypeId];

			if ($.inArray(extension, extensions) >= 0) {
				$('#type-id').val(fileTypeId);
				$('#file-type-id').val(fileTypeId);

				$('#type-id-hidden').val(fileTypeId);
				$('#file-type-id-hidden').val(fileTypeId);

				typeSet = true;
			}
		}

		if (!typeSet) {
			$('#type-id').val('');
			$('#file-type-id').val('');
		}

		$('#type-id').select2();
		$('#file-type-id').select2();

		if ($.inArray(extension, ['jpg', 'png', 'gif']) >= 0) {
			$('#width').val('');
			$('#height').val('');

			$('#image-settings-area').removeClass('hidden');
		} else {
			$('#image-settings-area').addClass('hidden');
		}

		$('#title').val($('#title').val().replace(/  /g, ' '));

		var slug = strToSlug($('#title').val());
		$('#slug').val(slug);
	});

	$('#create-thumbnail').click(function(){
		if ($(this).prop('checked')) {
			$('#thumbnail-settings-area').removeClass('hidden');
		} else {
			$('#thumbnail-settings-area').addClass('hidden');
		}
	});

});