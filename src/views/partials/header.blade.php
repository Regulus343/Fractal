<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>{{{ Site::title() }}}</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
	@include(Fractal::view('partials.included_files', true))

	@include(Fractal::view('partials.modal', true))

	<div class="container-full" id="container">

		@include(Fractal::view('partials.nav_top', true))

		@include(Fractal::view('partials.nav_side', true))

		<div class="container-full sidebar-offset" id="container-content">
			<div class="container-pad">
				@if (!Site::get('hideTitle'))
					<h1 id="main-heading">{{ Site::titleHeading() }}</h1>
				@endif

				@include(Fractal::view('partials.messages', true))