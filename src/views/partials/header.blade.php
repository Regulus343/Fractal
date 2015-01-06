<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>{{{ Site::title() }}}</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="shortcut icon" type="image/x-icon" href="{{ Fractal::getImageUrlFromConfig('favicon') }}" />

	@include(Fractal::view('partials.included_files', true))
</head>
<body>
	@include(Fractal::view('partials.modal', true))

	<div class="container-full" id="container">

		@include(Fractal::view('partials.nav.top', true))

		@include(Fractal::view('partials.nav.side', true))

		@include(Fractal::view('partials.messages', true))

		<div class="container-full{{ (!Site::get('hideSidebar') && Auth::checkState('sidebarOpen', true, true) ? ' sidebar-offset' : '') }}" id="container-content">
			<div class="container-pad">
				@if (!Site::get('hideTitle'))
					<h1 id="main-heading">{{ Site::titleHeading() }}</h1>

					{{ Site::getBreadcrumbTrailMarkup() }}
				@endif