@extends(config('cms.layout'))

@section(config('cms.content_section'))

	{{-- Search Filters --}}
	@include(Fractal::view('partials.search_filters'))

	{{-- Content Table --}}
	@include(Fractal::view('partials.content_table', true))

@stop