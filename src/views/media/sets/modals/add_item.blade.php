<ul class="image-list" id="select-item">
	@foreach ($mediaItems as $mediaItem)

		@if (!in_array($mediaItem->id, $mediaItemsSelected))

			<li data-item-id="{{ $mediaItem->id }}" data-title="{{ $mediaItem->title }}" data-image-url="{{ $mediaItem->getImageUrl(true) }}" data-file-type-id="{{ $mediaItem->file_type_id }}">
				<img src="{{ $mediaItem->getImageUrl(true) }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" />
			</li>

		@endif
	@endforeach
</ul>