@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<style type="text/css">
		table tr td.actions a { margin-right: 4px; font-size: 16px; }
		table tr td.no-data { text-align: center; font-size: 18px; font-style: italic; }
	</style>

	{{ HTML::table(Config::get('fractal::tables.userRoles'), $roles) }}

	<a class="btn btn-default" href="{{ Fractal::url('user-roles/create') }}">{{ Lang::get('fractal::labels.createRole') }}</a>

@stop