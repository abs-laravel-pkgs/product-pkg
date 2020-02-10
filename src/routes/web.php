<?php

Route::group(['namespace' => 'Abs\ProductPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'product-pkg'], function () {
	//Main Categories
	Route::get('/main-categories/get-list', 'MainCategoryController@getMainCategoryList')->name('getMainCategoryList');
	Route::get('/main-category/get-form-data', 'MainCategoryController@getMainCategoryFormData')->name('getMainCategoryFormData');
	Route::post('/main-category/save', 'MainCategoryController@saveMainCategory')->name('saveMainCategory');
	Route::get('/main-category/delete', 'MainCategoryController@deleteMainCategory')->name('deleteMainCategory');

	//Categories
	Route::get('/categories/get-list', 'CategoryController@getCategoryList')->name('getCategoryList');
	Route::get('/category/get-form-data', 'CategoryController@getCategoryFormData')->name('getCategoryFormData');
	Route::post('/category/save', 'CategoryController@saveCategory')->name('saveCategory');
	Route::get('/category/delete', 'CategoryController@deleteCategory')->name('deleteCategory');

	//Strengths
	Route::get('/strengths/get-list', 'StrengthController@getStrengthList')->name('getStrengthList');
	Route::get('/strength/get-form-data', 'StrengthController@getStrengthFormData')->name('getStrengthFormData');
	Route::post('/strength/save', 'StrengthController@saveStrength')->name('saveStrength');
	Route::get('/strength/delete', 'StrengthController@deleteStrength')->name('deleteStrength');

	//Items
	Route::get('/items/get-list', 'ItemController@getItemList')->name('getItemList');
	Route::get('/item/get-form-data', 'ItemController@getItemFormData')->name('getItemFormData');
	Route::get('/item/get-category-list', 'ItemController@getCategory')->name('getCategory');
	Route::post('/item/save', 'ItemController@saveItem')->name('saveItem');
	Route::get('/item/delete', 'ItemController@deleteItem')->name('deleteItem');
});

Route::group(['namespace' => 'Abs\ProductPkg', 'middleware' => ['web'], 'prefix' => 'product-pkg'], function () {
	//Items
	Route::get('/items/get', 'ItemController@getItems')->name('getItems');
});
