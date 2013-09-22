<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>{{{ Site::title() }}}</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
	@include('fractal::partials.included_files')

	<div class="container" id="container">

		@include('fractal::partials.nav')

		@if (!isset($hideTitle) || !$hideTitle)
			<h1 id="main-heading">{{ Site::titleHeading() }}</h1>
		@endif

		@include('fractal::partials.messages')