<?php

namespace Abs\ProductPkg\Models;

use Abs\BasicPkg\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Strength extends BaseModel {
	use SoftDeletes;
	protected $table = 'strengths';
	protected $fillable = [
		'name',
		'type_id',
		'display_order',
	];

	public function type() {
		return $this->belongsTo('Abs\BasicPkg\Entity', 'type_id')->where('entity_type_id', 5);
	}

	public function items($category_id) {
		return Item::where('strength_id', $this->id)->where('category_id', $category_id)->get();
	}

	public function getFullNameAttribute($category_id) {
		return $this->name . ' ' . $this->type->name;
	}

	public static function createFromObject($record_data, $company = null) {

		$errors = [];
		if (!$company) {
			$company = Company::where('code', $record_data->company_code)->first();
		}
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company_code);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$strength_type = Entity::where([
			'company_id' => $company->id,
			'name' => $record_data->strength_type,
			'entity_type_id' => 5,
		])->first();
		if (!$strength_type) {
			dump('Invalid strength_type : ' . $record_data->strength_type);
			return;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->strength_name,
			'type_id' => $strength_type->id,
		]);
		$record->display_order = $record_data->display_order;
		if ($record_data->status != 1) {
			$record->deleted_at = date('Y-m-d');
		}
		$record->save();
	}

}
