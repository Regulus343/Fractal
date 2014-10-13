@if (!$listItemsOnly)
	<ul class="{{ $class }}">
@endif

@foreach ($menu as $menuItem)
	@include(Fractal::view('partials.menu_item', true))
@endforeach

@if (!$listItemsOnly)
	</ul>
@endif