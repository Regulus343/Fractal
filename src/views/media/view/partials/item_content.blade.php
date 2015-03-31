<div class="content-item-body media-item-body{{ (isset($contentInserted) && $contentInserted ? ' media-item-inserted' : '') }}">

	@if (isset($contentInserted) && $contentInserted)

		<h3><a href="{{ $mediaItem->getUrl() }}">{!! $mediaItem->title !!}</a></h3>

	@endif

	@if ($mediaItem->hostedExternally())

		{!! $mediaItem->getEmbeddedContent() !!}

	@elseif ($mediaItem->getFileType() == "Image")

		<img src="{{ $mediaItem->getImageUrl() }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" class="media-image" />

	@else

		@if ($mediaItem->thumbnail)
			<img src="{{ $mediaItem->getImageUrl(true) }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" class="thumbnail-image" />
		@endif

		@if ($mediaItem->hasFile())

			<div class="media-item-area">

				@if ($mediaItem->getFileType() == "Audio")

					<audio src="{{ $mediaItem->getFileUrl() }}"{{ (!Site::get('mediaList') && !Site::get('articleList') ? ' preload="auto"' : '') }}></audio>

				@endif

				<a href="{{ $mediaItem->getFileUrl() }}" class="btn btn-primary btn-xs download-media-item" target="_blank" data-media-item-id="{{ $mediaItem->id }}">Download {{ $mediaItem->title }}</a>

			</div>

		@endif

	@endif

	@if ($mediaItem->description)

		<div class="media-item-description">
			{!! $mediaItem->getRenderedDescription(['previewOnly' => Site::get('mediaList')]) !!}
		</div>

	@endif

	<div class="clear"></div>

</div>