<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>{{ Site::title() }}</title>

	@include(Fractal::view('public.partials.meta', true))

	<link rel="shortcut icon" type="image/x-icon" href="{{ Fractal::getImageUrlFromConfig('cms.favicon') }}" />

	@include(Fractal::view('public.partials.included_files', true))

	<script type="text/javascript" src="{{ Site::js('fractal/public', 'regulus/fractal') }}"></script>
</head>
<body>
	@include(Fractal::view('partials.modal', true))

	<div class="container" id="container">

		@include(Fractal::view('public.partials.nav', true))

		<div id="container-content">
			<div class="container-pad-fluid">

				<div class="row">

					@yield('leftColumn')

					<div class="col-md-{{ Site::get('contentColumnWidth', 12) }}">

						@if (!Site::get('title.hide'))
							<h1 id="main-heading">{!! Site::heading() !!}</h1>

							{!! Site::getBreadcrumbTrailMarkup() !!}
						@endif

						@include(Fractal::view('public.partials.messages', true))