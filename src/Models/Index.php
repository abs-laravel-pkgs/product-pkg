<?php

namespace Abs\ProductPkg\Models;
use Abs\CompanyPkg\Traits\CompanyableTrait;
use App\Models\BaseModel;
use App\Models\Config;

class Index extends BaseModel {
	use CompanyableTrait;
	protected $table = 'indexes';
	public $timestamps = false;
	protected $fillable = [
		'url',
		'page_type_id',
		'company_id'
	];

	public static function createFromObject($record_data, $company = null) {

		$errors = [];
		if (!$company) {
			$company = Company::where('code', $record_data->company_code)->first();
		}
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company_code);
			return;
		}

		// $admin = $company->admin();
		// if (!$admin) {
		// 	dump('Default Admin user not found');
		// 	return;
		// }

		$type = Config::where('name', $record_data->type)->where('config_type_id', 2)->first();
		if (!$type) {
			$errors[] = 'Invalid type : ' . $record_data->type;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'url' => $record_data->url,
		]);
		$record->page_type_id = $type->id;
		$record->save();
		return $record;
	}
}
