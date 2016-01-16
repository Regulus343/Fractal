<ul class="image-gallery">

	@foreach ($mediaItems as $mediaItem)

		<li data-src="{{ $mediaItem->getImageUrl() }}" data-sub-html="{{ $mediaItem->getRenderedDescription(['previewOnly' => false, 'sanitized' => true]) }}">

			<img src="{{ $mediaItem->getThumbnailImageUrl() }}" alt="{{ strip_tags($mediaItem->getTitle()) }}" title="{{ strip_tags($mediaItem->getTitle()) }}" />

		</li>

	@endforeach

</ul>