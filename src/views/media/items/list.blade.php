@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{-- Search Filters --}}
	@include(Fractal::view('partials.search_filters'))

	{{-- Content Table --}}
	@include(Fractal::view('partials.content_table', true))

@stop