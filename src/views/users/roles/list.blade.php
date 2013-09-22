@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{ HTML::table(Config::get('fractal::tables.userRoles'), $roles) }}

	<a class="btn btn-default" href="{{ Fractal::url('user-roles/create') }}">{{ Lang::get('fractal::labels.createRole') }}</a>

@stop