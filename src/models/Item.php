<?php

namespace Abs\ProductPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model {
	use SeederTrait;
	use SoftDeletes;
	protected $table = 'items';
	public $timestamps = true;
	protected $fillable = [
		'category_id',
		'strength_id',
		'package_size',
		'display_order',
		'regular_price',
		'special_price',
	];

	public function mainCategory() {
		return $this->belongsTo('Abs\ProductPkg\MainCategory');
	}

	public function category() {
		return $this->belongsTo('Abs\ProductPkg\Category');
	}

	public function strengths() {
		return $this->belongsTo('Abs\ProductPkg\Strength', 'strength_id');
	}

	public function shippingMethods() {
		return $this->belongsTo('Abs\ShippingMethodPkg\ShippingMethod', 'shipping_method_id');
	}

	public static function createFromObject($record_data) {

		$errors = [];
		$company = Company::where('code', $record_data->company)->first();
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$type = Config::where('name', $record_data->type)->where('config_type_id', 89)->first();
		if (!$type) {
			$errors[] = 'Invalid Tax Type : ' . $record_data->type;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->tax_name,
		]);
		$record->type_id = $type->id;
		$record->created_by_id = $admin->id;
		$record->save();
		return $record;
	}

}
