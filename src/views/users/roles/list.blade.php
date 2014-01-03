@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{-- Content Table --}}
	<div class="row">
		<div class="col-md-12">
			{{ HTML::table(Config::get('fractal::tables.userRoles'), $roles) }}
		</div>
	</div>

	<a class="btn btn-primary" href="{{ Fractal::url('user-roles/create') }}">
		<span class="glyphicon glyphicon-file"></span>&nbsp; {{ Lang::get('fractal::labels.createRole') }}
	</a>

@stop