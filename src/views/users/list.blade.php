@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{ HTML::table(Config::get('fractal::tables.users'), $users) }}

	<a class="btn btn-default" href="{{ Fractal::url('users/create') }}">{{ Lang::get('fractal::labels.createUser') }}</a>

@stop