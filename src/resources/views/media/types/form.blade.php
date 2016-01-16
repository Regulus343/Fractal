@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		$(document).ready(function()
		{
			@if (!isset($update) || !$update)

				$('#field-name').keyup(function()
				{
					var slug = Fractal.strToSlug($('#field-name').val());
					$('#field-slug').val(slug);
				});

			@endif

			Fractal.initSlugField();
		});
	</script>

	{!! Form::openResource() !!}

		<div class="row">
			<div class="col-md-4">
				{!! Form::field('name') !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('name_plural', 'text', ['label' => 'Name (Plural)']) !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('slug') !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{!! Form::field('file_type_id', 'select', [
					'label'       => Fractal::transChoice('labels.file_type'),
					'options'     => Form::prepOptions(\Regulus\Fractal\Models\Content\FileType::orderBy('name')->get(), ['id', 'name']),
					'null-option' => 'Select a File Type',
				]) !!}
			</div>
			<div class="col-md-6">
				<div class="form-group">
					{!! Form::field('media_source_required', 'checkbox') !!}
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field(Form::submitResource(Fractal::transChoice('labels.media_type')), 'button') !!}
			</div>
		</div>

	{!! Form::close() !!}

@stop