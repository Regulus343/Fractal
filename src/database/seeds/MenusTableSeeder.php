<?php

use Illuminate\Database\Seeder;

class MenusTableSeeder extends Seeder {

	public $timestamp;

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('menus')->truncate();
		DB::table('menu_items')->truncate();

		$this->timestamp = date('Y-m-d H:i:s');

		$cmsSubdomain = config('cms.subdomain');

		$menus = [
			[
				'name'  => 'CMS Main',
				'cms'   => true,

				'items' => [
					[
						'uri'                => '',
						'label_language_key' => 'content',
						'icon'               => 'th-list',
						'display_order'      => 1,
						'auth_status'        => 1,
						'items'              => [
							[
								'uri'                => 'menus',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:menu',
								'icon'               => 'tasks',
								'display_order'      => 1,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'pages',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:page',
								'icon'               => 'file',
								'display_order'      => 2,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'files',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:file',
								'icon'               => 'folder',
								'display_order'      => 3,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'layout-templates',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:layout_template',
								'icon'               => 'th',
								'display_order'      => 4,
								'auth_status'        => 1,
							],
						],
					],
					[
						'uri'                => 'media',
						'subdomain'          => $cmsSubdomain,
						'label_language_key' => 'media',
						'icon'               => 'th-list',
						'display_order'      => 2,
						'auth_status'        => 1,
						'items'              => [
							[
								'uri'                => 'media/items',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:item',
								'icon'               => 'file-image-o',
								'display_order'      => 3,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'media/types',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:type',
								'icon'               => 'tag',
								'display_order'      => 3,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'media/sets',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:set',
								'icon'               => 'folder',
								'display_order'      => 3,
								'auth_status'        => 1,
							],
						],
					],
					[
						'uri'                => 'blogs',
						'subdomain'          => $cmsSubdomain,
						'label_language_key' => 'plural:blog',
						'icon'               => 'th-list',
						'display_order'      => 2,
						'auth_status'        => 1,
						'items'              => [
							[
								'uri'                => 'blogs/articles',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:article',
								'icon'               => 'file',
								'display_order'      => 1,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'blogs/categories',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:category',
								'icon'               => 'book',
								'display_order'      => 2,
								'auth_status'        => 1,
							],
						],
					],
					[
						'uri'                => 'users',
						'subdomain'          => $cmsSubdomain,
						'label_language_key' => 'plural:user',
						'icon'               => 'user',
						'display_order'      => 2,
						'auth_status'        => 1,
						'items'              => [
							[
								'uri'                => 'users/roles',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:role',
								'icon'               => 'book',
								'display_order'      => 2,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'users',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:user',
								'icon'               => 'user',
								'display_order'      => 1,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'users/permissions',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'plural:permission',
								'icon'               => 'star',
								'display_order'      => 2,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'users/activity',
								'subdomain'          => $cmsSubdomain,
								'label_language_key' => 'singular:activity',
								'icon'               => 'info-circle',
								'display_order'      => 3,
								'auth_status'        => 1,
							],
						],
					],
					[
						'uri'                => 'settings',
						'subdomain'          => $cmsSubdomain,
						'label_language_key' => 'settings',
						'icon'               => 'cog',
						'display_order'      => 4,
						'auth_status'        => 1,
					],
				],
			],
			[
				'name'  => 'CMS Account',
				'cms'   => true,
				'items' => [
					[
						'uri'                => 'login',
						'subdomain'          => $cmsSubdomain,
						'label_language_key' => 'log_in',
						'icon'               => 'sign-in',
						'display_order'      => 1,
						'auth_status'        => 2,
					],
					[
						'uri'                => 'account',
						'subdomain'          => $cmsSubdomain,
						'label_language_key' => 'account',
						'icon'               => 'user',
						'display_order'      => 2,
						'auth_status'        => 1,
					],
					[
						'uri'                => 'logout',
						'subdomain'          => $cmsSubdomain,
						'label_language_key' => 'log_out',
						'icon'               => 'sign-out',
						'display_order'      => 3,
						'auth_status'        => 1,
					],
				],
			],
			[
				'name'  => 'Main',

				'items' => [
					[
						'type'               => 'Content Page',
						'page_id'            => 1,
						'label_language_key' => 'home',
						'icon'               => 'home',
						'display_order'      => 1,
					],
					[
						'uri'                => config('blogs.base_uri'),
						'subdomain'          => config('blogs.subdomain'),
						'label_language_key' => 'singular:blog',
						'icon'               => 'comment',
						'display_order'      => 2,
					],
					[
						'uri'                => config('media.base_uri'),
						'subdomain'          => config('media.subdomain'),
						'label_language_key' => 'media',
						'icon'               => 'book',
						'display_order'      => 3,
					],
					[
						'type'               => 'Content Page',
						'page_id'            => 2,
						'label_language_key' => 'about',
						'icon'               => 'list',
						'display_order'      => 4,
					],
					[
						'type'               => 'Content Page',
						'page_id'            => 3,
						'label_language_key' => 'contact',
						'icon'               => 'envelope',
						'display_order'      => 5,
					],
				],
			],
			[
				'name'  => 'Footer',
				'items' => [
					[
						'type'               => 'Content Page',
						'page_id'            => 1,
						'label_language_key' => 'home',
						'display_order'      => 1,
					],
					[
						'uri'                => config('blogs.base_uri'),
						'subdomain'          => config('blogs.subdomain'),
						'label_language_key' => 'singular:blog',
						'display_order'      => 2,
					],
					[
						'uri'                => config('media.base_uri'),
						'subdomain'          => config('media.subdomain'),
						'label_language_key' => 'media',
						'display_order'      => 3,
					],
					[
						'type'               => 'Content Page',
						'page_id'            => 2,
						'label_language_key' => 'about',
						'display_order'      => 4,
					],
					[
						'type'               => 'Content Page',
						'page_id'            => 3,
						'label_language_key' => 'contact',
						'display_order'      => 5,
					],
				],
			],
		];

		foreach ($menus as $menu)
		{
			$items = isset($menu['items']) ? $menu['items'] : [];

			if (isset($menu['items']))
				unset($menu['items']);

			$menu['created_at'] = $this->timestamp;
			$menu['updated_at'] = $this->timestamp;

			$id = DB::table('menus')->insertGetId($menu);

			$this->insertMenuItems($items, $id);
		}
	}

	/**
	 * Insert menu items.
	 *
	 * @return void
	 */
	private function insertMenuItems($items, $menuId, $parentId = null)
	{
		foreach ($items as $item)
		{
			$subItems = isset($item['items']) ? $item['items'] : [];

			if (isset($item['items']))
				unset($item['items']);

			if (!isset($item['uri']) || $item['uri'] == false)
				$item['uri'] = "";

			if (!isset($item['subdomain']) || $item['subdomain'] == false)
				$item['subdomain'] = "";

			$item['menu_id']    = $menuId;
			$item['parent_id']  = $parentId;
			$item['active']     = true;
			$item['created_at'] = $this->timestamp;
			$item['updated_at'] = $this->timestamp;

			$id = DB::table('menu_items')->insertGetId($item);

			$this->insertMenuItems($subItems, $menuId, $id);
		}
	}

}