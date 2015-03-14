@if (!empty($latestContent))

	<div id="latest-content" class="content-feed">

		@foreach ($latestContent as $contentItem)

			<div class="content-item-preview">

				<div class="content-item">

					<div class="content-item-heading">

						<h3><a href="{{ $contentItem->url }}">{{ $contentItem->title }}</a></h3>

						<time datetime="{{ $contentItem->published_at }}" class="date-time-published">
							@if ($contentItem->published_at)

								Published {{ date(Fractal::getDateFormat(), strtotime($contentItem->published_at)) }}

							@else

								<span class="not-published">{{ Fractal::trans('labels.notPublished') }}</span>

							@endif
						</time>

						@if ($contentItem->date_created)

							<time datetime="{{ $contentItem->date_created }}" class="date-created">
								Created {{ date(Fractal::getDateFormat(), strtotime($contentItem->date_created)) }}
							</time>

						@endif

					</div>

					<div class="content-item-body">

						@if ($contentItem->type == "Article")

							{{ $contentItem->content }}

						@else

							<div class="row">
								<div class="col-sm-2">
									@if ($contentItem->thumbnail_image_url)
										<a href="{{ $contentItem->url }}" class="thumbnail-image">
											<img src="{{ $contentItem->thumbnail_image_url }}" alt="{{ $contentItem->title }}" title="{{ $contentItem->title }}" />
										</a>
									@endif
								</div>

								<div class="col-sm-10">
									{{ $contentItem->content }}

									<a href="{{ $contentItem->url }}" class="btn btn-default btn-xs btn-read-more">
										{{ Fractal::trans('labels.viewItem') }}
									</a>
								</div>
							</div>

						@endif

					</div>

				</div>

			</div>

		@endforeach

	</div><!-- /#latest-content -->

@endif