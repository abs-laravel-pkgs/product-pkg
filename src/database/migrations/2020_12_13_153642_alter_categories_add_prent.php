<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCategoriesAddPrent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (Schema::hasTable('categories')) {
			Schema::table('categories', function (Blueprint $table) {
				$table->unsignedInteger('parent_id')->nullable()->after('name');
				$table->foreign('parent_id')->references('id')->on('categories')->onDelete('SET NULL')->onUpdate('cascade');
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
		if (Schema::hasTable('categories')) {
			Schema::table('categories', function (Blueprint $table) {
				$table->dropForeign('categories_parent_id_foreign');
				$table->dropColumn('parent_id');
			});
		}
    }
}
