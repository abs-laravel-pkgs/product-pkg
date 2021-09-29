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
  protected $visible = [
    'id',
    'key',
    'url',
    'pageType',
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

	// Relationships -----------------------------

  public function pageType(){
	  return $this->belongsTo(Config::class, 'page_type_id');
  }


  // Relationships to auto load
  public static function relationships($action = '', $format = ''): array
  {
    $relationships = [];

    if ($action === 'index') {
//      $relationships = array_merge($relationships, [
//        'mainCategory',
//        'image',
//      ]);
    }
    else if ($action === 'read') {
//      $relationships = array_merge($relationships, [
//        'packageType',
//        'image',
//        'manufacturer',
//        'activeSubstance',
//        'mainCategory',
//        'parent',
//        'tags',
//      ]);
    }
    else if ($action === 'options') {
      $relationships = array_merge($relationships, [
      ]);
    }

    return $relationships;
  }

}
