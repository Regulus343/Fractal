@if (isset($mediaSets) && !empty($mediaSets))

	<h3>{{ Fractal::lang('labels.mediaSets') }}</h3>

	<ul class="nav nav-pills nav-stacked nav-side">

		@if (isset($mediaSet))

		<li class="special">
			<a href="{{ Fractal::mediaUrl('') }}">
				<span class="glyphicon glyphicon-chevron-left"></span> {{ Lang::get('fractal::labels.allMediaSets') }}
			</a>
		</li>

		@endif

		@foreach ($mediaSets as $mediaSetListed)

			@if ($mediaSetListed->items()->count())

				<li{{ HTML::activeArea(isset($mediaSet) && $mediaSetListed->id == $mediaSet->id) }}>
					<a href="{{ $mediaSetListed->getUrl() }}">
						<span class="glyphicon glyphicon-tag"></span> {{ $mediaSetListed->title }}

						<span class="badge primary">{{ $mediaSetListed->items()->count() }}</span>
					</a>
				</li>

			@endif

		@endforeach

	</ul>

@endif