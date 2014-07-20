<div class="navbar navbar-inverse">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>

		@if (Config::get('fractal::logo') && is_string(Config::get('fractal::logo')))
			<a class="navbar-brand" id="logo" href="{{ Site::get('menus') == "Front" ? URL::to('') : Fractal::url() }}">
				<img src="{{ Site::img(Config::get('fractal::logo'), (Config::get('fractal::logoFractal') ? 'regulus/fractal' : false)) }}" alt="{{{ Site::get('name') }}}" title="{{{ Site::get('name') }}}" id="logo" />
			</a>
		@else
			<a class="navbar-brand" href="{{ Site::get('menus') == "Front" ? URL::to('') : Fractal::url() }}">{{{ Site::get('name') }}}</a>
		@endif
	</div>
	<div class="navbar-collapse collapse">
		@if (Site::get('menus') == "Front")
			{{ Fractal::getMenuMarkup('Main') }}
		@else
			{{ Fractal::getMenuMarkup('CMS Main') }}

			{{ Fractal::getMenuMarkup('CMS Account', false, 'navbar-right') }}
		@endif
	</div><!-- /.nav-collapse -->
</div>