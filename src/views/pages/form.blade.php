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

	{{ Form::openResource(null, null, 'pages') }}
		{{ Form::field('title') }}

		{{ Form::field('slug') }}

		{{ Form::field('content', 'textarea', array('class-field' => 'ckeditor', 'id-field' => 'content-editor')) }}

		{{ Form::field('set_side_content', 'checkbox') }}

		{{ Form::field('side_content', 'textarea', array('class-field-container' => HTML::hiddenArea(!Form::checked('set_side_content'), true), 'class-field' => 'ckeditor', 'id-field' => 'side-content-editor')) }}

		{{ Form::field('active', 'checkbox') }}

		{{ Form::field(Form::submitResource('Page', (isset($update) && $update)), 'button') }}
	{{ Form::close() }}

@stop