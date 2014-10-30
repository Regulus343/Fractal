<ul class="nav nav-pills nav-stacked nav-side">

	<li class="special">
		<a href="{{ Fractal::mediaUrl('') }}">
			<span class="glyphicon glyphicon-chevron-left"></span> {{ Lang::get('fractal::labels.mediaReturnToHome') }}
		</a>
	</li>

	@foreach ($media as $mediaItemListed)

		<li{{ HTML::activeArea(isset($mediaItem->id) && $mediaItemListed->id == $mediaItem->id) }}>
			<a href="{{ $mediaItemListed->getUrl() }}">
				{{ $mediaItemListed->title }}

				<div>
					<time datetime="{{ $mediaItemListed->published_at }}">{{ $mediaItemListed->getPublishedDateTime() }}</time>
				</div>
			</a>
		</li>

	@endforeach

</ul>