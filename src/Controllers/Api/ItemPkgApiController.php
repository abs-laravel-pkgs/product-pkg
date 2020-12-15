<?php

namespace Abs\ProductPkg\Controllers\Api;

use Abs\BasicPkg\Classes\ApiResponse;
use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;
use App\Models\Index;
use App\Models\Masters\Category;
use App\Models\Masters\Item;
use Auth;

class ItemPkgApiController extends BaseController {
	use CrudTrait;
	public $model = Item::class;


	public function afterSave($Model, $isNew, $input, ApiResponse $response): void
	{
		$index = Index::firstOrNew([
			'url' => $input['seo_name'],
			'page_type_id' => Item::$PAGE_TYPE_CONFIG_ID,
			'company_id' => Auth::user()->company_id,
		]);
		$index->save();
	}

}
