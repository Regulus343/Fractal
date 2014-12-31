@foreach ($mediaItems as $mediaItem)

	<div class="content-item-preview media-item-preview">

		@include(Fractal::mediaView('partials.item', true))

	</div>

@endforeach