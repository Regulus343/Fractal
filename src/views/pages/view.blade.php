@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	<div class="row">
		<div class="col-md-12">
			<div>{{ $page->content }}</div>
		</div>
	</div>

@stop