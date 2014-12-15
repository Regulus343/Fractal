@foreach ($mediaItems as $mediaItem)

	<div class="media-item-preview">

		@include(Fractal::mediaView('partials.item', true))

	</div>

@endforeach