@if (isset($categories) && Regulus\Fractal\Models\Blog\Category::publishedArticleInCategories($categories))

	<h3>{{ Fractal::transChoice('labels.category', 2) }}</h3>

	<ul class="nav nav-pills nav-stacked nav-side">

		@if (isset($category))

			<li class="special nav-item">
				<a href="{{ Fractal::blogUrl() }}" class="nav-link">
					<i class="fa fa-chevron-left"></i> {{ Fractal::trans('labels.all_items', ['items' => Fractal::transChoice('labels.category', 2)]) }}
				</a>
			</li>

		@endif

		@foreach ($categories as $categoryListed)

			@if ($categoryListed->articles()->onlyPublished(false)->count())

				<li class="nav-item{{ HTML::activeArea(isset($category) && $categoryListed->id == $category->id, true) }}">
					<a href="{{ $categoryListed->getUrl() }}" class="nav-link">
						<i class="fa fa-tag"></i> {{ $categoryListed->name }}

						<span class="label label-pill label-primary label-sticky">{{ $categoryListed->articles()->onlyPublished()->count() }}</span>
					</a>
				</li>

			@endif

		@endforeach

	</ul>

@endif