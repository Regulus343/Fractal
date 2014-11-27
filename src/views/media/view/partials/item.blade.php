<div class="media-item">

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

	{{ $mediaItem->getContent() }}

</div>