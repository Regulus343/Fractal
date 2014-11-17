<ul class="nav nav-pills nav-stacked nav-side">

	<li class="special">
		<a href="{{ Fractal::blogUrl('') }}">
			<span class="glyphicon glyphicon-chevron-left"></span> {{ Lang::get('fractal::labels.blogReturnToHome') }}
		</a>
	</li>

	@foreach ($articles as $articleListed)

		<li{{ HTML::activeArea(isset($article->id) && $articleListed->id == $article->id) }}>
			<a href="{{ $articleListed->getUrl() }}">
				<span class="glyphicon glyphicon-file"></span> {{ $articleListed->title }}

				<div>
					<time datetime="{{ $articleListed->published_at }}">{{ $articleListed->getPublishedDateTime() }}</time>
				</div>
			</a>
		</li>

	@endforeach

</ul>