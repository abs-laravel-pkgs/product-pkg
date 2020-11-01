<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WishlistsC extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('wishlists')) {
			Schema::create('wishlists', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('company_id');
				$table->string('wishable_type',255);
				$table->unsignedInteger('wishable_id');
				$table->unsignedInteger('user_id');
				$table->timestamps();

				$table->foreign('company_id')->references('id')->on('companies')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE')->onUpdate('cascade');

				$table->dropForeign('wishlists_company_id_foreign');
				$table->dropForeign('wishlists_user_id_foreign');
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
		Schema::dropIfExists('wishlists');
    }
}
