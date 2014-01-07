@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{-- Content Table --}}
	@include(Fractal::view('partials.content_table', true))

	{{-- Buttons --}}
	<a class="btn btn-primary" href="{{ Fractal::url('users/create') }}">
		<span class="glyphicon glyphicon-user"></span>&nbsp; {{ Lang::get('fractal::labels.createUser') }}
	</a>

@stop