@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		$(document).ready(function(){

			@if (!isset($update) || !$update)
				$('#title').keyup(function(){
					$('#title').val($('#title').val().replace(/  /g, ' '));

					var slug = strToSlug($('#title').val());
					$('#slug').val(slug);
				});
			@endif

			$('#slug').keyup(function(){
				var slug = strToSlug($('#slug').val());
				$('#slug').val(slug);
			});

			$('#set-side-content').click(function(){
				if ($(this).prop('checked')) {
					$('#side-content-area').removeClass('hidden');
				} else {
					$('#side-content-area').addClass('hidden');
				}
			});

		});
	</script>

	{{ Form::openResource() }}
		{{ Form::field('title') }}

		{{ Form::field('slug') }}

		{{ Form::field('layout_template_id', 'select', array(
			'label'       => 'Layout Template',
			'options'     => Form::prepOptions(Regulus\Fractal\ContentLayoutTemplate::orderBy('name')->get(), array('id', 'name')),
			'null-option' => 'Select a layout template'
		)) }}

		{{ Form::field('content', 'textarea', array('class-field' => 'ckeditor', 'id-field' => 'content-editor')) }}

		{{ Form::field('active', 'checkbox') }}

		{{ Form::field(Form::submitResource(Lang::get('fractal::labels.page'), (isset($update) && $update)), 'button') }}
	{{ Form::close() }}

@stop