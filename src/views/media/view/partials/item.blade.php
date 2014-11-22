<div class="media-item-heading">
	@if (Site::get('mediaList'))
		<h3><a href="{{ $mediaItem->getUrl() }}">{{ $mediaItem->title }}</a></h3>
	@endif

	<time datetime="{{ $mediaItem->published_at }}" class="date-time-published">
		Published {{ $mediaItem->getPublishedDate() }}
	</time>

	@if ($mediaItem->date_created)

		<time datetime="{{ $mediaItem->date_created }}" class="date-created">
			Created {{ $mediaItem->getCreatedDate() }}
		</time>

	@endif
</div>

<div class="media-item-body">

	@if ($mediaItem->hostedExternally('YouTube'))

		<iframe class="video youtube" src="http://www.youtube.com/embed/{{ $mediaItem->hosted_content_uri }}" frameborder="0" allowfullscreen></iframe>

	@elseif ($mediaItem->hostedExternally('Vimeo'))

		<iframe class="video vimeo" src="http://player.vimeo.com/video/{{ $mediaItem->hosted_content_uri }}?title=0&amp;byline=0&amp;portrait=0" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>

	@elseif ($mediaItem->hostedExternally('SoundCloud'))

		<iframe class="audio soundcloud" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/{{ $mediaItem->hosted_content_uri }}&amp;color=ff5500&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false"></iframe>

	@elseif ($mediaItem->getFileType() == "Image")

		<img src="{{ $mediaItem->getImageUrl() }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" class="media-image" />

	@else

		@if ($mediaItem->thumbnail)
			<img src="{{ $mediaItem->getImageUrl(true) }}" alt="{{ $mediaItem->title }}" title="{{ $mediaItem->title }}" class="thumbnail-image" />
		@endif

		<div class="media-item-area">

			@if ($mediaItem->getFileType() == "Audio")

				<audio src="{{ $mediaItem->getFileUrl() }}"{{ (!Site::get('mediaList') && !Site::get('articleList') ? ' preload="auto"' : '') }}></audio>

			@endif

			<a href="{{ $mediaItem->getFileUrl() }}" class="btn btn-primary" target="_blank">Download {{ $mediaItem->title }}</a>

		</div>

	@endif

	@if ($mediaItem->description)

		<div class="media-item-description">
			{{ $mediaItem->getRenderedDescription() }}
		</div>

	@endif

	<div class="clear"></div>
</div>