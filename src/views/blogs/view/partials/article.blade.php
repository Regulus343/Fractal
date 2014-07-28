<div class="article-heading">
	@if (Site::get('articleList'))
		<h1><a href="{{ $article->getUrl() }}">{{ $article->title }}</a></h1>
	@else
		<h1>{{ $article->title }}</h1>
	@endif

	<time datetime="{{ $article->published_at }}">{{ $article->getPublishedDateTime() }}</time>
</div>

<div class="article-body">
	{{ $article->getRenderedContent(Site::get('articleList')) }}
</div>