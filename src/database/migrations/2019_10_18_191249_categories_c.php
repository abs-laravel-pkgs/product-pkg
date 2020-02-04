<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CategoriesC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('categories', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->string('name', 191);
			$table->unsignedMediumInteger('display_order')->default(9999);
			$table->string('seo_name', 191);
			$table->string('page_title', 255)->nullable();
			$table->string('meta_description', 255)->nullable();
			$table->string('meta_keywords', 255)->nullable();
			$table->text('description')->nullable();
			$table->text('usage')->nullable();
			$table->unsignedInteger('package_type_id')->nullable();
			$table->unsignedInteger('image_id')->nullable();
			$table->unsignedInteger('manufacturer_id')->nullable();
			$table->unsignedInteger('active_substance_id')->nullable();
			$table->unsignedDecimal('customer_rating', 3, 2);
			$table->unsignedInteger('main_category_id')->nullable();
			$table->decimal('starts_at', 12, 2);
			$table->boolean('has_free');
			$table->boolean('has_free_shipping');
			$table->boolean('is_best_selling')->default(0);
			$table->unsignedInteger('created_by_id')->nullable();
			$table->unsignedInteger('updated_by_id')->nullable();
			$table->unsignedInteger('deleted_by_id')->nullable();
			$table->timestamps();
			$table->softdeletes();

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('package_type_id')->references('id')->on('entities')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('image_id')->references('id')->on('attachments')->onDelete('SET NULL')->onUpdate('cascade');
			// $table->foreign('drug_category_id')->references('id')->on('entities')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('manufacturer_id')->references('id')->on('entities')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('active_substance_id')->references('id')->on('entities')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('main_category_id')->references('id')->on('main_categories')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

			$table->unique(["company_id", "name"]);
			$table->unique(["company_id", "seo_name"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('categories');
	}
}
