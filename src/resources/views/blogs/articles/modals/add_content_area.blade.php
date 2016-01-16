<ul id="select-content-area">
	<li class="new">
		<h3>{{ Fractal::trans('labels.create_new_item', ['item' => Fractal::transChoice('labels.content_area')]) }}</h3>
	</li>

	@foreach ($contentAreas as $contentArea)

		@if (!in_array((int) $articleId, $contentArea->getArticleIds()))

			<li data-content-area-id="{{ $contentArea->id }}">
				<div class="info">
					@if (!$contentArea->articles()->count())
						<div class="actions">
							<span class="glyphicon glyphicon-remove red delete" title="{{ Fractal::trans('messages.delete_item', ['item' => Fractal::trans('labels.content_area')]) }}"></span>
						</div>
					@endif

					{{ $contentArea->articles()->count() }}
					{{ Fractal::transChoice('labels.article', $contentArea->articles()->count()) }}
				</div>

				<h3>{{ $contentArea->getTitle() }}</h3>
			</li>

		@endif
	@endforeach
</ul>