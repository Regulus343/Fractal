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

		$menus = [
			[
				'name'  => 'CMS Main',
				'cms'   => true,

				'items' => [
					[
						'uri'           => '',
						'label'         => 'Content',
						'icon'          => 'th-list',
						'display_order' => 1,
						'auth_status'   => 1,

						'items'         => [
							[
								'uri'                => 'menus',
								'label'              => 'Menus',
								'label_language_key' => 'plural:menu',
								'icon'               => 'tasks',
								'display_order'      => 1,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'pages',
								'label'              => 'Pages',
								'label_language_key' => 'plural:page',
								'icon'               => 'file',
								'display_order'      => 2,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'files',
								'label'              => 'Files',
								'label_language_key' => 'plural:file',
								'icon'               => 'folder',
								'display_order'      => 3,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'layout-templates',
								'label'              => 'Layout Templates',
								'label_language_key' => 'plural:layout_template',
								'icon'               => 'th',
								'display_order'      => 4,
								'auth_status'        => 1,
							],
						],
					],
					[
						'uri'           => 'media',
						'label'         => 'Media',
						'icon'          => 'th-list',
						'display_order' => 2,
						'auth_status'   => 1,

						'items'         => [
							[
								'uri'                => 'media/items',
								'label'              => 'Media Items',
								'label_language_key' => 'plural:item',
								'icon'               => 'file-image-o',
								'display_order'      => 3,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'media/types',
								'label'              => 'Media Types',
								'label_language_key' => 'plural:type',
								'icon'               => 'tag',
								'display_order'      => 3,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'media/sets',
								'label'              => 'Media Sets',
								'label_language_key' => 'plural:set',
								'icon'               => 'folder',
								'display_order'      => 3,
								'auth_status'        => 1,
							],
						],
					],
					[
						'uri'           => 'blogs',
						'label'         => 'Blogs',
						'icon'          => 'th-list',
						'display_order' => 2,
						'auth_status'   => 1,

						'items'         => [
							[
								'uri'                => 'blogs/articles',
								'label'              => 'Blog Articles',
								'label_language_key' => 'plural:article',
								'icon'               => 'file',
								'display_order'      => 1,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'blogs/categories',
								'label'              => 'Categories',
								'label_language_key' => 'plural:category',
								'icon'               => 'book',
								'display_order'      => 2,
								'auth_status'        => 1,
							],
						],
					],
					[
						'uri'           => 'users',
						'label'         => 'Users',
						'icon'          => 'user',
						'display_order' => 2,
						'auth_status'   => 1,
						'auth_roles'    => 'admin',

						'items'         => [
							[
								'uri'                => 'users',
								'label'              => 'Users',
								'label_language_key' => 'plural:user',
								'icon'               => 'user',
								'display_order'      => 1,
								'auth_status'        => 1,
								'auth_roles'         => 'admin',
							],
							[
								'uri'                => 'users/roles',
								'label'              => 'User Roles',
								'label_language_key' => 'plural:role',
								'icon'               => 'book',
								'display_order'      => 2,
								'auth_status'        => 1,
								'auth_roles'         => 'admin',
							],
							[
								'uri'                => 'users/permissions',
								'label'              => 'User Permissions',
								'label_language_key' => 'plural:permission',
								'icon'               => 'star',
								'display_order'      => 2,
								'auth_status'        => 1,
								'auth_roles'         => 'admin',
							],
							[
								'uri'                => 'users/activity',
								'label'              => 'User Activity',
								'label_language_key' => 'singular:activity',
								'icon'               => 'info-circle',
								'display_order'      => 3,
								'auth_status'        => 1,
								'auth_roles'         => 'admin',
							],
						],
					],
					[
						'uri'                => 'settings',
						'label'              => 'Settings',
						'label_language_key' => 'settings',
						'icon'               => 'cog',
						'display_order'      => 4,
						'auth_status'        => 1,
						'auth_roles'         => 'admin',
					],
				],
			],
			[
				'name'  => 'CMS Account',
				'cms'   => true,

				'items' => [
					[
						'uri'                => 'login',
						'label'              => 'Log In',
						'label_language_key' => 'log_in',
						'icon'               => 'sign-in',
						'display_order'      => 1,
						'auth_status'        => 2,
					],
					[
						'uri'                => 'account',
						'label'              => 'Account',
						'label_language_key' => 'account',
						'icon'               => 'user',
						'display_order'      => 2,
						'auth_status'        => 1,
					],
					[
						'uri'                => 'logout',
						'label'              => 'Log Out',
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
						'label'              => 'Home',
						'icon'               => 'home',
						'display_order'      => 1,
					],
					[
						'uri'                => config('blogs.base_uri'),
						'subdomain'          => config('blogs.subdomain'),
						'label'              => 'Blog',
						'icon'               => 'comment',
						'display_order'      => 2,
					],
					[
						'uri'                => config('media.base_uri'),
						'subdomain'          => config('media.subdomain'),
						'label'              => 'Media',
						'icon'               => 'book',
						'display_order'      => 3,
					],
					[
						'type'               => 'Content Page',
						'page_id'            => 2,
						'label'              => 'About',
						'icon'               => 'list',
						'display_order'      => 4,
					],
					[
						'type'               => 'Content Page',
						'page_id'            => 3,
						'label'              => 'Contact',
						'icon'               => 'envelope',
						'display_order'      => 5,
					],
				],
			],
			[
				'name'  => 'Footer',

				'items' => [
					[
						'type'          => 'Content Page',
						'page_id'       => 1,
						'label'         => 'Home',
						'display_order' => 1,
					],
					[
						'uri'           => config('blogs.base_uri'),
						'subdomain'     => config('blogs.subdomain'),
						'label'         => 'Blog',
						'display_order' => 2,
					],
					[
						'uri'           => config('media.base_uri'),
						'subdomain'     => config('media.subdomain'),
						'label'         => 'Media',
						'display_order' => 3,
					],
					[
						'type'          => 'Content Page',
						'page_id'       => 2,
						'label'         => 'About',
						'display_order' => 4,
					],
					[
						'type'          => 'Content Page',
						'page_id'       => 3,
						'label'         => 'Contact',
						'display_order' => 5,
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