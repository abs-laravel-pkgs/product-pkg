<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ItemsU1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('items', function (Blueprint $table) {
			$table->string('name',191)->nullable()->after('category_id');
			$table->string('package_size',16)->nullable()->change();
			$table->unsignedDecimal('per_qty_price',5,2)->nullable()->change();
			$table->boolean('has_free')->default(0)->change();
			$table->unsignedDecimal('free_qty',5,2)->nullable()->change();
			$table->boolean('has_free_shipping')->default(0)->change();
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
			$table->dropColumn('name');
		});
    }
}
