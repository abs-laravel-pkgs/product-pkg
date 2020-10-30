<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ItemRelatedItemC extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('item_related_item')) {
			Schema::create('item_related_item', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('item_id');
				$table->unsignedInteger('related_item_id');

				$table->foreign('item_id')->references('id')->on('items')->onDelete('cascade')->onUpdate('cascade');
				$table->foreign('related_item_id')->references('id')->on('items')->onDelete('cascade')->onUpdate('cascade');

				$table->unique(["item_id","related_item_id"],'item_related_item_uk');
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
		Schema::dropIfExists('item_related_item');
    }
}
