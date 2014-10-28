@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::section'))

	<div class="row">
		<div class="col-md-9">
			@include(Fractal::mediaView('partials.item', true))
		</div>

		<div class="col-md-3">
			@include(Fractal::mediaView('partials.nav_side', true))
		</div>
	</div>

@stop