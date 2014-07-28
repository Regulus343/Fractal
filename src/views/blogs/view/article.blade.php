@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<div class="row">
		<div class="col-md-9">
			@include(Fractal::blogView('partials.article', true))
		</div>

		<div class="col-md-3">
			@include(Fractal::blogView('partials.nav_side', true))
		</div>
	</div>

@stop