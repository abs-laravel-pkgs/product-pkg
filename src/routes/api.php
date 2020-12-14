<?php

use App\Http\Controllers\Api\Masters\CategoryApiController;
use App\Http\Controllers\Api\Masters\ItemApiController;

Route::group(['middleware' => ['api','auth:api']], function () {

	Route::group(['prefix' => '/api/category'], function () {
		$className = CategoryApiController::class;
		Route::get('index', $className . '@index');
		Route::get('read/{id}', $className . '@read');
		Route::post('save', $className . '@save');
		Route::get('options', $className . '@options');
		Route::get('delete/{id}', $className . '@delete');
	});

	Route::group(['prefix' => '/api/item'], function () {
		$className = ItemApiController::class;
		Route::get('index', $className . '@index');
		Route::get('read/{id}', $className . '@read');
		Route::post('save', $className . '@save');
		Route::get('options', $className . '@options');
		Route::get('delete/{id}', $className . '@delete');
	});
});
