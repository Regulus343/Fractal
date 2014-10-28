@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::section'))

	@foreach ($articles as $article)

		<div class="article-preview">

			@include(Fractal::blogView('partials.article', true))

		</div>

	@endforeach

@stop