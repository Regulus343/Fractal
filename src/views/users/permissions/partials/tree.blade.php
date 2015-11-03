@if (!empty($permissions))

	<ul class="tree">

		@foreach ($permissions as $permission)

			<li>
				<a class="btn btn-xs btn-primary pull-left tree-expand-collapse{!! HTML::dynamicArea(!$permission->subPermissions()->count(), 'disabled', true) !!}" data-expanded="0">
					<i class="fa fa-plus"></i>
				</a>

				{{ $permission->name }}

				@if ($permission->subPermissions()->count())

					@include(Fractal::view('partials.tree'), ['permissions' => $permission->subPermissions])

				@endif
			</li>

		@endforeach

	</ul>

@endif