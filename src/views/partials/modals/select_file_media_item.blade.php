<ul class="image-list image-list-large select-{{ ($type == "File" ? 'file' : 'media-item') }}" id="select-{{ ($type == "File" ? 'file' : 'media-item') }}">
	@foreach ($items as $item)

		@if ($type == "File")

			<li data-file-id="{{ $item->id }}">
				<img src="{{ $item->getImageUrl(true) }}" alt="{{ $item->name }}" title="{{ $item->name }}" />
				<h4>{{ $item->name }}</h4>
			</li>

		@else

			<li data-media-item-id="{{ $item->id }}">
				<img src="{{ $item->getImageUrl(true) }}" alt="{{ $item->title }}" title="{{ $item->title }}" />
				<h4>{{ $item->title }}</h4>
			</li>

		@endif

	@endforeach
</ul>