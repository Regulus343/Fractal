@if (isset($mediaSets) && Regulus\Fractal\Models\Media\Set::publishedItemInSets($mediaSets) && Fractal::getSetting('Display Media Sets Menu', true))

	<h3>{{ Fractal::lang('labels.mediaSets') }}</h3>

	<ul class="nav nav-pills nav-stacked nav-side">

		@if (isset($mediaSet))

		<li class="special">
			<a href="{{ Fractal::mediaUrl('') }}">
				<span class="glyphicon glyphicon-chevron-left"></span> {{ Fractal::lang('labels.allMediaSets') }}
			</a>
		</li>

		@endif

		@foreach ($mediaSets as $mediaSetListed)

			@if ($mediaSetListed->items()->onlyPublished()->count())

				<li{{ HTML::activeArea(isset($mediaSet) && $mediaSetListed->id == $mediaSet->id) }}>
					<a href="{{ $mediaSetListed->getUrl() }}">
						<span class="glyphicon glyphicon-th"></span> {{ $mediaSetListed->title }}

						<span class="badge primary">{{ $mediaSetListed->items()->onlyPublished()->count() }}</span>
					</a>
				</li>

			@endif

		@endforeach

	</ul>

@endif