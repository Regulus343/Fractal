<div class="content-item article">

	<div class="content-item-heading article-heading">
		@if (Site::get('articleList'))

			<h3><a href="{{ $article->getUrl() }}">{!! $article->getTitle() !!}</a></h3>

		@endif

		<time datetime="{{ $article->published_at }}" class="date-time-published">
			@if ($article->published_at)

				By {{ $article->author->getName('F L') }}, published {{ $article->getPublishedDate() }}

			@else

				<span class="not-published">{{ Fractal::trans('labels.notPublished') }}</span>

			@endif
		</time>
	</div>

	@if (!Site::get('articleList'))

		@if (Auth::is('admin'))

			<a href="{{ Fractal::url('blogs/articles/'.$article->slug.'/edit') }}" class="btn btn-primary btn-xs pull-right">
				<span class="glyphicon glyphicon-edit"></span>

				{{ Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.article')]) }}
			</a>

		@endif

		@include(Fractal::view('public.partials.share', true))

	@endif

	<div class="content-item-body article-body">

		{!! $article->getRenderedContent(['previewOnly' => Site::get('articleList', false)]) !!}

	</div>

	@if (!Site::get('articleList') && $article->commentsEnabled())

		@include(Fractal::view('public.partials.comments', true))

	@endif

</div>