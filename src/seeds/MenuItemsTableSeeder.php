<?php

class MenuItemsTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('menu_items')->truncate();

		$timestamp = date('Y-m-d H:i:s');
		$menuItems = array(
			//CMS Main Menu Items
			array(
				'menu_id'       => 1,
				'label'         => 'Home',
				'icon'          => 'home',
				'display_order' => 1,
				'auth_status'   => 1,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 1,
				'uri'           => '',
				'label'         => 'Content',
				'icon'          => 'th-list',
				'display_order' => 2,
				'auth_status'   => 1,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 1,
				'parent_id'     => 2,
				'uri'           => 'menus',
				'label'         => 'Menus',
				'icon'          => 'tasks',
				'display_order' => 1,
				'auth_status'   => 1,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 1,
				'parent_id'     => 2,
				'uri'           => 'pages',
				'label'         => 'Pages',
				'icon'          => 'file',
				'display_order' => 2,
				'auth_status'   => 1,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 1,
				'parent_id'     => 2,
				'uri'           => 'files',
				'label'         => 'Files',
				'icon'          => 'folder-open',
				'display_order' => 3,
				'auth_status'   => 1,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 1,
				'uri'           => 'users',
				'label'         => 'Users',
				'icon'          => 'user',
				'display_order' => 3,
				'auth_status'   => 1,
				'auth_roles'    => 'admin',
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 1,
				'parent_id'     => 6,
				'uri'           => 'users',
				'label'         => 'Users',
				'icon'          => 'user',
				'display_order' => 1,
				'auth_status'   => 1,
				'auth_roles'    => 'admin',
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 1,
				'parent_id'     => 6,
				'uri'           => 'user-roles',
				'label'         => 'User Roles',
				'icon'          => 'book',
				'display_order' => 2,
				'auth_status'   => 1,
				'auth_roles'    => 'admin',
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 1,
				'parent_id'     => 6,
				'uri'           => 'activity',
				'label'         => 'User Activity',
				'icon'          => 'info-sign',
				'display_order' => 3,
				'auth_status'   => 1,
				'auth_roles'    => 'admin',
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 1,
				'uri'           => 'settings',
				'label'         => 'Settings',
				'icon'          => 'cog',
				'display_order' => 4,
				'auth_status'   => 1,
				'auth_roles'    => 'admin',
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),

			//CMS Account Menu Items
			array(
				'menu_id'       => 2,
				'uri'           => 'login',
				'label'         => 'Log In',
				'icon'          => 'log-in',
				'display_order' => 1,
				'auth_status'   => 2,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 2,
				'uri'           => 'account',
				'label'         => 'Account',
				'icon'          => 'asterisk',
				'display_order' => 2,
				'auth_status'   => 1,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 2,
				'uri'           => 'logout',
				'label'         => 'Log Out',
				'icon'          => 'log-out',
				'display_order' => 3,
				'auth_status'   => 1,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),

			//Main Menu Items
			array(
				'menu_id'       => 3,
				'page_id'       => 1,
				'label'         => 'Home',
				'icon'          => 'home',
				'display_order' => 1,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 3,
				'page_id'       => 2,
				'label'         => 'About',
				'icon'          => 'list',
				'display_order' => 2,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 3,
				'page_id'       => 3,
				'label'         => 'Contact',
				'icon'          => 'envelope',
				'display_order' => 3,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),

			//Footer Menu Items
			array(
				'menu_id'       => 4,
				'page_id'       => 1,
				'label'         => 'Home',
				'display_order' => 1,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 4,
				'page_id'       => 2,
				'label'         => 'About',
				'display_order' => 2,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
			array(
				'menu_id'       => 4,
				'page_id'       => 3,
				'label'         => 'Contact',
				'display_order' => 3,
				'active'        => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			),
		);

		foreach ($menuItems as $menuItem) {
			DB::table('menu_items')->insert($menuItem);
		}
	}

}