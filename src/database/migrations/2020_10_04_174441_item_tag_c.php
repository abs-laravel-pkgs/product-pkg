<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ItemTagC extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

		if (!Schema::hasTable('item_tags')) {
			Schema::create('item_tags', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('item_id');
				$table->unsignedInteger('tag_id');

				$table->foreign('item_id')->references('id')->on('items')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('tag_id')->references('id')->on('tags')->onDelete('CASCADE')->onUpdate('cascade');

				$table->unique(["item_id","tag_id"]);
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
		Schema::dropIfExists('item_tags');
    }
}
