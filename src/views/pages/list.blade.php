@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{ HTML::table(Config::get('fractal::tables.pages'), $pages) }}

	<a class="btn btn-default" href="{{ Fractal::url('pages/create') }}">{{ Lang::get('fractal::labels.createPage') }}</a>

@stop