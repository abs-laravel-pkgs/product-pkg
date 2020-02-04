<?php
Route::group(['namespace' => 'Abs\ProductPkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'product-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});