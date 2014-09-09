<ul class="nav nav-pills nav-stacked">
	<li class="danger">
		<a href="{{ Fractal::mediaUrl('') }}">
			<span class="glyphicon glyphicon-chevron-left"></span> {{ Lang::get('fractal::labels.mediaReturnToHome') }}
		</a>
	</li>

	@foreach ($media as $mediaItemListed)

		<li{{ HTML::activeArea(isset($article->id) && $mediaItemListed->id == $article->id) }}>
			<a href="{{ $mediaItemListed->getUrl() }}">
				{{ $mediaItemListed->title }}

				<div>
					<time datetime="{{ $mediaItemListed->published_at }}">{{ $mediaItemListed->getPublishedDateTime() }}</time>
				</div>
			</a>
		</li>

	@endforeach
</ul>