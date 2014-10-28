@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::section'))

	{{ $page->getRenderedContent() }}

@stop