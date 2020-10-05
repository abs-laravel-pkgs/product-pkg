<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CategoryTagsC extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('category_tags')) {
			Schema::create('category_tags', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('category_id');
				$table->unsignedInteger('tag_id');

				$table->foreign('category_id')->references('id')->on('categories')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('tag_id')->references('id')->on('tags')->onDelete('CASCADE')->onUpdate('cascade');

				$table->unique(["category_id","tag_id"]);
			});
		}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::dropIfExists('category_tags');
    }
}
