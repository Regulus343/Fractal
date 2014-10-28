<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container">
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

		<div id="top-buttons">
			{{ Site::getButtonListMarkup() }}
		</div>

		<div class="navbar-collapse collapse">

			{{ Fractal::getMenuMarkup('Main') }}

			@yield('search')

		</div><!-- /.nav-collapse -->
	</div><!-- /.container -->
</nav>