@if (isset($mediaTypes) && Regulus\Fractal\Models\Media\Type::publishedItemInTypes($mediaTypes) && Fractal::getSetting('Display Media Types Menu', true))

	<h3>{{ Fractal::trans('labels.mediaTypes') }}</h3>

	<ul class="nav nav-pills nav-stacked nav-side">

		@if (isset($mediaType))

			<li class="special">
				<a href="{{ Fractal::mediaUrl('') }}">
					<i class="fa fa-chevron-left"></i> {{ Fractal::trans('labels.all_items', ['items' => Fractal::transChoice('labels.media_type', 2)]) }}
				</a>
			</li>

		@endif

		@foreach ($mediaTypes as $mediaTypeListed)

			@if ($mediaTypeListed->items()->onlyPublished()->count())

				<li{{ HTML::activeArea(isset($mediaType) && $mediaTypeListed->id == $mediaType->id) }}>
					<a href="{{ $mediaTypeListed->getUrl() }}">
						<i class="fa fa-tag"></i> {{ $mediaTypeListed->name }}

						<span class="badge primary">{{ $mediaTypeListed->items()->onlyPublished()->count() }}</span>
					</a>
				</li>

			@endif

		@endforeach

	</ul>

@endif