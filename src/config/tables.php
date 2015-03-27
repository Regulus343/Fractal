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
			'class'           => 'table-striped table-bordered table-hover',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.menu', 2)]),
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
						'uri'        => config('cms.base_uri').'/menus/:id/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.menu')]),
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
							'data-action-message'  => 'confirm.delete',
							'title'                => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.menu')]),
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix' => 'menu',
		],
	],

	'pages' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.page', 2)]),
		],
		'columns' => [
			[
				'attribute'  => 'id',
				'sort'       => true,
			],
			[
				'attribute'  => 'title',
				'class'      => 'title',
				'sort'       => true,
			],
			[
				'attribute'  => 'slug',
				'sort'       => true,
			],
			[
				'label'      => 'Published',
				'method'     => 'getPublishedStatus()',
				'sort'       => 'published_at',
			],
			[
				'label'      => (Fractal::getSetting('Display Unique Content Views') ? 'Unique ' : '').'Views',
				'method'     => (Fractal::getSetting('Display Unique Content Views') ? 'getUniqueViews()' : 'getViews()'),
				'body_class' => 'text-align-right',
			],
			[
				'label'      => 'Last Updated',
				'method'     => 'getLastUpdatedDateTime()',
				'sort'       => 'updated_at',
			],
			[
				'label'      => 'Actions',
				'class'      => 'actions',
				'elements'   => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => config('cms.base_uri').'/pages/:slug/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.page')]),
						],
					],
					[
						'icon'       => 'file',
						'class'      => 'btn btn-default',
						'uri'        => config('cms.page_uri') == "" ? ':slug' : config('cms.page_uri').'/:slug',
						'attributes' => [
							'title'  => Fractal::trans('labels.view_item', ['item' => Fractal::transChoice('labels.page')]),
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
							'data-action-message' => 'confirm.delete',
							'title'               => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.page')]),
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix'       => 'page',
			'class_modifiers' => [
				'danger' => [
					'isPublished()' => false,
				],
			],
		],
	],

	'files' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.file', 2)]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'label'     => Fractal::transChoice('labels.image'),
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
				'label'     => Fractal::transChoice('labels.type'),
				'method'    => 'getType()',
				'sort'      => 'type_id',
			],
			[
				'label'     => Fractal::trans('labels.dimensions'),
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
						'uri'        => config('cms.base_uri').'/files/:id/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.file')]),
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
							'data-action-message' => 'confirm.delete',
							'title'               => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.file')]),
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix' => 'file',
		],
	],

	'layout_templates' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.layout_template', 2)]),
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
				'label'     => Fractal::transChoice('labels.page', 2),
				'method'    => 'getNumberOfPages()',
			],
			[
				'label'     => Fractal::transChoice('labels.blog_article', 2),
				'method'    => 'getNumberOfArticles()',
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'class_modifiers' => [
							'invisible' => [
								'static' => true,
							],
						],
						'uri'        => config('cms.base_uri').'/layout-templates/:id/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.layout_template')]),
						],
					],
					[
						'icon'           => 'remove',
						'class'          => 'btn btn-danger action-item',
						'class_modifiers' => [
							'invisible' => [
								'static' => true,
							],
						],
						'attributes'     => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':name',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-message' => 'confirm.delete',
							'title'               => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.layout_template')]),
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix' => 'layout-template',
		],
	],

	'media_items' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.media_item', 2)]),
		],
		'columns' => [
			[
				'attribute'  => 'id',
				'sort'       => true,
			],
			[
				'label'      => Fractal::transChoice('labels.image'),
				'method'     => 'getThumbnailImage()',
				'class'      => 'image',
				'sort'       => 'filename',
			],
			[
				'label'      => Fractal::transChoice('labels.media_type'),
				'method'     => 'getType()',
				'sort'       => 'media_type_id',
			],
			[
				'attribute'  => 'title',
				'class'      => 'title',
				'sort'       => true,
			],
			[
				'label'      => 'Published',
				'method'     => 'getPublishedStatus()',
				'sort'       => 'published_at',
			],
			[
				'label'      => Fractal::transChoice('labels.media_set', 2),
				'method'     => 'getNumberOfSets()',
				'body_class' => 'text-align-right',
			],
			[
				'label'      => (Fractal::getSetting('Display Unique Content Views') ? 'Unique ' : '').'Views',
				'method'     => (Fractal::getSetting('Display Unique Content Views') ? 'getUniqueViews()' : 'getViews()'),
				'body_class' => 'text-align-right',
			],
			[
				'label'      => (Fractal::getSetting('Display Unique Content Downloads') ? 'Unique ' : '').'Downloads',
				'method'     => (Fractal::getSetting('Display Unique Content Downloads') ? 'getUniqueDownloads()' : 'getDownloads()'),
				'body_class' => 'text-align-right',
			],
			[
				'label'      => 'Last Updated',
				'method'     => 'getLastUpdatedDateTime()',
				'sort'       => 'updated_at',
			],
			[
				'label'      => 'Actions',
				'class'      => 'actions',
				'elements'   => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => config('cms.base_uri').'/media/items/:slug/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.media_item')]),
						],
					],
					[
						'icon'       => 'file',
						'class'      => 'btn btn-default',
						'url'        => Fractal::mediaUrl(config('media.base_uri') == false ? 'item/:slug' : config('media.base_uri').'/article/:slug'),
						'attributes' => [
							'title'  => Fractal::trans('labels.view_item', ['item' => Fractal::transChoice('labels.media_item')]),
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
							'data-action-message' => 'confirm.delete',
							'title'               => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.media_item')]),
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix'       => 'media-item',
			'class_modifiers' => [
				'danger' => [
					'isPublished()' => false,
				],
			],
		],
	],

	'media_types' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.media_type', 2)]),
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
				'label'     => 'File Type',
				'method'    => 'getFileType()',
				'sort'      => 'file_type_id',
			],
			[
				'label'     => 'Media Source Required',
				'attribute' => 'media_source_required',
				'type'      => 'boolean',
				'sort'      => true,
			],
			[
				'label'     => Fractal::transChoice('labels.item', 2),
				'method'    => 'getNumberOfItems()',
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => config('cms.base_uri').'/media/types/:slug/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.media_type')]),
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
							'data-action-url'     => 'types/:id',
							'data-action-message' => 'confirm.delete',
							'title'               => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.media_type')]),
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix' => 'media-type',
		],
	],

	'media_sets' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.media_set', 2)]),
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
				'label'     => Fractal::transChoice('labels.item', 2),
				'method'    => 'getNumberOfItems()',
			],
			[
				'attribute' => 'image_gallery',
				'type'      => 'boolean',
				'sort'      => true,
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => config('cms.base_uri').'/media/sets/:slug/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.media_set')]),
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
							'data-action-message' => 'confirm.delete',
							'title'               => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.media_set')]),
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix' => 'media-set',
		],
	],

	'blog_articles' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.article', 2)]),
		],
		'columns' => [
			[
				'attribute'  => 'id',
				'sort'       => true,
			],
			[
				'attribute'  => 'title',
				'class'      => 'title',
				'sort'       => true,
			],
			[
				'attribute'  => 'slug',
				'sort'       => true,
			],
			[
				'label'      => Fractal::transChoice('labels.category', 2),
				'method'     => 'categories()',
				'attribute'  => 'name',
				'type'       => 'list',
			],
			[
				'label'      => 'Published',
				'method'     => 'getPublishedStatus()',
				'sort'       => 'published_at',
			],
			[
				'label'      => (Fractal::getSetting('Display Unique Content Views') ? 'Unique ' : '').'Views',
				'method'     => (Fractal::getSetting('Display Unique Content Views') ? 'getUniqueViews()' : 'getViews()'),
				'body_class' => 'text-align-right',
			],
			[
				'label'      => 'Last Updated',
				'method'     => 'getLastUpdatedDateTime()',
				'sort'       => 'updated_at',
			],
			[
				'label'      => 'Actions',
				'class'      => 'actions',
				'elements'   => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => config('cms.base_uri').'/blogs/articles/:slug/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.article')]),
						],
					],
					[
						'icon'       => 'file',
						'class'      => 'btn btn-default',
						'url'        => Fractal::blogUrl(config('blogs.base_uri') == false ? 'article/:slug' : config('blogs.base_uri').'/article/:slug'),
						'attributes' => [
							'title'  => Fractal::trans('labels.view_item', ['item' => Fractal::transChoice('labels.article')]),
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
							'data-action-message' => 'confirm.delete',
							'title'               => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.article')]),
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix'       => 'blog-article',
			'class_modifiers' => [
				'danger' => [
					'isPublished()' => false,
				],
			],
		],
	],

	'blog_categories' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.category', 2)]),
		],
		'columns' => [
			[
				'attribute'  => 'id',
				'sort'       => true,
			],
			[
				'attribute'  => 'name',
				'class'      => 'title',
				'sort'       => true,
			],
			[
				'attribute'  => 'slug',
				'sort'       => true,
			],
			[
				'label'      => '# of Articles',
				'method'     => 'getNumberOfArticles()',
				'body_class' => 'text-align-right',
			],
			[
				'label'      => 'Actions',
				'class'      => 'actions',
				'elements'   => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => config('cms.base_uri').'/blogs/categories/:slug/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.category')]),
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
							'data-action-message' => 'confirm.delete',
							'title'               => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.category')]),
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix' => 'blog-category',
		],
	],

	'users' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.user', 2)]),
		],
		'columns' => [
			[
				'attribute' => 'id',
				'sort'      => true,
			],
			[
				'attribute' => 'name',
				'class'     => 'username',
				'sort'      => true,
			],
			[
				'attribute' => 'last_name',
				'method'    => 'getName()',
				'sort'      => true,
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
				'label'     => Fractal::transChoice('labels.role', 2),
				'method'    => 'roles()',
				'attribute' => 'name',
				'type'      => 'list',
			],
			[
				'label'     => 'Activated',
				'method'    => 'isActivated()',
				'type'      => 'boolean',
				'sort'      => 'activated_at',
			],
			[
				'label'     => 'Banned',
				'method'    => 'isBanned()',
				'type'      => 'boolean',
				'class'     => 'banned',
				'sort'      => 'banned_at',
			],
			[
				'label'     => 'Last Updated',
				'attribute' => 'updated_at',
				'type'      => 'datetime',
				'sort'      => true,
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => config('cms.base_uri').'/users/:name/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.user')]),
						],
					],
					[
						'icon'            => 'ban',
						'class'           => 'btn btn-danger action-item ban-user',
						'class_modifiers' => [
							'hidden' => [
								'isBanned()' => true,
							],
							'invisible' => [
								'id' => 1,
							],
						],
						'attributes' => [
							'data-item-id'         => ':id',
							'data-item-name'       => ':name',
							'data-action-function' => 'actionBanUser',
							'data-action-message'  => 'confirm.banUser',
							'title'                => Fractal::trans('labels.ban_user'),
						],
					],
					[
						'icon'            => 'check-circle-o',
						'class'           => 'btn btn-primary action-item unban-user',
						'class_modifiers' => [
							'hidden' => [
								'isBanned()' => false,
							],
							'invisible' => [
								'id' => 1,
							],
						],
						'attributes' => [
							'data-item-id'         => ':id',
							'data-item-name'       => ':name',
							'data-action-function' => 'actionUnbanUser',
							'data-action-message'  => 'confirm.unbanUser',
							'title'                => Fractal::trans('labels.unban_user'),
						],
					],
					[
						'icon'            => 'remove',
						'class'           => 'btn btn-danger action-item',
						'class_modifiers' => [
							'invisible' => [
								'id' => 1,
							],
						],
						'attributes' => [
							'data-item-id'        => ':id',
							'data-item-name'      => ':name',
							'data-action'         => 'delete',
							'data-action-type'    => 'delete',
							'data-action-message' => 'confirm.delete',
							'title'               => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.user')]),
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix'       => 'user',
			'class_modifiers' => [
				'warning' => [
					'isActivated()' => false,
				],
				'danger' => [
					'isBanned()' => true,
				],
			],
		],
	],

	'user_roles' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.role', 2)]),
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
				'type'      => 'datetime',
				'sort'      => true,
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => config('cms.base_uri').'/users/roles/:id/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.role')])
						],
					],
					[
						'icon'            => 'remove',
						'class'           => 'btn btn-danger action-item',
						'class_modifiers' => [
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
							'data-action-message' => 'confirm.delete',
							'title'               => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.role')])
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix' => 'user-role',
		],
	],

	'user_permissions' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.permission', 2)]),
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
				'label'     => Fractal::transChoice('labels.role', 2),
				'method'    => 'roles()',
				'attribute' => 'name',
				'type'      => 'list',
			],
			[
				'label'     => 'Last Updated',
				'attribute' => 'updated_at',
				'type'      => 'datetime',
				'sort'      => true,
			],
			[
				'label'     => 'Actions',
				'class'     => 'actions',
				'elements'  => [
					[
						'icon'       => 'edit',
						'class'      => 'btn btn-primary',
						'uri'        => config('cms.base_uri').'/users/permissions/:id/edit',
						'attributes' => [
							'title' => Fractal::trans('labels.edit_item', ['item' => Fractal::transChoice('labels.permission')])
						],
					],
					[
						'icon'            => 'remove',
						'class'           => 'btn btn-danger action-item',
						'class_modifiers' => [
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
							'data-action-message' => 'confirm.delete',
							'title'               => Fractal::trans('labels.delete_item', ['item' => Fractal::transChoice('labels.permission')])
						],
					],
				],
			],
		],
		'rows' => [
			'id_prefix' => 'user-permission',
		],
	],

	'activities' => [
		'table' => [
			'class'           => 'table-striped table-bordered table-hover table-sortable',
			'no_data_message' => Fractal::trans('messages.no_items', ['items' => Fractal::transChoiceLower('labels.activity', 2)]),
		],
		'columns' => [
			[
				'label'     => '',
				'method'    => 'getIconMarkup()',
				'class'     => 'icon',
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
				'label'     => 'Description',
				'method'    => 'getLinkedDescription()',
				'sort'      => 'description',
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
				'label'     => Fractal::trans('labels.timestamp'),
				'attribute' => 'created_at',
				'type'      => 'datetime',
				'sort'      => true,
			],
		],
		'rows' => [
			'id_prefix' => 'activity',
		],
	],

];
