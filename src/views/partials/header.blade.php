<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>{{{ Site::title() }}}</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
	@include(Fractal::view('partials.included_files', true))

	@include(Fractal::view('partials.modal', true))

	<div class="container" id="container">

		@include(Fractal::view('partials.nav', true))

		@if (!Site::get('hideTitle'))
			<h1 id="main-heading">{{ Site::titleHeading() }}</h1>
		@endif

		@include(Fractal::view('partials.messages', true))