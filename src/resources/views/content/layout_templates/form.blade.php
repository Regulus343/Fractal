@extends(config('cms.layout'))

@section(config('cms.content_section'))

	{!! Form::openResource() !!}

		<div class="row">
			<div class="col-md-12">
				{!! Form::field('name') !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field('layout', 'textarea', ['class-field' => 'tab']) !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field(Form::submitResource(Fractal::transChoice('labels.layout_template')), 'button') !!}
			</div>
		</div>

	{!! Form::close() !!}

@stop