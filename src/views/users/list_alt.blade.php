@extends(Config::get('fractal::layout'))

@section(Config::get('fractal::section'))

	{{-- Search & Pagination --}}
	@include(Config::get('fractal::viewsLocation').'partials.search_pagination')

	{{-- Content Table --}}
	<div class="row">
		<div class="col-md-12">
			<table class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th>ID</th>
						<th>Username</th>
						<th>Role(s)</th>
						<th>Last Updated</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($users as $user)
						<tr id="user-{{ $user->id }}">
							<td>{{ $user->id }}</td>
							<td>{{{ $user->username }}}</td>
							<td>{{ Format::objListToStr($user->roles, 'name') }}</td>
							<td>{{ Format::date($user->created_at, Config::get('fractal::dateTimeFormat')) }}</td>
							<td>
								<a href="{{ Regulus\Fractal\Fractal::controllerUrl(strtolower($user->username).'/edit', 'Users') }}" title="Edit"><span class="glyphicon glyphicon-wrench"></span></a>
								<a href="#" class="warning delete-user" data-user-id="{{ $user->id }}" title="Delete"><span class="glyphicon glyphicon-remove"></span></a>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	{{-- Bottom Pagination --}}
	@include(Config::get('fractal::viewsLocation').'partials.pagination')

	<a href="{{ Fractal::url('users/create') }}" class="btn btn-primary">
		<span class="glyphicon glyphicon-user"></span>&nbsp; {{ Lang::get('fractal::labels.createUser') }}
	</a>

@stop