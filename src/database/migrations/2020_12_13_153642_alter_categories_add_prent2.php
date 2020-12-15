<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCategoriesAddPrent2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (Schema::hasTable('items')) {
			Schema::table('items', function (Blueprint $table) {
				$table->unsignedInteger('image_id')->nullable()->after('seo_name');
				$table->string('page_title', 255)->nullable()->after('seo_name');
				$table->string('meta_description', 255)->nullable()->after('short_description');
				$table->string('meta_keywords', 255)->nullable()->after('meta_description');
				$table->foreign('image_id')->references('id')->on('attachments')->onDelete('SET NULL')->onUpdate('cascade');
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
		if (Schema::hasTable('items')) {
			Schema::table('items', function (Blueprint $table) {
				$table->dropForeign('items_image_id_foreign');
				$table->dropColumn('image_id');
				$table->dropColumn('page_title');
				$table->dropColumn('meta_description');
				$table->dropColumn('meta_keywords');
			});
		}
    }
}
