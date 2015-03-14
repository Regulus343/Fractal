<ul class="nav nav-pills nav-stacked nav-side">

	<li class="special">
		<a href="{{ Fractal::mediaUrl('') }}">
			<span class="glyphicon glyphicon-chevron-left"></span> {{ Fractal::trans('labels.media_return_to_home') }}
		</a>
	</li>

	@foreach ($mediaItems as $mediaItemListed)

		<li{{ HTML::activeArea(isset($mediaItem->id) && $mediaItemListed->id == $mediaItem->id) }}>
			<a href="{{ $mediaItemListed->getUrl() }}">
				<span class="glyphicon glyphicon-file"></span> {{ $mediaItemListed->getTitle() }}

				@if ($mediaItemListed->published_at)
					<div>
						<time datetime="{{ $mediaItemListed->published_at }}">{{ $mediaItemListed->getPublishedDate() }}</time>
					</div>
				@endif
			</a>
		</li>

	@endforeach

</ul>