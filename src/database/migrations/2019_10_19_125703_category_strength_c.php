<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CategoryStrengthC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('category_strength', function (Blueprint $table) {
			$table->unsignedInteger('category_id');
			$table->unsignedInteger('strength_id');
			$table->unsignedInteger('display_order')->default(9999);

			$table->foreign('category_id')->references('id')->on('categories')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('strength_id')->references('id')->on('strengths')->onDelete('CASCADE')->onUpdate('cascade');

			$table->unique(["category_id", "strength_id"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('category_strength');
	}
}
