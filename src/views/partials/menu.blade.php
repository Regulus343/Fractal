@if (!$listItemsOnly)
	<ul class="nav navbar-nav{{ $class }}">
@endif

@foreach ($menu as $menuItem)
	@include(Fractal::view('partials.menu_item', true))
@endforeach

@if (!$listItemsOnly)
	</ul>
@endif