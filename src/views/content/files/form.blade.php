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
					'options'        => Form::prepOptions(Regulus\Fractal\Models\Content\FileType::orderBy('name')->get(), array('id', 'name')),
					'null-option'    => 'None',
					'readonly-field' => 'readonly',
				)) }}
			</div>
		</div>

		{{-- Image Settings --}}
		@include(Fractal::view('partials.image_settings', true))

		{{ Form::field(str_replace('Create', 'Upload', Form::submitResource(Fractal::lang('labels.file'))), 'button') }}

	{{ Form::close() }}

@stop