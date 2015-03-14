@extends(config('cms.layout'))

@section(config('cms.content_section'))

	{!! Form::openResource() !!}

		<div class="row">
			<div class="col-md-4">
				{!! Form::field('permission') !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('name') !!}
			</div>
			<div class="col-md-4">
				{!! Form::field('display_order', 'select', ['options' => Form::numberOptions(1, 36)]) !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{!! Form::field('description', 'textarea') !!}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{!! Form::field(Form::submitResource(Fractal::trans('labels.permission')), 'button') !!}
			</div>
		</div>

	{!! Form::close() !!}

@stop