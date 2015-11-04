@extends(config('cms.layout'))

@section(config('cms.content_section'))

	<script type="text/javascript">
		$(document).ready(function()
		{
			@if (!isset($update) || !$update)

				$('#field-name').keyup(function()
				{
					var slug = Fractal.strToSlug($('#field-name').val());
					$('#field-role').val(slug);
				});

			@endif

			Fractal.initSlugField('field-role');
		});
	</script>

	{!! Form::openResource() !!}

		<div class="row">
			<div class="col-md-4">
				{!! Form::field('name') !!}
			</div>

			<div class="col-md-4">
				{!! Form::field('role') !!}
			</div>

			<div class="col-md-4">
				{!! Form::field('display_order', 'select', ['options' => Form::numberOptions(1, 36)]) !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-8">
				{!! Form::field('description', 'textarea') !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field(Form::submitResource(Fractal::transChoice('labels.role')), 'button') !!}
			</div>
		</div>

	{!! Form::close() !!}

@stop