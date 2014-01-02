@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{ Form::open() }}

		@foreach ($settings as $setting)
			<div class="row">
				<div class="col-md-12">
					{{ Form::field('setting_'.$setting->id, 'text', array('label' => $setting->name)) }}
				</div>
			</div>
		@endforeach

		{{ Form::field(Form::submitResource(Lang::get('fractal::labels.settings'), true), 'button') }}
	{{ Form::close() }}

@stop