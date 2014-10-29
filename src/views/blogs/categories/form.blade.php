@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<script type="text/javascript">
		$(document).ready(function(){

			@if (!isset($update) || !$update)
				$('#name').keyup(function(){
					$('#name').val($('#name').val().replace(/  /g, ' '));

					var slug = strToSlug($('#name').val());
					$('#slug').val(slug);
				});
			@endif

			$('#slug').keyup(function(){
				var slug = strToSlug($('#slug').val());
				$('#slug').val(slug);
			});

		});
	</script>

	{{ Form::openResource() }}

		<div class="row">
			<div class="col-md-6">
				{{ Form::field('name') }}
			</div>
			<div class="col-md-6">
				{{ Form::field('slug') }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field(Form::submitResource(Fractal::lang('labels.category')), 'button') }}
			</div>
		</div>

	{{ Form::close() }}

@stop