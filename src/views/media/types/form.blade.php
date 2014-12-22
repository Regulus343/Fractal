@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::contentSection'))

	<script type="text/javascript">
		$(document).ready(function(){

			@if (!isset($update) || !$update)
				$('#field-name').keyup(function(){
					$('#field-name').val($('#field-name').val().replace(/  /g, ' '));

					var slug = strToSlug($('#field-name').val());
					$('#field-slug').val(slug);
				});
			@endif

			$('#field-slug').keyup(function(){
				var slug = strToSlug($('#field-slug').val());
				$('#field-slug').val(slug);
			});

		});
	</script>

	{{ Form::openResource() }}

		<div class="row">
			<div class="col-md-4">
				{{ Form::field('name') }}
			</div>
			<div class="col-md-4">
				{{ Form::field('slug') }}
			</div>
			<div class="col-md-4">
				{{ Form::field('file_type_id', 'select', [
					'label'       => Fractal::lang('labels.fileType'),
					'options'     => Form::prepOptions(\Regulus\Fractal\Models\Content\FileType::orderBy('name')->get(), ['id', 'name']),
					'null-option' => 'Select a File Type',
				]) }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					{{ Form::field('media_source_required', 'checkbox') }}
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field(Form::submitResource(Fractal::lang('labels.mediaType')), 'button') }}
			</div>
		</div>

	{{ Form::close() }}

@stop