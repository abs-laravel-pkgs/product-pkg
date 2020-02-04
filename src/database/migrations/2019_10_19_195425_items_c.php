<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ItemsC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('shipping_methods', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->string('name', 191);
			$table->string('delivery_time', 255)->nullable();
			$table->unsignedInteger('logo_id')->nullable();
			$table->unsignedDecimal('charge', 8, 2)->default(0);
			$table->unsignedInteger('created_by_id')->nullable();
			$table->unsignedInteger('updated_by_id')->nullable();
			$table->unsignedInteger('deleted_by_id')->nullable();
			$table->timestamps();
			$table->softdeletes();

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('logo_id')->references('id')->on('attachments')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

			$table->unique(["company_id", "name"]);
		});

		Schema::create('items', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('category_id')->nullable();
			$table->unsignedInteger('strength_id')->nullable();
			$table->string('package_size', 16);
			$table->unsignedMediumInteger('display_order')->default(9999);
			$table->unsignedDecimal('regular_price', 8, 2);
			$table->unsignedDecimal('special_price', 8, 2);
			$table->unsignedDecimal('per_qty_price', 5, 2);
			$table->boolean('has_free');
			$table->unsignedDecimal('free_qty', 5, 2);
			$table->boolean('has_free_shipping');
			$table->unsignedInteger('shipping_method_id')->nullable();
			$table->unsignedInteger('created_by_id')->nullable();
			$table->unsignedInteger('updated_by_id')->nullable();
			$table->unsignedInteger('deleted_by_id')->nullable();
			$table->timestamps();
			$table->softdeletes();

			$table->foreign('category_id')->references('id')->on('categories')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('strength_id')->references('id')->on('strengths')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('shipping_method_id')->references('id')->on('shipping_methods')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

			$table->unique(["category_id", "strength_id", "package_size"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('products');
	}
}
