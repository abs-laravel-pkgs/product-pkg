<?php

namespace Abs\ProductPkg;

use Abs\Basic\Traits\BasicTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MainCategory extends Model {
	use SoftDeletes;
	use BasicTrait;
	protected $table = 'main_categories';
	protected $fillable = [
		'name',
		'display_order',
		'seo_name',
	];

	public function categories() {
		return $this->hasMany('App\Category')->orderBy('display_order');
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

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->main_category_name,
		]);
		$record->display_order = $record_data->display_order;
		$record->seo_name = $record_data->seo_name;
		$record->created_by_id = $admin->id;
		if ($record_data->status != 1) {
			$record->deleted_at = date('Y-m-d');
		}
		$record->save();
	}

}
