@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{-- Content Table --}}
	<div class="row">
		<div class="col-md-12">
			{{ HTML::table(Config::get('fractal::tables.pages'), $pages) }}
		</div>
	</div>

	<a class="btn btn-default" href="{{ Fractal::url('pages/create') }}">{{ Lang::get('fractal::labels.createPage') }}</a>

@stop