<nav class="navbar navbar-dark navbar-fixed-top bg-inverse" role="navigation">
	<div class="container-fluid">
		<div class="navbar-logo">

			@if (config('cms.logo') && is_string(config('cms.logo')))

				<a class="navbar-brand" id="logo" href="{{ Fractal::url() }}">
					<img src="{{ Fractal::getImageUrlFromConfig('cms.logo') }}" alt="{{{ Site::get('name') }}}" title="{{{ Site::get('name') }}}" id="logo" />
				</a>

			@else

				<a class="navbar-brand" href="{{ Fractal::url() }}">{{ Site::get('name') }}</a>

			@endif

		</div>

		<div id="top-buttons">
			{!! Site::getButtonListMarkup() !!}
		</div>

		<button class="navbar-toggler hidden-sm-up" type="button" data-toggle="collapse" data-target="#navbar-header" aria-controls="navbar-header">&#9776;</button>

		<div class="collapse navbar-toggleable-xs" id="navbar-header">

			{!! Fractal::getMenuMarkup('CMS Account', ['class' => 'nav navbar-nav pull-right']) !!}

			@yield('search')

		</div><!-- /.nav-collapse -->
	</div><!-- /.container-fluid -->
</nav>