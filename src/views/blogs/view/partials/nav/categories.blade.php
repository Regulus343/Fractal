@if (isset($categories) && Regulus\Fractal\Models\Blog\Category::publishedArticleInCategories($categories))

	<h3>{{ Fractal::transChoice('labels.category', 2) }}</h3>

	<ul class="nav nav-pills nav-stacked nav-side">

		@if (isset($category))

			<li class="special">
				<a href="{{ Fractal::blogUrl() }}">
					<i class="fa fa-chevron-left"></i> {{ Fractal::trans('labels.all_items', ['items' => Fractal::transChoice('labels.category', 2)]) }}
				</a>
			</li>

		@endif

		@foreach ($categories as $categoryListed)

			@if ($categoryListed->articles()->onlyPublished(false)->count())

				<li{!! HTML::activeArea(isset($category) && $categoryListed->id == $category->id) !!}>
					<a href="{{ $categoryListed->getUrl() }}">
						<i class="fa fa-tag"></i> {{ $categoryListed->name }}

						<span class="badge primary">{{ $categoryListed->articles()->onlyPublished()->count() }}</span>
					</a>
				</li>

			@endif

		@endforeach

	</ul>

@endif