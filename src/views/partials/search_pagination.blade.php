<div class="row search-pagination-area">
	{{-- Search --}}
	@include(Fractal::view('partials.search', true))

	{{-- Additional Search Filters --}}
	@yield('search-filters')

	{{-- Pagination --}}
	@include(Fractal::view('partials.pagination', true))
</div>