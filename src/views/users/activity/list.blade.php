@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::contentSection'))

	{{-- Content Table --}}
	@include(Fractal::view('partials.content_table', true))

@stop