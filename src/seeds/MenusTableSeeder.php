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

		$menus = array(
			array(
				'name'  => 'CMS Main',
				'cms'   => true,

				'items' => array(
					array(
						'uri'           => '',
						'label'         => 'Content',
						'icon'          => 'th-list',
						'display_order' => 1,
						'auth_status'   => 1,

						'items'         => array(
							array(
								'uri'           => 'menus',
								'label'         => 'Menus',
								'icon'          => 'tasks',
								'display_order' => 1,
								'auth_status'   => 1,
							),

							array(
								'uri'           => 'pages',
								'label'         => 'Pages',
								'icon'          => 'file',
								'display_order' => 2,
								'auth_status'   => 1,
							),

							array(
								'uri'           => 'files',
								'label'         => 'Files',
								'icon'          => 'folder-open',
								'display_order' => 3,
								'auth_status'   => 1,
							),

							array(
								'uri'           => 'media/items',
								'label'         => 'Media',
								'icon'          => 'folder-open',
								'display_order' => 4,
								'auth_status'   => 1,
							),

							array(
								'uri'           => 'blogs/articles',
								'label'         => 'Blog',
								'icon'          => 'file',
								'display_order' => 5,
								'auth_status'   => 1,
							),
						),
					),

					array(
						'uri'           => 'users',
						'label'         => 'Users',
						'icon'          => 'user',
						'display_order' => 2,
						'auth_status'   => 1,
						'auth_roles'    => 'admin',

						'items'         => array(
							array(
								'uri'           => 'users',
								'label'         => 'Users',
								'icon'          => 'user',
								'display_order' => 1,
								'auth_status'   => 1,
								'auth_roles'    => 'admin',
							),

							array(
								'uri'           => 'users/roles',
								'label'         => 'User Roles',
								'icon'          => 'book',
								'display_order' => 2,
								'auth_status'   => 1,
								'auth_roles'    => 'admin',
							),

							array(
								'uri'           => 'users/activity',
								'label'         => 'User Activity',
								'icon'          => 'info-sign',
								'display_order' => 3,
								'auth_status'   => 1,
								'auth_roles'    => 'admin',
							),
						),
					),

					array(
						'uri'           => 'settings',
						'label'         => 'Settings',
						'icon'          => 'cog',
						'display_order' => 4,
						'auth_status'   => 1,
						'auth_roles'    => 'admin',
					),
				),
			),

			array(
				'name'  => 'CMS Account',
				'cms'   => true,

				'items' => array(
					array(
						'uri'           => 'login',
						'label'         => 'Log In',
						'icon'          => 'log-in',
						'display_order' => 1,
						'auth_status'   => 2,
					),

					array(
						'uri'           => 'account',
						'label'         => 'Account',
						'icon'          => 'asterisk',
						'display_order' => 2,
						'auth_status'   => 1,
					),

					array(
						'uri'           => 'logout',
						'label'         => 'Log Out',
						'icon'          => 'log-out',
						'display_order' => 3,
						'auth_status'   => 1,
					),
				),
			),

			array(
				'name'  => 'Main',

				'items' => array(
					array(
						'type'          => 'Content Page',
						'page_id'       => 1,
						'label'         => 'Home',
						'icon'          => 'home',
						'display_order' => 1,
					),

					array(
						'uri'           => Config::get('fractal::blog.baseUri'),
						'subdomain'     => Config::get('fractal::blog.subdomain'),
						'label'         => 'Blog',
						'icon'          => 'comment',
						'display_order' => 2,
					),

					array(
						'type'          => 'Content Page',
						'page_id'       => 2,
						'label'         => 'About',
						'icon'          => 'list',
						'display_order' => 3,
					),

					array(
						'type'          => 'Content Page',
						'page_id'       => 3,
						'label'         => 'Contact',
						'icon'          => 'envelope',
						'display_order' => 4,
					),
				),
			),

			array(
				'name'  => 'Footer',

				'items' => array(
					array(
						'type'          => 'Content Page',
						'page_id'       => 1,
						'label'         => 'Home',
						'display_order' => 1,
					),

					array(
						'uri'           => Config::get('fractal::blog.baseUri'),
						'subdomain'     => Config::get('fractal::blog.subdomain'),
						'label'         => 'Blog',
						'display_order' => 2,
					),

					array(
						'type'          => 'Content Page',
						'page_id'       => 2,
						'label'         => 'About',
						'display_order' => 3,
					),
					array(
						'type'          => 'Content Page',
						'page_id'       => 3,
						'label'         => 'Contact',
						'display_order' => 4,
					),
				),
			),
		);

		foreach ($menus as $menu) {
			$items = isset($menu['items']) ? $menu['items'] : array();

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
			$subItems = isset($item['items']) ? $item['items'] : array();

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