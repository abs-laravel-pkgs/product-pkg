<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('item_images')) {
			Schema::create('item_images', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('item_id');
				$table->unsignedInteger('image_id');
				$table->boolean('primary')->nullable();

				$table->foreign('item_id')->references('id')->on('items')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('image_id')->references('id')->on('attachments')->onDelete('CASCADE')->onUpdate('cascade');

				$table->unique(["item_id","image_id"]);
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
		Schema::dropIfExists('item_images');
    }
}
