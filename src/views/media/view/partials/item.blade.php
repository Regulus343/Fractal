<div class="media-item">

	<div class="media-item-heading">
		@if (Site::get('mediaList'))

			<h3><a href="{{ $mediaItem->getUrl() }}">{{ $mediaItem->getTitle() }}</a></h3>

		@endif

		<time datetime="{{ $mediaItem->published_at }}" class="date-time-published">
			Published {{ $mediaItem->getPublishedDate() }}
		</time>

		@if ($mediaItem->date_created)

			<time datetime="{{ $mediaItem->date_created }}" class="date-created">
				Created {{ $mediaItem->getCreatedDate() }}
			</time>

		@endif
	</div>

	@if (!Site::get('mediaList'))

		@if (Auth::is('admin'))

			<a href="{{ Fractal::url('media/items/'.$mediaItem->slug.'/edit') }}" class="btn btn-primary btn-xs pull-right">
				<span class="glyphicon glyphicon-edit"></span>

				{{ Fractal::lang('labels.editItem') }}
			</a>

		@endif

		@include(Fractal::view('public.partials.share', true))

	@endif

	{{ $mediaItem->getContent() }}

	@if (!Site::get('mediaList') && Fractal::getSetting('Enable Media Item Comments', false))

		@include(Fractal::view('public.partials.comments', true))

	@endif

</div>