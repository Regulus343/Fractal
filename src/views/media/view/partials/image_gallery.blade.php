<ul class="image-gallery">

	@foreach ($mediaItems as $mediaItem)

		<li>

			{{ $mediaItem->getThumbnailImage() }}

		</li>

	@endforeach

</ul>