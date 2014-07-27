@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<ul>
		@foreach ($articles as $article)

			<li><a href="{{ $article->getUrl() }}">{{ $article->title }}</a></li>

		@endforeach
	</ul>

@stop