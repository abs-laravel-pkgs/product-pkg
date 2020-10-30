<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ItemsU3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('items', function (Blueprint $table) {
			$table->string('short_description',255)->nullable()->after('seo_name');
			$table->text('full_description')->nullable()->after('short_description');
			$table->unsignedMediumInteger('rating')->default(0)->after('full_description');
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
			$table->dropColumn('short_description');
			$table->dropColumn('full_description');
			$table->dropColumn('rating');
		});
    }
}
