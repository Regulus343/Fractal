@if (!Site::get('hideSidebar'))

	<div id="nav-side"{{ Auth::checkState('sidebarOpen', false) ? ' class="collapsed"' : '' }}>

		<div id="nav-side-toggle"></div>

		{!! Fractal::getMenuMarkup('CMS Main', ['class' => 'nav nav-stacked', 'actionSubMenuDropDown' => false]) !!}

	</div><!-- /#nav-side -->

@endif