@if (isset($mediaTypes) && !empty($mediaTypes))

	<h3>{{ Fractal::lang('labels.mediaTypes') }}</h3>

	<ul class="nav nav-pills nav-stacked nav-side">

		@if (isset($mediaType))

		<li class="special">
			<a href="{{ Fractal::mediaUrl('') }}">
				<span class="glyphicon glyphicon-chevron-left"></span> {{ Lang::get('fractal::labels.allMediaTypes') }}
			</a>
		</li>

		@endif

		@foreach ($mediaTypes as $mediaTypeListed)

			@if ($mediaTypeListed->items()->count())

				<li{{ HTML::activeArea(isset($mediaType) && $mediaTypeListed->id == $mediaType->id) }}>
					<a href="{{ $mediaTypeListed->getUrl() }}">
						<span class="glyphicon glyphicon-tag"></span> {{ $mediaTypeListed->name }}

						<span class="badge primary">{{ $mediaTypeListed->items()->count() }}</span>
					</a>
				</li>

			@endif

		@endforeach

	</ul>

@endif