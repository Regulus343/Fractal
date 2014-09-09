{{ Form::open(array('url' => Request::url().'/search', 'id' => 'form-search')) }}

	<div class="row search-pagination-area">
		{{-- Search --}}
		@include(Fractal::view('partials.search', true))

		{{-- Pagination --}}
		@include(Fractal::view('partials.pagination', true))
	</div>

	{{-- Additional Search Filters --}}
	@yield('search-filters')

{{ Form::close() }}