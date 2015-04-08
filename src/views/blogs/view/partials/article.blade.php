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

		@if ($article->sticky)

			<span class="badge badge-primary badge-sticky" title="{{ Fractal::trans('labels.sticky') }}">
				<i class="fa fa-thumb-tack"></i>
			</span>

		@endif
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

		{!! $article->getRenderedContent(['previewOnly' => Site::get('articleList')]) !!}

	</div>

	@if (!Site::get('articleList'))

		@if ($article->getPreviousItem() || $article->getNextItem())

			<div class="nav-previous-next-content">

				@if ($article->getPreviousItem())

					<a href="{{ Fractal::blogUrl($article->getPreviousItem('slug')) }}" class="btn btn-primary btn-sm btn-previous">
						<i class="fa fa-arrow-left"></i>

						{!! $article->getPreviousItem('title') !!}
					</a>

				@endif

				@if ($article->getNextItem())

					<a href="{{ Fractal::blogUrl($article->getNextItem('slug')) }}" class="btn btn-primary btn-sm btn-next">
						 {!! $article->getNextItem('title') !!}

						 <i class="fa fa-arrow-right"></i>
					</a>

				@endif

			</div><!-- /.nav-previous-next-content -->

		@endif

		@if ($article->commentsEnabled())

			@include(Fractal::view('public.partials.comments', true))

		@endif

	@endif

</div>