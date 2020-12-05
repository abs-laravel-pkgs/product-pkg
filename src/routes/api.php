<?php

use App\Http\Controllers\Api\Masters\CategoryController;

Route::group(['middleware' => ['api']], function () {
	Route::group(['prefix' => '/api/master/category'], function () {
		$className = CategoryController::class;
		Route::get('index', $className.'@index');
		Route::get('read/{id}', $className.'@read');
		Route::post('save', $className.'@save');
		Route::get('options', $className.'@options');
		Route::get('delete/{category}', $className.'@delete');
	});
});
