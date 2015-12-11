<nav class="navbar navbar-dark bg-inverse" role="navigation">
	<div class="container-fluid">
		<div class="navbar-logo">
			@if (config('cms.logo') && is_string(config('cms.logo')))
				<a class="navbar-brand" id="logo" href="{{ URL::to('') }}">
					<img src="{{ Fractal::getImageUrlFromConfig('cms.logo') }}" alt="{{{ Site::get('name') }}}" title="{{{ Site::get('name') }}}" id="logo" />
				</a>
			@else
				<a class="navbar-brand" href="{{ URL::to('') }}">{{ Site::get('name') }}</a>
			@endif
		</div>

		<button class="navbar-toggler hidden-sm-up" type="button" data-toggle="collapse" data-target="#navbar-header" aria-controls="navbar-header">&#9776;</button>

		<div class="collapse navbar-toggleable-xs" id="navbar-header">

			{!! Fractal::getMenuMarkup('Main') !!}

			@if (Auth::is('admin'))

				<ul class="nav navbar-nav navbar-right" id="nav-return-to-cms">
					<li>
						<a href="{{ Fractal::url() }}">
							<i class="fa fa-sign-in"></i>
							<span class="menu-item-label">{{ Fractal::trans('labels.return_to_cms') }}</span>
						</a>
					</li>
				</ul>

			@endif

			@yield('search')

		</div><!-- /.nav-collapse -->
	</div><!-- /.container -->
</nav>