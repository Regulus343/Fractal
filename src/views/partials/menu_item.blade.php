<?php if (is_array($menuItem)) $menuItem = (object) $menuItem; ?>

@if (Fractal::isMenuItemVisible($menuItem))
	<li class="{{ Fractal::setMenuItemSelectedClass($menuItem) }}">
		<a href="{{ URL::to($menuItem->url) }}" class="{{ $menuItem->anchorClass }}"{{ !empty($menuItem->children) ? ' data-toggle="dropdown"' : '' }}>
			{{ $menuItem->labelIcon }}

			@if (!empty($menuItem->children))
				<b class="caret"></b>
			@endif
		</a>

		@if (!empty($menuItem->children))
			<ul class="dropdown-menu">
				@include(Fractal::view('partials.menu', true), array('menu' => $menuItem->children, 'listItemsOnly' => true))
			</ul>
		@endif
	</li>
@endif