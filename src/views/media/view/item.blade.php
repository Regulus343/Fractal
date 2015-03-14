@extends(Config::get('fractal::layoutPublic'))

@section(config('cms.content_section'))

	@include(Fractal::mediaView('partials.item', true))

@endsection

@section('rightColumn')

	<div class="col-md-{{ (12 - Site::get('contentColumnWidth', 12)) }}">

		@include(Fractal::mediaView('partials.nav.items', true))

		@include(Fractal::mediaView('partials.nav.sets', true))

		@include(Fractal::mediaView('partials.nav.types', true))

	</div>

@endsection