@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::section'))

	@foreach ($articles as $article)

		<div class="article-preview">

			@include(Fractal::blogView('partials.article', true))

		</div>

	@endforeach

@endsection

@section('rightColumn')

	<div class="col-md-{{ (12 - Site::get('contentColumnWidth', 12)) }}">

		@include(Fractal::blogView('partials.nav.categories', true))

	</div>

@endsection