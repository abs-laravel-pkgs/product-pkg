<?php

namespace Abs\ProductPkg\Controllers\Api;

use Abs\ProductPkg\Models\Category;
use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;

class CategoryTagPkgApiController extends BaseController {
	use CrudTrait;
	public $model = Category::class;
}
