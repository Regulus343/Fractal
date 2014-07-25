@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		$(document).ready(function(){

			$('#file').change(function(){
				var path      = $(this).val().split('\\');
				var filename  = path[(path.length - 1)].split('.');
				var basename  = filename[0];
				var extension = filename[(filename.length - 1)].toLowerCase();
				var name      = upperCaseWords(basename.replace(/_/g, ' ').replace(/-/g, ' '));
				$('#name').val(name);

				if ($.inArray(extension, ['jpg', 'png', 'gif']) >= 0) {
					$('#type').val('Image');

					$('#width').val('');
					$('#height').val('');

					$('#image-settings-area').removeClass('hidden');
				} else {
					if ($.inArray(extension, ['txt', 'odt', 'md', 'pdf', 'doc', 'docx']) >= 0) {
						$('#type').val('Document');
					} else if ($.inArray(extension, ['csv', 'ods', 'xls']) >= 0) {
						$('#type').val('Spreadsheet');
					} else if ($.inArray(extension, ['zip', 'gz', 'rar']) >= 0) {
						$('#type').val('Archive');
					} else if ($.inArray(extension, ['mp3', 'ogg', 'wav']) >= 0) {
						$('#type').val('Audio');
					} else if ($.inArray(extension, ['mp4', 'mpg', 'avi', 'flv']) >= 0) {
						$('#type').val('Video');
					} else {
						$('#type').val('');
					}

					$('#image-settings-area').addClass('hidden');
				}
			});

			$('#create-thumbnail').click(function(){
				if ($(this).prop('checked')) {
					$('#thumbnail-settings-area').removeClass('hidden');
				} else {
					$('#thumbnail-settings-area').addClass('hidden');
				}
			});

		});
	</script>

	{{ Form::openResource(array('files' => true)) }}
		<div class="row">
			<div class="col-md-4">
				{{ Form::field('file', 'file', array('class-field' => 'file-upload-button')) }}
			</div><div class="col-md-4">
				{{ Form::field('name') }}
			</div><div class="col-md-4">
				{{ Form::field('type', 'text', array('readonly-field' => 'readonly')) }}
			</div>
		</div>

		<div id="image-settings-area" class="hidden">
			<div class="row">
				<div class="col-md-4">
					{{ Form::field('width', 'number', array('placeholder-field' => 'Current Width')) }}
				</div><div class="col-md-4">
					{{ Form::field('height', 'number', array('placeholder-field' => 'Current Height')) }}
				</div><div class="col-md-4">
					{{ Form::field(null, 'checkbox-set', array(
						'options' => array('crop', 'create_thumbnail')
					)) }}
				</div>
			</div>

			<div id="thumbnail-settings-area" class="row{{ HTML::hiddenArea(!Form::value('create_thumbnail', 'checkbox'), true) }}">
				<div class="col-md-4">
					{{ Form::field('thumbnail_width', 'number', array('placeholder-field' => 'Current Width')) }}
				</div><div class="col-md-4">
					{{ Form::field('thumbnail_height', 'number', array('placeholder-field' => 'Current Height')) }}
				</div>
			</div>
		</div>

		{{ Form::field(str_replace('Create', 'Upload', Form::submitResource(Lang::get('fractal::labels.file'))), 'button') }}
	{{ Form::close() }}

@stop