<?php

use Illuminate\Database\Seeder;

use Regulus\Identify\Facade as Auth;

class FractalPermissionsTableSeeder extends Seeder {

	protected $table;
	protected $displayOrder;
	protected $timestamp;

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->table = Auth::getTableName('permissions');

		DB::table($this->table)->truncate();

		$this->displayOrder = 1;

		$this->timestamp = date('Y-m-d H:i:s');

		$permissions = [
			[
				'permission'   => 'manage',
				'name'         => 'Manage',
				'description'  => 'Full management permissions',
				'access_level' => 1000,
				'children'     => [
					[
						'permission'   => 'manage-content',
						'name'         => 'Manage Content',
						'description'  => 'Manage standard content',
						'access_level' => 900,
						'children'     => [
							[
								'permission'   => 'manage-menus',
								'name'         => 'Manage Menus',
								'description'  => 'Manage menus and menu items',
								'access_level' => 700,
							],
							[
								'permission'   => 'manage-pages',
								'name'         => 'Manage Pages',
								'description'  => 'Manage content pages',
								'access_level' => 700,
							],
							[
								'permission'   => 'manage-files',
								'name'         => 'Manage Files',
								'description'  => 'Manage and upload files',
								'access_level' => 700,
							],
							[
								'permission'   => 'manage-layout-templates',
								'name'         => 'Manage Layout Templates',
								'description'  => 'Manage layout templates for pages and articles',
								'access_level' => 700,
							],
							[
								'permission'   => 'manage-forms',
								'name'         => 'Manage Forms',
								'description'  => 'Manage forms and form records',
								'access_level' => 700,
							],
						],
					],
					[
						'permission'   => 'manage-media',
						'name'         => 'Manage Media',
						'description'  => 'Manage media items, types, and sets',
						'access_level' => 800,
						'children'     => [
							[
								'permission'   => 'manage-media-items',
								'name'         => 'Manage Media Items',
								'description'  => 'Manage media items',
								'access_level' => 700,
							],
							[
								'permission'   => 'manage-media-types',
								'name'         => 'Manage Media Types',
								'description'  => 'Manage media types',
								'access_level' => 700,
							],
							[
								'permission'   => 'manage-media-sets',
								'name'         => 'Manage Media Sets',
								'description'  => 'Manage media sets',
								'access_level' => 700,
							],
						],
					],
					[
						'permission'   => 'manage-blogs',
						'name'         => 'Manage Blogs',
						'description'  => 'Manage blog articles and categories',
						'access_level' => 800,
						'children'     => [
							[
								'permission'   => 'manage-blog-articles',
								'name'         => 'Manage Blog Articles',
								'description'  => 'Manage blog articles',
								'access_level' => 700,
							],
							[
								'permission'   => 'manage-blog-categories',
								'name'         => 'Manage Blog Categories',
								'description'  => 'Manage blog categories',
								'access_level' => 700,
							],
						],
					],
					[
						'permission'   => 'manage-users',
						'name'         => 'Manage Users',
						'description'  => 'Manage users, roles, and permissions',
						'access_level' => 950,
					],
					[
						'permission'  => 'manage-settings',
						'name'        => 'Manage Settings',
						'description' => 'Manage site / app settings',
						'access_level' => 960,
					],
				],
			],
			[
				'permission'  => 'view',
				'name'        => 'View',
				'description' => 'Full viewing permissions',
				'children'    => [
					[
						'permission'   => 'view-content',
						'name'         => 'View Content',
						'description'  => 'View standard content',
						'access_level' => 500,
						'children'     => [
							[
								'permission'   => 'view-menus',
								'name'         => 'View Menus',
								'description'  => 'View menus and menu items',
								'access_level' => 400,
							],
							[
								'permission'   => 'view-pages',
								'name'         => 'View Pages',
								'description'  => 'View content pages',
								'access_level' => 400,
							],
							[
								'permission'   => 'view-files',
								'name'         => 'View Files',
								'description'  => 'View and upload files',
								'access_level' => 400,
							],
							[
								'permission'   => 'view-layout-templates',
								'name'         => 'View Layout Templates',
								'description'  => 'View layout templates for pages and articles',
								'access_level' => 400,
							],
							[
								'permission'   => 'view-forms',
								'name'         => 'View Forms',
								'description'  => 'View forms and form records',
								'access_level' => 400,
							],
						],
					],
					[
						'permission'   => 'view-media',
						'name'         => 'View Media',
						'description'  => 'View media items, types, and sets',
						'access_level' => 500,
						'children'     => [
							[
								'permission'   => 'view-media-items',
								'name'         => 'View Media Items',
								'description'  => 'View media items',
								'access_level' => 400,
							],
							[
								'permission'   => 'view-media-types',
								'name'         => 'View Media Types',
								'description'  => 'View media types',
								'access_level' => 400,
							],
							[
								'permission'   => 'view-media-sets',
								'name'         => 'View Media Sets',
								'description'  => 'View media sets',
								'access_level' => 400,
							],
						],
					],
					[
						'permission'   => 'view-blogs',
						'name'         => 'View Blogs',
						'description'  => 'View blog articles and categories',
						'access_level' => 500,
						'children'     => [
							[
								'permission'   => 'view-blog-articles',
								'name'         => 'View Blog Articles',
								'description'  => 'View blog articles',
								'access_level' => 400,
							],
							[
								'permission'   => 'view-blog-categories',
								'name'         => 'View Blog Categories',
								'description'  => 'View blog categories',
								'access_level' => 400,
							],
						],
					],
					[
						'permission'   => 'view-users',
						'name'         => 'View Users',
						'description'  => 'View users, roles, and permissions',
						'access_level' => 500,
					],
				],
			],
			[
				'permission'   => 'demo',
				'name'         => 'Demonstration Mode',
				'description'  => 'View system with full management access without the ability to actually update records',
				'access_level' => 500,
			],
		];

		foreach ($permissions as $permission)
		{
			$this->addPermission($permission);
		}
	}

	private function addPermission($permission, $parentId = null)
	{
		$permission['parent_id']     = $parentId;
		$permission['display_order'] = $this->displayOrder;
		$permission['created_at']    = $this->timestamp;
		$permission['updated_at']    = $this->timestamp;

		$children = [];
		if (isset($permission['children']) && is_array($permission['children']))
		{
			$children = $permission['children'];

			unset($permission['children']);
		}

		$id = DB::table($this->table)->insertGetId($permission);

		$this->displayOrder ++;

		foreach ($children as $subPermission)
		{
			$this->addPermission($subPermission, $id);
		}
	}

}