@if (!empty($permissions))

	<?php $itemIdAttribute = "";
	if (isset($form))
	{
		if (isset($user))
			$itemIdAttribute = 'data-user-id="'.$user->id.'" ';

		if (isset($role))
			$itemIdAttribute = 'data-role-id="'.$role->id.'" ';
	} ?>

	<ul class="tree tree-permissions" {!! $itemIdAttribute !!}>

		@foreach ($permissions as $permission)

			<?php $hasPermission = false;
			if (isset($form))
			{
				if (isset($user))
					$hasPermission = $user->hasPermission($permission->permission);

				if (isset($role))
					$hasPermission = $role->hasPermission($permission->permission);
			} ?>

			<li data-permission="{{ $permission->permission }}" {!! ($hasPermission ? 'data-added="1"' : '') . HTML::dynamicArea($hasPermission, 'active') !!}>
				<a class="btn btn-xs btn-primary pull-left tree-expand-collapse{!! HTML::dynamicArea(!$permission->subPermissions()->count(), 'disabled', true) !!}" data-expanded="0">
					<i class="fa fa-folder-o"></i>
				</a>

				<div class="info">
					<i class="permission-active fa fa-check-circle"></i>
					<i class="sub-permission-active fa fa-dot-circle-o"></i>
					<i class="permission-inactive fa fa-circle-o"></i>

					{{ $permission->name }}
				</div>

				@if (isset($form))

					<?php $source = (object) [
						'type' => null,
					];

					if (isset($user))
						$source = $user->getPermissionSource($permission->permission, true); ?>

					<div class="inline-block">

						<div class="add-area hidden">
							<a class="btn btn-xs btn-green btn-add-permission show-tooltip pull-left" title="Add Permission">
								<i class="fa fa-plus"></i>
							</a>
						</div>

						<div class="remove-area hidden">
							<a class="btn btn-xs btn-remove-permission{!! HTML::dynamicArea(is_null($source->type) || $source->type == "User", ['btn-danger show-tooltip', 'btn-grey disabled'], true) !!} pull-left" title="Remove Permission">
								<i class="fa fa-minus"></i>
							</a>

							@if (in_array($source->type, ['Role', 'Permission']))

								Inherited from <em>{{ $source->name }}</em> {{ $source->type }}

							@endif
						</div>

					</div><!-- /.inline-block -->

				@endif

				@if ($permission->subPermissions()->count())

					@include(Fractal::view('users.permissions.partials.tree', true), ['permissions' => $permission->subPermissions])

				@endif
			</li>

		@endforeach

	</ul>

@endif