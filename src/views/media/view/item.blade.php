@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::section'))

	@include(Fractal::mediaView('partials.item', true))

@endsection

@section('rightColumn')

	<div class="col-md-{{ (12 - Site::get('contentColumnWidth', 12)) }}">

		@include(Fractal::mediaView('partials.nav.items', true))

	</div>

@endsection