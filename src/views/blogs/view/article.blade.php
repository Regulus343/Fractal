@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{ $article->getRenderedContent() }}

@stop