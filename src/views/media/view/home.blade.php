@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::section'))

	@foreach ($media as $mediaItem)

		<div class="media-item-preview">

			@include(Fractal::mediaView('partials.item', true))

		</div>

	@endforeach

@stop