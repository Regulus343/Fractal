<div class="article-heading">
	@if (Site::get('articleList'))
		<h3><a href="{{ $article->getUrl() }}">{{ $article->title }}</a></h3>
	@endif

	<time datetime="{{ $article->published_at }}" class="date-time-published">
		Published {{ $article->getPublishedDate() }}
	</time>
</div>

<div class="article-body">

	{{ $article->getRenderedContent(Site::get('articleList')) }}

</div>