<?php

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
								'label_language_key' => 'menus',
								'icon'               => 'tasks',
								'display_order'      => 1,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'pages',
								'label'              => 'Pages',
								'label_language_key' => 'pages',
								'icon'               => 'file',
								'display_order'      => 2,
								'auth_status'        => 1,
							],
							[
								'uri'                => 'files',
								'label'              => 'Files',
								'label_language_key' => 'files',
								'icon'               => 'folder-open',
								'display_order'      => 3,
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
								'label_language_key' => 'items',
								'icon'               => 'folder-open',
								'display_order'      => 1,
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
								'label_language_key' => 'articles',
								'icon'               => 'file',
								'display_order'      => 1,
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
								'label_language_key' => 'users',
								'icon'               => 'user',
								'display_order'      => 1,
								'auth_status'        => 1,
								'auth_roles'         => 'admin',
							],
							[
								'uri'                => 'users/roles',
								'label'              => 'User Roles',
								'label_language_key' => 'roles',
								'icon'               => 'book',
								'display_order'      => 2,
								'auth_status'        => 1,
								'auth_roles'         => 'admin',
							],
							[
								'uri'                => 'users/permissions',
								'label'              => 'User Permissions',
								'label_language_key' => 'permissions',
								'icon'               => 'star',
								'display_order'      => 2,
								'auth_status'        => 1,
								'auth_roles'         => 'admin',
							],
							[
								'uri'                => 'users/activity',
								'label'              => 'User Activity',
								'label_language_key' => 'activity',
								'icon'               => 'info-sign',
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
						'label_language_key' => 'logIn',
						'icon'               => 'log-in',
						'display_order'      => 1,
						'auth_status'        => 2,
					],
					[
						'uri'                => 'account',
						'label'              => 'Account',
						'label_language_key' => 'account',
						'icon'               => 'asterisk',
						'display_order'      => 2,
						'auth_status'        => 1,
					],
					[
						'uri'                => 'logout',
						'label'              => 'Log Out',
						'label_language_key' => 'logOut',
						'icon'               => 'log-out',
						'display_order'      => 3,
						'auth_status'        => 1,
					],
				],
			],
			[
				'name'  => 'Main',

				'items' => [
					[
						'type'          => 'Content Page',
						'page_id'       => 1,
						'label'         => 'Home',
						'icon'          => 'home',
						'display_order' => 1,
					],
					[
						'uri'           => Config::get('fractal::blog.baseUri'),
						'subdomain'     => Config::get('fractal::blog.subdomain'),
						'label'         => 'Blog',
						'icon'          => 'comment',
						'display_order' => 2,
					],
					[
						'uri'           => Config::get('fractal::media.baseUri'),
						'subdomain'     => Config::get('fractal::media.subdomain'),
						'label'         => 'Media',
						'icon'          => 'book',
						'display_order' => 3,
					],
					[
						'type'          => 'Content Page',
						'page_id'       => 2,
						'label'         => 'About',
						'icon'          => 'list',
						'display_order' => 4,
					],
					[
						'type'          => 'Content Page',
						'page_id'       => 3,
						'label'         => 'Contact',
						'icon'          => 'envelope',
						'display_order' => 5,
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
						'uri'           => Config::get('fractal::blog.baseUri'),
						'subdomain'     => Config::get('fractal::blog.subdomain'),
						'label'         => 'Blog',
						'display_order' => 2,
					],
					[
						'uri'           => Config::get('fractal::media.baseUri'),
						'subdomain'     => Config::get('fractal::media.subdomain'),
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

		foreach ($menus as $menu) {
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
		foreach ($items as $item) {
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