@section('search')

	{{ Form::open(['url' => Request::url().'/search', 'id' => 'form-search']) }}

		{{ Form::text('search', null, array('placeholder' => Lang::get('fractal::labels.searchBox'))) }}

		{{ Form::hidden('page', ['value' => (isset($page) ? $page : 1)]) }}
		{{ Form::hidden('changing_page', ['value' => 0]) }}

		{{ Form::hidden('sort_field') }}
		{{ Form::hidden('sort_order') }}

	{{ Form::close() }}

@endsection