<div class="btn-group" id="select-thumbnail-image-type">
	<button type="button" class="btn btn-default{{ HTML::activeArea($defaultThumbnailImageType == "File", true) }}" data-type="File">
		<span class="glyphicon glyphicon-file"></span> File
	</button>

	<button type="button" class="btn btn-default{{ HTML::activeArea($defaultThumbnailImageType == "Media Item", true) }}" data-type="Media Item">
		<span class="glyphicon glyphicon-book"></span> Media Item
	</button>
</div>

<ul class="image-list select-thumbnail-image{{ HTML::hiddenArea($defaultThumbnailImageType != "File", true) }}" id="select-thumbnail-image-file">
	@foreach ($files as $file)

		<li{{ HTML::selectedArea($selectedFileId == $file->id) }} data-file-id="{{ $file->id }}" data-image-url="{{ $file->getImageUrl(true) }}">
			<img src="{{ $file->getImageUrl(true) }}" alt="{{ $file->name }}" title="{{ $file->name }}" />
		</li>

	@endforeach
</ul>

<ul class="image-list select-thumbnail-image{{ HTML::hiddenArea($defaultThumbnailImageType != "Media Item", true) }}" id="select-thumbnail-image-media-item">
	@foreach ($mediaItems as $mediaItem)

		<li{{ HTML::selectedArea($selectedMediaItemId == $mediaItem->id) }} data-media-item-id="{{ $mediaItem->id }}" data-image-url="{{ $mediaItem->getImageUrl(true) }}">
			<img src="{{ $mediaItem->getImageUrl(true) }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" />
		</li>

	@endforeach
</ul>