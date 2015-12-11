<ul class="nav nav-pills nav-stacked nav-side">

	<li class="special nav-item">
		<a href="{{ Fractal::mediaUrl() }}" class="nav-link">
			<i class="fa fa-chevron-left"></i> {{ Fractal::trans('labels.media_return_to_home') }}
		</a>
	</li>

	@foreach ($mediaItems as $mediaItemListed)

		<li class="nav-item{{ HTML::activeArea(isset($mediaItem->id) && $mediaItemListed->id == $mediaItem->id, true) }}">
			<a href="{{ $mediaItemListed->getUrl() }}" class="nav-link">
				<i class="fa fa-file"></i> {{ $mediaItemListed->getTitle() }}

				@if ($mediaItemListed->published_at)

					<div>
						<time datetime="{{ $mediaItemListed->published_at }}">{{ $mediaItemListed->getPublishedDate() }}</time>

						@if ($mediaItemListed->sticky)

							<span class="label label-pill label-primary label-sticky" title="{{ Fractal::trans('labels.sticky') }}">
								<i class="fa fa-thumb-tack"></i>
							</span>

						@endif
					</div>

				@endif
			</a>
		</li>

	@endforeach

</ul>