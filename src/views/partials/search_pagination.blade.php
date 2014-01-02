<div class="row search-pagination-area">
	{{-- Search --}}
	@include(Config::get('fractal::viewsLocation').'partials.search')

	{{-- Pagination --}}
	@include(Config::get('fractal::viewsLocation').'partials.pagination')
</div>