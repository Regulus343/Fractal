@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{ Form::openResource() }}

		<div class="row">
			<div class="col-md-6">
				{{ Form::field('name') }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{{ Form::field('layout', 'textarea', ['class-field' => 'tab']) }}
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				{{ Form::field(Form::submitResource(Fractal::lang('labels.layoutTemplate')), 'button') }}
			</div>
		</div>

	{{ Form::close() }}

@stop