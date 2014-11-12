<ul class="image-list" id="select-item">
	@foreach ($mediaItems as $mediaItem)

		@if (!in_array((int) $setId, $mediaItem->getSetIds()))

			<li data-item-id="{{ $mediaItem->id }}" data-title="{{ $mediaItem->title }}" data-image-url="{{ $mediaItem->getImageUrl(true) }}">
				<img src="{{ $mediaItem->getImageUrl(true) }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" />
			</li>

		@endif
	@endforeach
</ul>