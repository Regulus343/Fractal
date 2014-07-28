@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	@foreach ($articles as $article)
		<div class="article-preview">

			@include(Fractal::blogView('partials.article', true))

		</div>
	@endforeach

@stop