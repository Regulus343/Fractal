@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::contentSection'))

	@include(Fractal::view('public.partials.pagination', true))

	@foreach ($mediaItems as $mediaItem)

		<div class="media-item-preview">

			@include(Fractal::mediaView('partials.item', true))

		</div>

	@endforeach

	@include(Fractal::view('public.partials.pagination', true))

@stop

@section('rightColumn')

	<div class="col-md-{{ (12 - Site::get('contentColumnWidth', 12)) }}">

		@include(Fractal::mediaView('partials.nav.sets', true))

		@include(Fractal::mediaView('partials.nav.types', true))

	</div>

@endsection