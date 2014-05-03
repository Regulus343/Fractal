@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{-- Content Table --}}
	@include(Fractal::view('partials.content_table', true))

	{{-- Buttons --}}
	<a href="{{ Fractal::url('menus/create') }}" class="btn btn-primary">
		<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Lang::get('fractal::labels.createMenu') }}
	</a>

@stop