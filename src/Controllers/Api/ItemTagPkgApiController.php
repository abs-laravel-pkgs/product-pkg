<?php

namespace Abs\ProductPkg\Controllers\Api;

use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;
use Abs\ProductPkg\Models\ItemTag;

class ItemTagPkgApiController extends BaseController {
	use CrudTrait;
	public $model = ItemTag::class;
}
