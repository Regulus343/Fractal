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
		<img src="{{ $mediaItem->getFileUrl() }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" class="media-image" />	
	@endif

	{{ $mediaItem->getRenderedDescription() }}
</div>