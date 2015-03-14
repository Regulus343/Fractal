@extends(Config::get('fractal::layoutPublic'))

@section(config('cms.content_section'))

	@include(Fractal::blogView('partials.article', true))

@endsection

@section('rightColumn')

	<div class="col-md-{{ (12 - Site::get('contentColumnWidth', 12)) }}">

		@include(Fractal::blogView('partials.nav.articles', true))

		@include(Fractal::blogView('partials.nav.categories', true))

	</div>

@endsection