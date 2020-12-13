<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomersC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('customers', function (Blueprint $table) {
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('default_billing_address_id')->nullable();
			$table->unsignedInteger('default_shipping_address_id')->nullable();
			$table->unsignedInteger('default_shipping_method_id')->nullable();
			$table->unsignedInteger('default_payment_mode_id')->nullable();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('default_billing_address_id')->references('id')->on('addresses')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('default_shipping_address_id')->references('id')->on('addresses')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('default_shipping_method_id')->references('id')->on('shipping_methods')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('default_payment_mode_id')->references('id')->on('payment_modes')->onDelete('SET NULL')->onUpdate('cascade');

			$table->unique(["user_id"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('customers');
	}
}
