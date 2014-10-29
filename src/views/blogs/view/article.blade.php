@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::section'))

	@include(Fractal::blogView('partials.article', true))

@endsection

@section('rightColumn')

	@include(Fractal::blogView('partials.nav_side', true))

@endsection