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
						'uri'        => Config::get('fractal::baseURI').'/menus/:id/edit',
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
			'class'         => 'table-striped table-bordered table-hover',
			'noDataMessage' => Lang::get('fractal::messages.noItems', array('items' => Str::plural(Lang::get('fractal::labels.page')))),
		),
		'columns' => array(
			array(
				'attribute' => 'id',
			),
			array(
				'attribute' => 'title',
			),
			array(
				'attribute' => 'slug',
			),
			array(
				'attribute' => 'active',
				'type'      => 'boolean',
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
						'uri'        => Config::get('fractal::baseURI').'/pages/:slug/edit',
						'attributes' => array(
							'title'        => Lang::get('fractal::labels.editPage'),
						),
					),
					array(
						'icon'       => 'file',
						'uri'        => Config::get('fractal::pageURI') == "" ? ':slug' : Config::get('fractal::pageURI').'/:slug',
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

	'users' => array(
		'table' => array(
			'class'         => 'table-striped table-bordered table-hover',
			'noDataMessage' => Lang::get('fractal::messages.noItems', array('items' => Str::plural(Lang::get('fractal::labels.user')))),
		),
		'columns' => array(
			array(
				'attribute' => 'id',
			),
			array(
				'attribute' => 'username',
				'class'     => 'username',
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
			),
			array(
				'attribute' => 'banned',
				'type'      => 'boolean',
				'class'     => 'banned',
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
						'uri'        => Config::get('fractal::baseURI').'/users/:username/edit',
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
							'hidden' => array(
								'banned' => false,
							),
						),
						'attributes'     => array(
							'data-user-id' => ':id',
							'title'        => Lang::get('fractal::labels.unbanUser'),
						),
					),
					array(
						'icon'       => 'remove',
						'class'      => 'delete-user red',
						'classModifiers' => array(
							'invisible' => array(
								'id' => 1,
							),
						),
						'attributes' => array(
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
						'uri'        => Config::get('fractal::baseURI').'/user-roles/:id/edit',
						'attributes' => array(
							'title'        => Lang::get('fractal::labels.editRole'),
						),
					),
					array(
						'icon'       => 'remove',
						'class'      => 'delete-user-role red',
						'attributes' => array(
							'data-user-id' => ':id',
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

);