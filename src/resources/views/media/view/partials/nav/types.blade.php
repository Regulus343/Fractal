@if (isset($mediaTypes) && Regulus\Fractal\Models\Media\Type::publishedItemInTypes($mediaTypes) && Fractal::getSetting('Display Media Types Menu', true))

	<h3>{{ Fractal::transChoice('labels.media_type', 2) }}</h3>

	<ul class="nav nav-pills nav-stacked nav-side">

		@if (isset($mediaType))

			<li class="special nav-item">
				<a href="{{ Fractal::mediaUrl() }}" class="nav-link">
					<i class="fa fa-chevron-left"></i> {{ Fractal::trans('labels.all_items', ['items' => Fractal::transChoice('labels.media_type', 2)]) }}
				</a>
			</li>

		@endif

		@foreach ($mediaTypes as $mediaTypeListed)

			@if ($mediaTypeListed->items()->onlyPublished(false)->count())

				<li class="nav-item{{ HTML::activeArea(isset($mediaType) && $mediaTypeListed->id == $mediaType->id, true) }}">
					<a href="{{ $mediaTypeListed->getUrl() }}" class="nav-link">
						<i class="fa fa-tag"></i> {{ $mediaTypeListed->name }}

						<span class="label label-pill label-primary label-sticky">{{ $mediaTypeListed->items()->onlyPublished()->count() }}</span>
					</a>
				</li>

			@endif

		@endforeach

	</ul>

@endif