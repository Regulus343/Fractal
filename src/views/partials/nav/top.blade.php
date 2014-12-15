<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>

			@if (Config::get('fractal::logo') && is_string(Config::get('fractal::logo')))
				<a class="navbar-brand" id="logo" href="{{ Fractal::url() }}">
					<img src="{{ Fractal::getImagePathFromConfig('logo') }}" alt="{{{ Site::get('name') }}}" title="{{{ Site::get('name') }}}" id="logo" />
				</a>
			@else
				<a class="navbar-brand" href="{{ Fractal::url() }}">{{{ Site::get('name') }}}</a>
			@endif
		</div>

		<div id="top-buttons">
			{{ Site::getButtonListMarkup() }}
		</div>

		<div class="navbar-collapse collapse">

			{{ Fractal::getMenuMarkup('CMS Account', ['class' => 'nav navbar-nav navbar-right']) }}

			@yield('search')

		</div><!-- /.nav-collapse -->
	</div><!-- /.container-fluid -->
</nav>