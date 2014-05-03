@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{-- Content Table --}}
	@include(Fractal::view('partials.content_table', true))

	{{-- Buttons --}}
	<a href="{{ Fractal::url('users/create') }}" class="btn btn-primary">
		<span class="glyphicon glyphicon-user"></span>&nbsp; {{ Lang::get('fractal::labels.createUser') }}
	</a>

@stop