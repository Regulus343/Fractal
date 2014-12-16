<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>{{{ Site::title() }}}</title>

	@include(Fractal::view('public.partials.meta', true))

	<link rel="shortcut icon" type="image/x-icon" href="{{ Fractal::getImagePathFromConfig('favicon') }}" />

	@include(Fractal::view('partials.included_files', true))

	<link type="text/css" rel="stylesheet" href="{{ Site::css('fractal/public', 'regulus/fractal') }}" />

	<script type="text/javascript" src="{{ Site::js('fractal/public', 'regulus/fractal') }}"></script>
</head>
<body>
	@include(Fractal::view('partials.modal', true))

	<div class="container" id="container">

		@include(Fractal::view('public.partials.nav', true))

		@include(Fractal::view('partials.messages', true))

		<div id="container-content">
			<div class="container-pad-fluid">

				<div class="row">

					@yield('leftColumn')

					<div class="col-md-{{ Site::get('contentColumnWidth', 12) }}">

						@if (!Site::get('hideTitle'))
							<h1 id="main-heading">{{ Site::titleHeading() }}</h1>

							{{ Site::getBreadcrumbTrailMarkup() }}
						@endif