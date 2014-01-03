<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Tables
	|--------------------------------------------------------------------------
	|
	| The table setups for the various models in the CMS.
	|
	*/

	'menus' => array(
		'table' => array(
			'class'         => 'table-striped table-bordered table-hover',
			'noDataMessage' => Lang::get('fractal::messages.noItems', array('items' => Str::plural(Lang::get('fractal::labels.menu')))),
		),
		'columns' => array(
			array(
				'attribute' => 'id',
			),
			array(
				'attribute' => 'name',
			),
			array(
				'label'     => 'Preview',
				'method'    => 'getActiveItemPreview()',
			),
			array(
				'label'     => 'CMS',
				'attribute' => 'cms',
				'type'      => 'boolean',
				'developer' => true,
			),
			array(
				'label'     => 'Last Updated',
				'method'    => 'getLastUpdatedDate()',
			),
			array(
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => array(
					array(
						'icon'       => 'edit',
						'uri'        => Config::get('fractal::baseUri').'/menus/:id/edit',
						'attributes' => array(
							'title'        => Lang::get('fractal::labels.editMenu'),
						),
					),
					array(
						'icon'       => 'remove',
						'class'      => 'delete-menu red',
						'attributes' => array(
							'data-menu-id' => ':id',
							'title'        => Lang::get('fractal::labels.deleteMenu'),
						),
					),
				),
			),
		),
		'rows' => array(
			'idPrefix' => 'menu',
		),
	),

	'pages' => array(
		'table' => array(
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Lang::get('fractal::messages.noItems', array('items' => Str::plural(Lang::get('fractal::labels.page')))),
		),
		'columns' => array(
			array(
				'attribute' => 'id',
				'sort'      => true,
			),
			array(
				'attribute' => 'title',
				'sort'      => true,
			),
			array(
				'attribute' => 'slug',
				'sort'      => true,
			),
			array(
				'attribute' => 'active',
				'type'      => 'boolean',
				'sort'      => true,
			),
			array(
				'label'     => 'Last Updated',
				'method'    => 'getLastUpdatedDate()',
				'sort'      => 'updated_at',
			),
			array(
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => array(
					array(
						'icon'       => 'edit',
						'uri'        => Config::get('fractal::baseUri').'/pages/:slug/edit',
						'attributes' => array(
							'title'        => Lang::get('fractal::labels.editPage'),
						),
					),
					array(
						'icon'       => 'file',
						'uri'        => Config::get('fractal::pageUri') == "" ? ':slug' : Config::get('fractal::pageUri').'/:slug',
						'attributes' => array(
							'title'        => Lang::get('fractal::labels.viewPage'),
							'target'       => '_blank',
						),
					),
					array(
						'icon'       => 'remove',
						'class'      => 'delete-page red',
						'attributes' => array(
							'data-page-id' => ':id',
							'title'        => Lang::get('fractal::labels.deletePage'),
						),
					),
				),
			),
		),
		'rows' => array(
			'idPrefix'       => 'page',
			'classModifiers' => array(
				'danger' => array(
					'active' => false,
				),
			),
		),
	),

	'files' => array(
		'table' => array(
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Lang::get('fractal::messages.noItems', array('items' => Str::plural(Lang::get('fractal::labels.file')))),
		),
		'columns' => array(
			array(
				'attribute' => 'id',
				'sort'      => true,
			),
			array(
				'label'     => Lang::get('fractal::labels.image'),
				'method'    => 'getThumbnailImage()',
				'class'     => 'image',
				'sort'      => 'filename',
			),
			array(
				'attribute' => 'name',
				'class'     => 'name',
				'sort'      => true,
			),
			array(
				'label'     => Lang::get('fractal::labels.type'),
				'attribute' => 'type',
				'sort'      => true,
			),
			array(
				'label'     => Lang::get('fractal::labels.dimensions'),
				'method'    => 'getImageDimensions()',
				'sort'      => 'width',
			),
			array(
				'label'     => 'Last Updated',
				'method'    => 'getLastUpdatedDate()',
				'sort'      => 'updated_at',
			),
			array(
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => array(
					array(
						'icon'       => 'edit',
						'uri'        => Config::get('fractal::baseUri').'/files/:id/edit',
						'attributes' => array(
							'title'        => Lang::get('fractal::labels.editFile'),
						),
					),
					array(
						'icon'       => 'remove',
						'class'      => 'delete-file red',
						'attributes' => array(
							'data-file-id' => ':id',
							'title'        => Lang::get('fractal::labels.deleteFile'),
						),
					),
				),
			),
		),
		'rows' => array(
			'idPrefix'       => 'file',
		),
	),

	'users' => array(
		'table' => array(
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Lang::get('fractal::messages.noItems', array('items' => Str::plural(Lang::get('fractal::labels.user')))),
		),
		'columns' => array(
			array(
				'attribute' => 'id',
				'sort'      => true,
			),
			array(
				'attribute' => 'username',
				'class'     => 'username',
				'sort'      => true,
			),
			array(
				'attribute' => 'name',
				'method'    => 'getName()',
				'sort'      => 'last_name',
			),
			array(
				'label'     => 'Email',
				'elements'  => array(
					array(
						'text' => ':email',
						'href' => 'mailto::email',
					),
				),
				'sort'      => 'email',
			),
			array(
				'label'     => 'Role(s)',
				'method'    => 'roles()',
				'attribute' => 'name',
				'type'      => 'list',
			),
			array(
				'label'     => 'Activated',
				'attribute' => 'active',
				'type'      => 'boolean',
				'sort'      => true,
			),
			array(
				'attribute' => 'banned',
				'type'      => 'boolean',
				'class'     => 'banned',
				'sort'      => true,
			),
			array(
				'label'     => 'Last Updated',
				'attribute' => 'updated_at',
				'type'      => 'dateTime',
				'sort'      => true,
			),
			array(
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => array(
					array(
						'icon'       => 'edit',
						'uri'        => Config::get('fractal::baseUri').'/users/:username/edit',
						'attributes' => array(
							'title'        => Lang::get('fractal::labels.editUser'),
						),
					),
					array(
						'icon'           => 'ban-circle',
						'class'          => 'ban-user red',
						'classModifiers' => array(
							'hidden' => array(
								'banned' => true,
							),
							'invisible' => array(
								'id' => 1,
							),
						),
						'attributes'     => array(
							'data-user-id' => ':id',
							'title'        => Lang::get('fractal::labels.banUser'),
						),
					),
					array(
						'icon'           => 'ok-circle',
						'class'          => 'unban-user',
						'classModifiers' => array(
							'hidden'       => array(
								'banned' => false,
							),
							'invisible'    => array(
								'id'     => 1,
							),
						),
						'attributes'     => array(
							'data-user-id' => ':id',
							'title'        => Lang::get('fractal::labels.unbanUser'),
						),
					),
					array(
						'icon'           => 'remove',
						'class'          => 'delete-user red',
						'classModifiers' => array(
							'invisible'    => array(
								'id' => 1,
							),
						),
						'attributes'     => array(
							'data-user-id' => ':id',
							'title'        => Lang::get('fractal::labels.deleteUser'),
						),
					),
				),
			),
		),
		'rows' => array(
			'idPrefix'       => 'user',
			'classModifiers' => array(
				'warning' => array(
					'active' => false,
				),
				'danger' => array(
					'banned' => true,
				),
			),
		),
	),

	'userRoles' => array(
		'table' => array(
			'class'         => 'table-striped table-bordered table-hover',
			'noDataMessage' => Lang::get('fractal::messages.noItems', array('items' => Str::plural(Lang::get('fractal::labels.role')))),
		),
		'columns' => array(
			array(
				'attribute' => 'id',
			),
			array(
				'attribute' => 'role',
				'developer' => true,
			),
			array(
				'attribute' => 'name',
				'class'     => 'name',
			),
			array(
				'label'     => 'Last Updated',
				'attribute' => 'updated_at',
				'type'      => 'dateTime',
			),
			array(
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => array(
					array(
						'icon'       => 'edit',
						'uri'        => Config::get('fractal::baseUri').'/user-roles/:id/edit',
						'attributes' => array(
							'title'        => Lang::get('fractal::labels.editRole'),
						),
					),
					array(
						'icon'       => 'remove',
						'class'      => 'delete-user-role red',
						'classModifiers' => array(
							'invisible'    => array(
								'id' => 1,
							),
						),
						'attributes' => array(
							'data-role-id' => ':id',
							'title'        => Lang::get('fractal::labels.deleteRole'),
						),
					),
				),
			),
		),
		'rows' => array(
			'idPrefix' => 'role',
		),
	),

	'activity' => array(
		'table' => array(
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Lang::get('fractal::messages.noItems', array('items' => Str::plural(Lang::get('fractal::labels.activity')))),
		),
		'columns' => array(
			array(
				'attribute' => 'id',
				'sort'      => true,
			),
			array(
				'attribute' => 'description',
				'sort'      => true,
			),
			array(
				'attribute' => 'details',
				'sort'      => true,
			),
			array(
				'attribute' => 'developer',
				'type'      => 'boolean',
				'sort'      => true,
				'developer' => true,
			),
			array(
				'label'     => Lang::get('fractal::labels.timestamp'),
				'attribute' => 'created_at',
				'type'      => 'dateTime',
				'sort'      => true,
			),
		),
		'rows' => array(
			'idPrefix' => 'activity',
		),
	),

);