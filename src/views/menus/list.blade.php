@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{-- Content Table --}}
	<div class="row">
		<div class="col-md-12">
			{{ HTML::table(Config::get('fractal::tables.menus'), $menus) }}
		</div>
	</div>

	<a class="btn btn-default" href="{{ Fractal::url('menus/create') }}">{{ Lang::get('fractal::labels.createMenu') }}</a>

@stop