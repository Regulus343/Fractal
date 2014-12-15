@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::contentSection'))

	@include(Fractal::view('public.partials.pagination', true))

	@if (Site::get('imageGallery'))

		@include(Fractal::mediaView('partials.image_gallery', true))

	@else

		@include(Fractal::mediaView('partials.list', true))

	@endif

	@include(Fractal::view('public.partials.pagination', true))

@stop

@section('rightColumn')

	<div class="col-md-{{ (12 - Site::get('contentColumnWidth', 12)) }}">

		@include(Fractal::mediaView('partials.nav.sets', true))

		@include(Fractal::mediaView('partials.nav.types', true))

	</div>

@endsection