@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{ HTML::table(Config::get('fractal::tables.menus'), $menus) }}

	<a class="btn btn-default" href="{{ Fractal::url('menus/create') }}">{{ Lang::get('fractal::labels.createMenu') }}</a>

@stop