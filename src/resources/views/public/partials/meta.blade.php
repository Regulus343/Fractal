<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">

<meta property="og:title" content="{{ Site::heading() }}" />

@if (Site::get('contentImage'))

	<meta property="og:image" content="{{ Site::get('contentImage') }}" />

@endif

@if (Site::get('contentDescription'))

	<meta property="og:description" content="{{ Site::get('contentDescription') }}" />

@endif

<link rel="canonical" href="{{ Site::get('contentUrl', Request::url()) }}" />