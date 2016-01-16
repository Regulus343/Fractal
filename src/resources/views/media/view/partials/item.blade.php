<div class="content-item media-item">

	<div class="content-item-heading media-item-heading">
		@if (Site::get('mediaList'))

			<h3><a href="{{ $mediaItem->getUrl() }}">{!! $mediaItem->getTitle() !!}</a></h3>

		@endif

		<time datetime="{{ $mediaItem->published_at }}" class="date-time-published">
			@if ($mediaItem->published_at)

				Published {{ $mediaItem->getPublishedDate() }}

			@else

				<span class="not-published">{{ Fractal::trans('labels.not_published') }}</span>

			@endif
		</time>

		@if ($mediaItem->date_created)

			<time datetime="{{ $mediaItem->date_created }}" class="date-created">
				Created {{ $mediaItem->getCreatedDate() }}
			</time>

		@endif

		@if ($mediaItem->sticky)

			<span class="badge badge-primary badge-sticky" title="{{ Fractal::trans('labels.sticky') }}">
				<i class="fa fa-thumb-tack"></i>
			</span>

		@endif
	</div>

	@if (!Site::get('mediaList'))

		@if (Auth::is('admin'))

			<a href="{{ Fractal::url('media/items/'.$mediaItem->slug.'/edit') }}" class="btn btn-primary btn-xs pull-right">
				<span class="glyphicon glyphicon-edit"></span>

				{{ Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.media_item')]) }}
			</a>

		@endif

		@include(Fractal::view('public.partials.share', true))

	@endif

	{!! $mediaItem->getContent() !!}

	@if (!Site::get('mediaList') && $mediaItem->commentsEnabled())

		@include(Fractal::view('public.partials.comments', true))

	@endif

</div>