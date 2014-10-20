<ul id="select-content-area">
	<li class="new">
		<h3>{{ Lang::get('fractal::labels.createNewContentArea') }}</h3>
	</li>

	@foreach ($contentAreas as $contentArea)

		@if (!in_array((int) $articleId, $contentArea->getArticleIds()))

			<li data-content-area-id="{{ $contentArea->id }}">
				<div class="info">
					@if (!$contentArea->articles()->count())
						<div class="actions">
							<span class="glyphicon glyphicon-remove red delete" title="{{ Fractal::lang('labels.deleteContentArea') }}"></span>
						</div>
					@endif

					{{ $contentArea->articles()->count() }}
					{{ Format::pluralize(Fractal::lang('labels.page'), $contentArea->articles()->count()) }}
				</div>

				<h3>{{ $contentArea->getTitle() }}</h3>
			</li>

		@endif
	@endforeach
</ul>