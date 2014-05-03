@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{ $page->getRenderedContent() }}

@stop