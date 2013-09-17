<div class="navbar navbar-inverse">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" href="{{ Fractal::url() }}">{{{ Site::get('name') }}}</a>
	</div>
	<div class="navbar-collapse collapse">
		{{ Fractal::getMenuMarkup('CMS Main') }}

		{{ Fractal::getMenuMarkup('CMS Account', false, 'navbar-right') }}
	</div><!-- /.nav-collapse -->
</div>