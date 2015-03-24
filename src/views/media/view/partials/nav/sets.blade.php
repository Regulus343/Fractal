@if (isset($mediaSets) && Regulus\Fractal\Models\Media\Set::publishedItemInSets($mediaSets) && Fractal::getSetting('Display Media Sets Menu', true))

	<h3>{{ Fractal::trans('labels.mediaSets') }}</h3>

	<ul class="nav nav-pills nav-stacked nav-side">

		@if (isset($mediaSet))

			<li class="special">
				<a href="{{ Fractal::mediaUrl('') }}">
					<i class="fa fa-chevron-left"></i> {{ Fractal::trans('labels.all_items', ['items' => Fractal::transChoice('labels.media_set', 2)]) }}
				</a>
			</li>

		@endif

		@foreach ($mediaSets as $mediaSetListed)

			@if ($mediaSetListed->items()->onlyPublished()->count())

				<li{{ HTML::activeArea(isset($mediaSet) && $mediaSetListed->id == $mediaSet->id) }}>
					<a href="{{ $mediaSetListed->getUrl() }}">
						<span class="fa fa-th"></span> {{ $mediaSetListed->title }}

						<span class="badge primary">{{ $mediaSetListed->items()->onlyPublished()->count() }}</span>
					</a>
				</li>

			@endif

		@endforeach

	</ul>

@endif