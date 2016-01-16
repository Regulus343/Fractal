<ul class="nav nav-pills nav-stacked nav-side">

	<li class="special nav-item">
		<a href="{{ Fractal::blogUrl() }}" class="nav-link">
			<i class="fa fa-chevron-left"></i> {{ Fractal::trans('labels.blog_return_to_home') }}
		</a>
	</li>

	@foreach ($articles as $articleListed)

		<li class="nav-item{{ HTML::activeArea(isset($article->id) && $articleListed->id == $article->id, true) }}">
			<a href="{{ $articleListed->getUrl() }}" class="nav-link">
				<i class="fa fa-file"></i> {{ $articleListed->getTitle() }}

				@if ($articleListed->published_at)

					<div>
						<time datetime="{{ $articleListed->published_at }}">{{ $articleListed->getPublishedDate() }}</time>

						@if ($articleListed->sticky)

							<span class="label label-pill label-primary label-sticky" title="{{ Fractal::trans('labels.sticky') }}">
								<i class="fa fa-thumb-tack"></i>
							</span>

						@endif
					</div>

				@endif
			</a>
		</li>

	@endforeach

</ul>