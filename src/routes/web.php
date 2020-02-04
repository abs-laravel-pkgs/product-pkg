<?php

Route::group(['namespace' => 'Abs\ProductPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'product-pkg'], function () {
	//FAQs
	Route::get('/items/get-list', 'ItemController@getItemList')->name('getItemList');
	Route::get('/item/get-form-data', 'ItemController@getItemFormData')->name('getItemFormData');
	Route::post('/item/save', 'ItemController@saveItem')->name('saveItem');
	Route::get('/item/delete/{id}', 'ItemController@deleteItem')->name('deleteItem');
});

Route::group(['namespace' => 'Abs\ProductPkg', 'middleware' => ['web'], 'prefix' => 'product-pkg'], function () {
	//FAQs
	Route::get('/items/get', 'ItemController@getItems')->name('getItems');
});
