<div class="col-md-4">
	{{ Form::open(Fractal::url($contentType.'/search'), 'post', array('id' => 'form-search')) }}

		{{ Form::text('search', null, array('placeholder' => Lang::get('fractal::labels.search'))) }}

		{{ Form::hidden('page', (isset($page) ? $page : 1)) }}
		{{ Form::hidden('changing_page', 0) }}

		{{ Form::hidden('sort_field') }}
		{{ Form::hidden('sort_order') }}

	{{ Form::close() }}
</div>