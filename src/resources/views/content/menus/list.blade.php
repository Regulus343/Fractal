@extends(config('cms.layout'))

@section(config('cms.content_section'))

	{{-- Content Table --}}
	@include(Fractal::view('partials.content_table', true))

@stop