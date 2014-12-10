@if (isset($mediaTypes) && Regulus\Fractal\Models\Media\Type::publishedItemInTypes($mediaTypes) && Fractal::getSetting('Display Media Types Menu', true))

	<h3>{{ Fractal::lang('labels.mediaTypes') }}</h3>

	<ul class="nav nav-pills nav-stacked nav-side">

		@if (isset($mediaType))

		<li class="special">
			<a href="{{ Fractal::mediaUrl('') }}">
				<span class="glyphicon glyphicon-chevron-left"></span> {{ Fractal::lang('labels.allMediaTypes') }}
			</a>
		</li>

		@endif

		@foreach ($mediaTypes as $mediaTypeListed)

			@if ($mediaTypeListed->items()->onlyPublished()->count())

				<li{{ HTML::activeArea(isset($mediaType) && $mediaTypeListed->id == $mediaType->id) }}>
					<a href="{{ $mediaTypeListed->getUrl() }}">
						<span class="glyphicon glyphicon-tag"></span> {{ $mediaTypeListed->name }}

						<span class="badge primary">{{ $mediaTypeListed->items()->onlyPublished()->count() }}</span>
					</a>
				</li>

			@endif

		@endforeach

	</ul>

@endif