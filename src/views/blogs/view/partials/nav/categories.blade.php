@if (isset($categories) && !empty($categories))

	<h3>Categories</h3>

	<ul class="nav nav-pills nav-stacked nav-side">

		@if (isset($category))

		<li class="special">
			<a href="{{ Fractal::blogUrl('') }}">
				<span class="glyphicon glyphicon-chevron-left"></span> {{ Lang::get('fractal::labels.allCategories') }}
			</a>
		</li>

		@endif

		@foreach ($categories as $categoryListed)

			@if ($categoryListed->articles()->count())

				<li{{ HTML::activeArea(isset($category) && $categoryListed->id == $category->id) }}>
					<a href="{{ $categoryListed->getUrl() }}">
						<span class="glyphicon glyphicon-tag"></span> {{ $categoryListed->name }}

						<span class="badge primary">{{ $categoryListed->articles()->count() }}</span>
					</a>
				</li>

			@endif

		@endforeach

	</ul>

@endif