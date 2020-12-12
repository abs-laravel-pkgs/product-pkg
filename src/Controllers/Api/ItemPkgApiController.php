<?php

namespace Abs\ProductPkg\Controllers\Api;

use Abs\ProductPkg\Models\Category;
use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;
use Abs\ProductPkg\Models\Item;

class ItemPkgApiController extends BaseController {
	use CrudTrait;
	public $model = Item::class;
}
