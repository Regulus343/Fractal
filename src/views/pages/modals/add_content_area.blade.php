<ul id="select-content-area">
	<li class="new">
		<h3>{{ Lang::get('fractal::labels.createNewContentArea') }}</h3>
	</li>

	@foreach ($contentAreas as $contentArea)

		@if (!in_array((int) $pageId, $contentArea->getContentPageIds()))

			<li data-content-area-id="{{ $contentArea->id }}">
				<div class="info">
					@if ($contentArea->contentPages()->count())
						<div class="actions">
							<span class="glyphicon glyphicon-remove red"></span>
						</div>
					@endif

					{{ $contentArea->contentPages()->count() }}
					{{ Format::pluralize(Lang::get('fractal::labels.page'), $contentArea->contentPages()->count()) }}
				</div>

				<h3>{{ $contentArea->title }}</h3>
			</li>

		@endif
	@endforeach
</ul>