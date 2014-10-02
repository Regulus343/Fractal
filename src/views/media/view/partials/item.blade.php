<div class="article-heading">
	@if (Site::get('mediaList'))
		<h1><a href="{{ $mediaItem->getUrl() }}">{{ $mediaItem->title }}</a></h1>
	@else
		<h1>{{ $mediaItem->title }}</h1>
	@endif

	<time datetime="{{ $mediaItem->published_at }}">{{ $mediaItem->getPublishedDateTime() }}</time>
</div>

<div class="article-body">
	@if ($mediaItem->getFileType() == "Image")
		<img src="{{ $mediaItem->getImageUrl() }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" class="media-image" />	
	@else
		@if ($mediaItem->thumbnail)
			<img src="{{ $mediaItem->getImageUrl(true) }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" class="media-image" />	
		@endif

		<a href="{{ $mediaItem->getFileUrl() }}" class="btn btn-primary" target="_blank">Download {{ $mediaItem->title }}</a>
	@endif

	{{ $mediaItem->getRenderedDescription() }}
</div>