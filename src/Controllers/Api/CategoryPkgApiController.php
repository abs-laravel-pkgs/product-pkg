<?php

namespace Abs\ProductPkg\Controllers\Api;


use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;
use App\Models\Masters\Category;

class CategoryPkgApiController extends BaseController {
	use CrudTrait;
	public $model = Category::class;
}
