@if (isset($mediaSets) && Regulus\Fractal\Models\Media\Set::publishedItemInSets($mediaSets) && Fractal::getSetting('Display Media Sets Menu', true))

	<h3>{{ Fractal::transChoice('labels.media_set', 2) }}</h3>

	<ul class="nav nav-pills nav-stacked nav-side">

		@if (isset($mediaSet))

			<li class="special nav-item">
				<a href="{{ Fractal::mediaUrl() }}" class="nav-link">
					<i class="fa fa-chevron-left"></i> {{ Fractal::trans('labels.all_items', ['items' => Fractal::transChoice('labels.media_set', 2)]) }}
				</a>
			</li>

		@endif

		@foreach ($mediaSets as $mediaSetListed)

			@if ($mediaSetListed->items()->onlyPublished(false)->count())

				<li class="nav-item{{ HTML::activeArea(isset($mediaSet) && $mediaSetListed->id == $mediaSet->id, true) }}">
					<a href="{{ $mediaSetListed->getUrl() }}" class="nav-link">
						<span class="fa fa-th"></span> {{ $mediaSetListed->title }}

						<span class="label label-pill label-primary label-sticky">{{ $mediaSetListed->items()->onlyPublished()->count() }}</span>
					</a>
				</li>

			@endif

		@endforeach

	</ul>

@endif