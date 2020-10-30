<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ItemsU2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('items', function (Blueprint $table) {
			$table->unsignedInteger('company_id')->nullable()->after('id');
			$table->string('seo_name',191)->nullable()->after('name');

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');

			$table->unique(["company_id", "seo_name"]);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('items', function (Blueprint $table) {
			$table->dropForeign('items_company_id_foreign');
			$table->dropUnique('items_company_id_seo_name_unique');
			$table->dropColumn('seo_name');
			$table->dropColumn('company_id');
		});
    }
}
