<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Tables
	|--------------------------------------------------------------------------
	|
	| The table setups for the various models in the CMS.
	|
	*/

	'menus' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.menu')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
			],
			[
				'attribute' => 'name',
			],
			[
				'label'     => 'Preview',
				'method'    => 'getActiveItemPreview()',
			],
			[
				'label'     => 'CMS',
				'attribute' => 'cms',
				'type'      => 'boolean',
				'developer' => true,
			],
			[
				'label'     => 'Last Updated',
				'method'    => 'getLastUpdatedDateTime()',
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => Config::get('fractal::baseUri').'/menus/:id/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editMenu'),
						],
					],
					[
						'icon'       => 'remove',
						'class'      => 'btn btn-danger action-item',
						'attributes' => [
							'data-item-id'         => ':id',
							'data-item-name'       => ':title',
							'data-action'          => 'delete',
							'data-action-type'     => 'delete',
							'data-action-message'  => 'confirmDelete',
							'title'                => Fractal::lang('labels.deleteMenu'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix' => 'menu',
		],
	],

	'pages' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.page')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'attribute' => 'title',
				'class'     => 'title',
				'sort'      => true,
			],
			[
				'attribute' => 'slug',
				'sort'      => true,
			],
			[
				'label'     => 'Published',
				'method'    => 'getPublishedStatus()',
				'sort'      => 'published_at',
			],
			[
				'label'     => (Fractal::getSetting('Display Unique Content Views') ? 'Unique ' : '').'Views',
				'method'    => (Fractal::getSetting('Display Unique Content Views') ? 'getUniqueViews()' : 'getViews()'),
				'bodyClass' => 'text-align-right',
			],
			[
				'label'     => 'Last Updated',
				'method'    => 'getLastUpdatedDateTime()',
				'sort'      => 'updated_at',
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => Config::get('fractal::baseUri').'/pages/:slug/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editPage'),
						],
					],
					[
						'icon'       => 'file',
						'class'      => 'btn btn-default',
						'uri'        => Config::get('fractal::pageUri') == "" ? ':slug' : Config::get('fractal::pageUri').'/:slug',
						'attributes' => [
							'title'  => Fractal::lang('labels.viewPage'),
						],
					],
					[
						'icon'       => 'remove',
						'class'      => 'btn btn-danger action-item',
						'attributes' => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':title',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-message' => 'confirmDelete',
							'title'               => Fractal::lang('labels.deletePage'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix'       => 'page',
			'classModifiers' => [
				'danger' => [
					'isPublished()' => false,
				],
			],
		],
	],

	'files' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.file')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'label'     => Fractal::lang('labels.image'),
				'method'    => 'getThumbnailImage()',
				'class'     => 'image',
				'sort'      => 'filename',
			],
			[
				'attribute' => 'name',
				'class'     => 'name',
				'sort'      => true,
			],
			[
				'label'     => Fractal::lang('labels.type'),
				'method'    => 'getType()',
				'sort'      => 'type_id',
			],
			[
				'label'     => Fractal::lang('labels.dimensions'),
				'method'    => 'getImageDimensions()',
				'sort'      => 'width',
			],
			[
				'label'     => 'Last Updated',
				'method'    => 'getLastUpdatedDateTime()',
				'sort'      => 'updated_at',
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => Config::get('fractal::baseUri').'/files/:id/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editFile'),
						],
					],
					[
						'icon'       => 'remove',
						'class'      => 'btn btn-danger action-item',
						'attributes' => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':name',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-message' => 'confirmDelete',
							'title'               => Fractal::lang('labels.deleteFile'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix' => 'file',
		],
	],

	'layoutTemplates' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.layoutTemplate')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'attribute' => 'name',
				'class'     => 'name',
				'sort'      => true,
			],
			[
				'label'     => Fractal::lang('labels.pages'),
				'method'    => 'getNumberOfPages()',
			],
			[
				'label'     => Fractal::lang('labels.blogArticles'),
				'method'    => 'getNumberOfArticles()',
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'classModifiers' => [
							'invisible' => [
								'static' => true,
							],
						],
						'uri'        => Config::get('fractal::baseUri').'/layout-templates/:id/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editLayoutTemplate'),
						],
					],
					[
						'icon'           => 'remove',
						'class'          => 'btn btn-danger action-item',
						'classModifiers' => [
							'invisible' => [
								'static' => true,
							],
						],
						'attributes'     => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':name',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-message' => 'confirmDelete',
							'title'               => Fractal::lang('labels.deleteLayoutTemplate'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix' => 'layout-template',
		],
	],

	'mediaItems' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.mediaItem')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'label'     => Fractal::lang('labels.image'),
				'method'    => 'getThumbnailImage()',
				'class'     => 'image',
				'sort'      => 'filename',
			],
			[
				'label'     => Fractal::lang('labels.mediaType'),
				'method'    => 'getType()',
				'sort'      => 'media_type_id',
			],
			[
				'attribute' => 'title',
				'class'     => 'title',
				'sort'      => true,
			],
			[
				'label'     => 'Published',
				'method'    => 'getPublishedStatus()',
				'sort'      => 'published_at',
			],
			[
				'label'     => (Fractal::getSetting('Display Unique Content Views') ? 'Unique ' : '').'Views',
				'method'    => (Fractal::getSetting('Display Unique Content Views') ? 'getUniqueViews()' : 'getViews()'),
				'bodyClass' => 'text-align-right',
			],
			[
				'label'     => (Fractal::getSetting('Display Unique Content Downloads') ? 'Unique ' : '').'Downloads',
				'method'    => (Fractal::getSetting('Display Unique Content Downloads') ? 'getUniqueDownloads()' : 'getDownloads()'),
				'bodyClass' => 'text-align-right',
			],
			[
				'label'     => 'Last Updated',
				'method'    => 'getLastUpdatedDateTime()',
				'sort'      => 'updated_at',
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => Config::get('fractal::baseUri').'/media/items/:slug/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editItem'),
						],
					],
					[
						'icon'       => 'file',
						'class'      => 'btn btn-default',
						'url'        => Fractal::mediaUrl(Config::get('fractal::blogs.baseUri') == false ? 'item/:slug' : Config::get('fractal::blogs.baseUri').'/article/:slug'),
						'attributes' => [
							'title'  => Fractal::lang('labels.viewItem'),
						],
					],
					[
						'icon'       => 'remove',
						'class'      => 'btn btn-danger action-item',
						'attributes' => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':title',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-url'     => 'items/:id',
							'data-action-message' => 'confirmDelete',
							'title'               => Fractal::lang('labels.deleteItem'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix'       => 'media-item',
			'classModifiers' => [
				'danger' => [
					'isPublished()' => false,
				],
			],
		],
	],

	'mediaTypes' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.mediaType')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'attribute' => 'name',
				'class'     => 'name',
				'sort'      => true,
			],
			[
				'attribute' => 'slug',
				'sort'      => true,
			],
			[
				'label'     => Fractal::lang('labels.items'),
				'method'    => 'getNumberOfItems()',
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => Config::get('fractal::baseUri').'/media/types/:slug/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editType'),
						],
					],
					[
						'icon'           => 'remove',
						'class'          => 'btn btn-danger action-item',
						'attributes'     => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':name',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-url'     => 'types/:id',
							'data-action-message' => 'confirmDelete',
							'title'               => Fractal::lang('labels.deleteType'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix' => 'media-type',
		],
	],

	'mediaSets' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.mediaSet')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'attribute' => 'title',
				'class'     => 'name',
				'sort'      => true,
			],
			[
				'attribute' => 'slug',
				'sort'      => true,
			],
			[
				'label'     => Fractal::lang('labels.items'),
				'method'    => 'getNumberOfItems()',
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => Config::get('fractal::baseUri').'/media/sets/:slug/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editSet'),
						],
					],
					[
						'icon'           => 'remove',
						'class'          => 'btn btn-danger action-item',
						'attributes'     => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':name',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-url'     => 'sets/:id',
							'data-action-message' => 'confirmDelete',
							'title'               => Fractal::lang('labels.deleteSet'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix' => 'media-set',
		],
	],

	'blogArticles' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.article')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'attribute' => 'title',
				'class'     => 'title',
				'sort'      => true,
			],
			[
				'attribute' => 'slug',
				'sort'      => true,
			],
			[
				'label'     => Fractal::lang('labels.categories'),
				'method'    => 'categories()',
				'attribute' => 'name',
				'type'      => 'list',
			],
			[
				'label'     => 'Published',
				'method'    => 'getPublishedStatus()',
				'sort'      => 'published_at',
			],
			[
				'label'     => (Fractal::getSetting('Display Unique Content Views') ? 'Unique ' : '').'Views',
				'method'    => (Fractal::getSetting('Display Unique Content Views') ? 'getUniqueViews()' : 'getViews()'),
				'bodyClass' => 'text-align-right',
			],
			[
				'label'     => 'Last Updated',
				'method'    => 'getLastUpdatedDateTime()',
				'sort'      => 'updated_at',
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => Config::get('fractal::baseUri').'/blogs/articles/:slug/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editArticle'),
						],
					],
					[
						'icon'       => 'file',
						'class'      => 'btn btn-default',
						'url'        => Fractal::blogUrl(Config::get('fractal::blogs.baseUri') == false ? 'article/:slug' : Config::get('fractal::blogs.baseUri').'/article/:slug'),
						'attributes' => [
							'title'  => Fractal::lang('labels.viewArticle'),
						],
					],
					[
						'icon'       => 'remove',
						'class'      => 'btn btn-danger action-item',
						'attributes' => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':title',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-url'     => 'articles/:id',
							'data-action-message' => 'confirmDelete',
							'title'               => Fractal::lang('labels.deleteArticle'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix'       => 'blog-article',
			'classModifiers' => [
				'danger' => [
					'isPublished()' => false,
				],
			],
		],
	],

	'blogCategories' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.category')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'attribute' => 'name',
				'class'     => 'title',
				'sort'      => true,
			],
			[
				'attribute' => 'slug',
				'sort'      => true,
			],
			[
				'label'     => '# of Articles',
				'method'    => 'getNumberOfArticles()',
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => Config::get('fractal::baseUri').'/blogs/categories/:slug/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editCategory'),
						],
					],
					[
						'icon'       => 'remove',
						'class'      => 'btn btn-danger action-item',
						'attributes' => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':title',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-url'     => 'articles/:id',
							'data-action-message' => 'confirmDelete',
							'title'               => Fractal::lang('labels.deleteCategory'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix' => 'blog-category',
		],
	],

	'users' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.user')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'attribute' => 'username',
				'class'     => 'username',
				'sort'      => true,
			],
			[
				'attribute' => 'name',
				'method'    => 'getName()',
				'sort'      => 'last_name',
			],
			[
				'label'     => 'Email',
				'elements'  => [
					[
						'text' => ':email',
						'href' => 'mailto::email',
					],
				],
				'sort'      => 'email',
			],
			[
				'label'     => Fractal::lang('labels.roles'),
				'method'    => 'roles()',
				'attribute' => 'name',
				'type'      => 'list',
			],
			[
				'label'     => 'Activated',
				'method'    => 'isActivated()',
				'type'      => 'boolean',
				'sort'      => true,
			],
			[
				'label'     => 'Banned',
				'method'    => 'isBanned()',
				'type'      => 'boolean',
				'class'     => 'banned',
				'sort'      => true,
			],
			[
				'label'     => 'Last Updated',
				'attribute' => 'updated_at',
				'type'      => 'dateTime',
				'sort'      => true,
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => Config::get('fractal::baseUri').'/users/:username/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editUser'),
						],
					],
					[
						'icon'           => 'ban-circle',
						'class'          => 'btn btn-danger action-item ban-user',
						'classModifiers' => [
							'hidden' => [
								'isBanned()' => true,
							],
							'invisible' => [
								'id' => 1,
							],
						],
						'attributes'     => [
							'data-item-id'         => ':id',
							'data-item-name'       => ':username',
							'data-action-function' => 'actionBanUser',
							'data-action-message'  => 'confirmBanUser',
							'title'                => Fractal::lang('labels.banUser'),
						],
					],
					[
						'icon'           => 'ok-circle',
						'class'          => 'btn btn-primary action-item unban-user',
						'classModifiers' => [
							'hidden' => [
								'isBanned()' => false,
							],
							'invisible' => [
								'id' => 1,
							],
						],
						'attributes'     => [
							'data-item-id'         => ':id',
							'data-item-name'       => ':username',
							'data-action-function' => 'actionUnbanUser',
							'data-action-message'  => 'confirmUnbanUser',
							'title'                => Fractal::lang('labels.unbanUser'),
						],
					],
					[
						'icon'           => 'remove',
						'class'          => 'btn btn-danger action-item',
						'classModifiers' => [
							'invisible' => [
								'id' => 1,
							],
						],
						'attributes'     => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':username',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-message' => 'confirmDelete',
							'title'               => Fractal::lang('labels.deleteUser'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix'       => 'user',
			'classModifiers' => [
				'warning' => [
					'isActivated()' => false,
				],
				'danger' => [
					'isBanned()'    => true,
				],
			],
		],
	],

	'userRoles' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.role')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'attribute' => 'role',
				'sort'      => true,
				'developer' => true,
			],
			[
				'attribute' => 'name',
				'sort'      => true,
				'class'     => 'name',
			],
			[
				'label'     => 'Last Updated',
				'attribute' => 'updated_at',
				'type'      => 'dateTime',
				'sort'      => true,
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => Config::get('fractal::baseUri').'/users/roles/:id/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editRole'),
						],
					],
					[
						'icon'           => 'remove',
						'class'          => 'btn btn-danger action-item',
						'classModifiers' => [
							'invisible' => [
								'id' => 1,
							],
						],
						'attributes' => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':title',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-url'     => 'roles/:id',
							'data-action-message' => 'confirmDelete',
							'title'               => Fractal::lang('labels.deleteRole'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix' => 'user-role',
		],
	],

	'userPermissions' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.permission')]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'attribute' => 'permission',
				'sort'      => true,
			],
			[
				'attribute' => 'name',
				'sort'      => true,
				'class'     => 'name',
			],
			[
				'attribute' => 'description',
				'sort'      => true,
			],
			[
				'label'     => Str::plural(Fractal::lang('labels.role')),
				'method'    => 'roles()',
				'attribute' => 'name',
				'type'      => 'list',
			],
			[
				'label'     => 'Last Updated',
				'attribute' => 'updated_at',
				'type'      => 'dateTime',
				'sort'      => true,
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => Config::get('fractal::baseUri').'/users/permissions/:id/edit',
						'attributes' => [
							'title' => Fractal::lang('labels.editPermission'),
						],
					],
					[
						'icon'           => 'remove',
						'class'          => 'btn btn-danger action-item',
						'classModifiers' => [
							'invisible' => [
								'id' => 1,
							],
						],
						'attributes' => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':title',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-url'     => 'permissions/:id',
							'data-action-message' => 'confirmDelete',
							'title'               => Fractal::lang('labels.deletePermission'),
						],
					],
				],
			],
		],
		'rows' => [
			'idPrefix' => 'user-permission',
		],
	],

	'activities' => [
		'table' => [
			'class'         => 'table-striped table-bordered table-hover table-sortable',
			'noDataMessage' => Fractal::lang('messages.noItems', ['items' => Fractal::langLowerPlural('labels.activity')]),
		],
		'columns' => [
			[
				'label'     => '',
				'method'    => 'getIconMarkup()',
			],
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'label'     => 'Name',
				'method'    => 'getName()',
				'sort'      => 'user_id',
			],
			[
				'attribute' => 'description',
				'sort'      => true,
			],
			[
				'attribute' => 'details',
				'sort'      => true,
			],
			[
				'attribute' => 'developer',
				'type'      => 'boolean',
				'sort'      => true,
				'developer' => true,
			],
			[
				'label'     => 'IP Address',
				'attribute' => 'ip_address',
				'sort'      => true,
				'developer' => true,
			],
			[
				'label'     => 'User Agent',
				'method'    => 'getUserAgentPreview()',
				'sort'      => 'user_agent',
				'class'     => 'small-text',
				'developer' => true,
			],
			[
				'label'     => Fractal::lang('labels.timestamp'),
				'attribute' => 'created_at',
				'type'      => 'dateTime',
				'sort'      => true,
			],
		],
		'rows' => [
			'idPrefix' => 'activity',
		],
	],

];