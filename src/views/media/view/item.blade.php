@extends(Config::get('fractal::layoutPublic'))

@section(Config::get('fractal::section'))

	@include(Fractal::mediaView('partials.item', true))

@endsection

@section('rightColumn')

	@include(Fractal::mediaView('partials.nav_side', true))

@endsection