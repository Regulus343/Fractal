<div class="article-heading">
	@if (Site::get('mediaList'))
		<h3><a href="{{ $mediaItem->getUrl() }}">{{ $mediaItem->title }}</a></h3>
	@endif

	<time datetime="{{ $mediaItem->published_at }}">Published {{ $mediaItem->getPublishedDateTime() }}</time>
</div>

<div class="article-body">
	@if ($mediaItem->hostedExternally('YouTube'))

		<iframe width="560" height="315" src="//www.youtube.com/embed/{{ $mediaItem->hosted_content_uri }}" frameborder="0" allowfullscreen></iframe>

	@elseif ($mediaItem->getFileType() == "Image")

		<img src="{{ $mediaItem->getImageUrl() }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" class="media-image" />

	@else

		@if ($mediaItem->thumbnail)
			<img src="{{ $mediaItem->getImageUrl(true) }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" class="media-image" />
		@endif

		<a href="{{ $mediaItem->getFileUrl() }}" class="btn btn-primary" target="_blank">Download {{ $mediaItem->title }}</a>

	@endif

	{{ $mediaItem->getRenderedDescription() }}
</div>