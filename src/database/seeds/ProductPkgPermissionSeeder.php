<?php
namespace Abs\ProductPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class ProductPkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//FAQ
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'items',
				'display_name' => 'Items',
			],
			[
				'display_order' => 1,
				'parent' => 'items',
				'name' => 'add-item',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'items',
				'name' => 'delete-item',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'items',
				'name' => 'delete-item',
				'display_name' => 'Delete',
			],

		];
		Permission::createFromArrays($permissions);
	}
}