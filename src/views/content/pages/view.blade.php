@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::contentSection'))

	{{ $page->getRenderedContent() }}

@stop