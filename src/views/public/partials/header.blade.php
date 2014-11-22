<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>{{{ Site::title() }}}</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">

	@include(Fractal::view('partials.included_files', true))
</head>
<body>
	@include(Fractal::view('partials.modal', true))

	<div class="container" id="container">

		@include(Fractal::view('public.partials.nav', true))

		@include(Fractal::view('partials.messages', true))

		<div id="container-content">
			<div class="container-pad-vertical">

				<div class="row">

					@yield('leftColumn')

					<div class="col-md-{{ Site::get('contentColumnWidth', 12) }}">

						@if (!Site::get('hideTitle'))
							<h1 id="main-heading">{{ Site::titleHeading() }}</h1>

							{{ Site::getBreadcrumbTrailMarkup() }}
						@endif