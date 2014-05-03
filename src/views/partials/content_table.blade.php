{{-- Search & Pagination --}}
@include(Fractal::view('partials.search_pagination', true))

{{-- Content Table --}}
<div class="row">
	<div class="col-md-12">
		{{ Fractal::createTable($content) }}
	</div>
</div>

{{-- Bottom Pagination --}}
@include(Fractal::view('partials.pagination', true))