@if (!$listItemsOnly)
	<ul class="nav navbar-nav{{ $class }}">
@endif

@foreach ($menu as $menuItem)
	@include(Config::get('fractal::viewsLocation').'partials.menu_item')
@endforeach

@if (!$listItemsOnly)
	</ul>
@endif