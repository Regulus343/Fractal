@extends(Config::get('fractal::layoutPublic'))

@section(config('cms.content_section'))

	@include(Fractal::view('public.partials.pagination', true))

	@foreach ($articles as $article)

		<div class="content-item-preview article-preview">

			@include(Fractal::blogView('partials.article', true))

		</div>

	@endforeach

	@include(Fractal::view('public.partials.pagination', true))

@endsection

@section('rightColumn')

	<div class="col-md-{{ (12 - Site::get('contentColumnWidth', 12)) }}">

		@include(Fractal::blogView('partials.nav.categories', true))

	</div>

@endsection