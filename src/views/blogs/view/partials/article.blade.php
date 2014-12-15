<div class="article">

	<div class="article-heading">
		@if (Site::get('articleList'))

			<h3><a href="{{ $article->getUrl() }}">{{ $article->getTitle() }}</a></h3>

		@endif

		<time datetime="{{ $article->published_at }}" class="date-time-published">
			Published {{ $article->getPublishedDate() }}
		</time>
	</div>

	@if (!Site::get('articleList'))

		@include(Fractal::view('public.partials.share', true))

	@endif

	<div class="article-body">

		{{ $article->getRenderedContent(Site::get('articleList')) }}

	</div>

	@if (!Site::get('articleList') && Fractal::getSetting('Enable Article Comments', false))

		@include(Fractal::view('public.partials.comments', true))

	@endif

</div>