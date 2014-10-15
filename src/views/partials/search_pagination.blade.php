<div class="row search-pagination-area">
	{{-- Search --}}
	@include(Fractal::view('partials.search', true))

	{{-- Pagination --}}
	@include(Fractal::view('partials.pagination', true))
</div>

{{-- Additional Search Filters --}}
@yield('search-filters')