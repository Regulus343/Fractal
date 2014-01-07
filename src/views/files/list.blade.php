@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{-- Content Table --}}
	@include(Fractal::view('partials.content_table', true))

	{{-- Buttons --}}
	<a class="btn btn-primary" href="{{ Fractal::url('files/create') }}">
		<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Lang::get('fractal::labels.uploadFile') }}
	</a>

@stop