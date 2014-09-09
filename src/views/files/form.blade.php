@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		var fileTypeExtensions = {{ json_encode($fileTypeExtensions) }};
	</script>
	<script type="text/javascript" src="{{ Site::js('fractal/forms/file-upload', 'regulus/fractal') }}"></script>

	{{ Form::openResource(array('files' => true)) }}

		<div class="row">
			<div class="col-md-4">
				{{ Form::field('file', 'file', array('class-field' => 'file-upload-button')) }}
			</div>
			<div class="col-md-4">
				{{ Form::field('name') }}
			</div>
			<div class="col-md-4">
				{{ Form::field('type_id', 'select', array(
					'label'          => 'Type',
					'options'        => Form::prepOptions(Regulus\Fractal\Models\FileType::orderBy('name')->get(), array('id', 'name')),
					'null-option'    => 'None',
					'disabled-field' => 'disabled',
				)) }}

				{{ Form::hidden('type_id_hidden') }}
			</div>
		</div>

		<div id="image-settings-area" class="hidden">
			<div class="row">
				<div class="col-md-4">
					{{ Form::field('width', 'number', array('placeholder-field' => 'Current Width')) }}
				</div>
				<div class="col-md-4">
					{{ Form::field('height', 'number', array('placeholder-field' => 'Current Height')) }}
				</div>
				<div class="col-md-4">
					{{ Form::field(null, 'checkbox-set', array(
						'options' => array('crop', 'create_thumbnail')
					)) }}
				</div>
			</div>

			<div id="thumbnail-settings-area" class="row{{ HTML::hiddenArea(!Form::value('create_thumbnail', 'checkbox'), true) }}">
				<div class="col-md-4">
					{{ Form::field('thumbnail_width', 'number', array('placeholder-field' => 'Current Width')) }}
				</div>
				<div class="col-md-4">
					{{ Form::field('thumbnail_height', 'number', array('placeholder-field' => 'Current Height')) }}
				</div>
			</div>
		</div>

		{{ Form::field(str_replace('Create', 'Upload', Form::submitResource(Lang::get('fractal::labels.file'))), 'button') }}

	{{ Form::close() }}

@stop