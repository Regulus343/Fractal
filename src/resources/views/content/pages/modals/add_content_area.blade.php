<ul id="select-content-area">
	<li class="new">
		<h3>{{ Fractal::trans('labels.create_new_item', ['item' => Fractal::transChoice('labels.content_area')]) }}</h3>
	</li>

	@foreach ($contentAreas as $contentArea)

		@if (!in_array((int) $pageId, $contentArea->getPageIds()))

			<li data-content-area-id="{{ $contentArea->id }}">
				<div class="info">
					@if (!$contentArea->pages()->count())
						<div class="actions">
							<span class="glyphicon glyphicon-remove red delete" title="{{ Fractal::trans('messages.delete_item', ['item' => Fractal::trans('labels.content_area')]) }}"></span>
						</div>
					@endif

					{{ $contentArea->pages()->count() }}
					{{ Fractal::transChoice('labels.page', $contentArea->pages()->count()) }}
				</div>

				<h3>{{ $contentArea->getTitle() }}</h3>
			</li>

		@endif
	@endforeach
</ul>