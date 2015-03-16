@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		var update             = {{ (isset($update) && $update ? 'true' : 'false') }};
		var fileTypeExtensions = {!! json_encode($fileTypeExtensions) !!};
	</script>
	<script type="text/javascript" src="{{ Site::js('fractal/forms/file-upload', 'regulus/fractal') }}"></script>

	{!! Form::openResource(array('files' => true)) !!}

		<div class="row">
			<div class="col-md-4">
				{!! Form::field('file', 'file', array('class-field' => 'file-upload-button')) !!}

				@if (isset($file))
					<div id="file-area">

						@if ($file->type_id == 1)

							<a href="{{ $file->getUrl() }}" target="_blank" class="thumbnail-image">
								<img src="{{ $file->getUrl() }}" alt="{{ $file->name }}" title="{{ $file->name }}" />
							</a>

						@else

							<div class="file-link">
								<label>Current File:</label>

								<a href="{{ $file->getUrl() }}" target="_blank" class="file-link">{{ $file->filename }}</a>
							</div>

						@endif

					</div><!-- /#file-area -->
				@endif
			</div>
			<div class="col-md-4">
				{!! Form::field('name') !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('type_id', 'select', [
					'label'          => 'Type',
					'options'        => Form::prepOptions(Regulus\Fractal\Models\Content\FileType::orderBy('name')->get(), ['id', 'name']),
					'null-option'    => 'None',
					'readonly-field' => 'readonly',
				]) !!}
			</div>
		</div>

		{{-- Image Settings --}}
		@include(Fractal::view('partials.image_settings', true))

		{!! Form::field(str_replace('Create', 'Upload', Form::submitResource(Fractal::trans('labels.file'))), 'button') !!}

	{!! Form::close() !!}

@stop