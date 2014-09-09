@extends(Config::get('fractal::layout'))

{{-- Search Filters --}}
@include(Fractal::view('media.items.partials.search_filters', true))

@section(Config::get('fractal::section'))

	{{-- Content Table --}}
	@include(Fractal::view('partials.content_table', true))

	{{-- Buttons --}}
	<a href="{{ Fractal::url('media/items/create') }}" class="btn btn-primary">
		<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Lang::get('fractal::labels.createItem') }}
	</a>

@stop