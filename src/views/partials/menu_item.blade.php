<?php if (is_array($menuItem)) $menuItem = (object) $menuItem; ?>

@if (Fractal::isMenuItemVisible($menuItem))
	<li class="{{ Fractal::setMenuItemSelectedClass($menuItem) }}">
		<a href="{{ URL::to($menuItem->url) }}" class="{{ $menuItem->anchorClass }}"
		{{ (!empty($menuItem->children) && $actionSubMenuDropDown ? ' data-toggle="dropdown"' : '') }}>
			{{ $menuItem->icon }}

			<span class="menu-item-label">{{ $menuItem->label }}</span>

			@if (!empty($menuItem->children))
				<b class="caret"></b>
			@endif
		</a>

		@if (!empty($menuItem->children))
			<ul{{ ($actionSubMenuDropDown ? ' class="dropdown-menu"' : '') }}>
				@include(Fractal::view('partials.menu', true), array('menu' => $menuItem->children, 'listItemsOnly' => true))
			</ul>
		@endif
	</li>
@endif