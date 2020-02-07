<?php

namespace Abs\ProductPkg;

use Abs\Basic\Traits\BasicTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model {
	use SoftDeletes;
	use BasicTrait;
	protected $table = 'categories';
	protected $fillable = [
		'name',
		'display_order',
		'description',
		'usage',
		'package_type_id',
		'manufacturer_id',
		'active_substance_id',
		'customer_rating',
		'main_category_id',
		'seo_name',
		'page_title',
		'meta_description',
		'meta_keywords',
	];

	public function strengths() {
		return $this->belongsToMany('App\Strength');
	}

	public function image() {
		return $this->belongsTo('Abs\Basic\Attachment', 'image_id');
	}

	public function items() {
		return $this->hasMany('App\Item');
	}

	public function lowestItem() {
		return $this->hasMany('App\Item')->orderBy('special_price')->first();
	}

	public function mainCategory() {
		return $this->belongsTo('Abs\ProductPkg\MainCategory');
	}

	public function activeSubstance() {
		return $this->belongsTo('Abs\Basic\Entity', 'active_substance_id')->where('entity_type_id', 1);
	}

	public function drugCategory() {
		return $this->belongsTo('Abs\Basic\Entity', 'drug_category_id')->where('entity_type_id', 2);
	}

	public function manufacturer() {
		return $this->belongsTo('Abs\Basic\Entity', 'manufacturer_id')->where('entity_type_id', 3);
	}

	public function packageType() {
		return $this->belongsTo('Abs\Basic\Entity', 'package_type_id')->where('entity_type_id', 4);
	}

	public function scopeBestSelling($query) {
		return $query->where('is_best_selling', 1)->select(
			'id',
			'name',
			'seo_name',
			'description',
			'starts_at',
			'image_id'
		)->with('image');
	}

	public function getImagePathAttribute() {
		// dd(asset('storage/uploads/category/' . $this->id . '/' . $this->image->name));
		return asset('public/uploads/category/small/' . str_replace(' ', '-', $this->name) . '.jpg');
		$image_name = ($this->image) ? $this->image->name : '';
		return asset('storage/uploads/category/' . $this->id . '/' . $image_name);
	}

	public function getBigImageAttribute() {
		return asset('public/uploads/category/big/' . str_replace(' ', '-', $this->name) . '.jpg');
		$image_name = ($this->image) ? $this->image->name : '';
		return asset('storage/uploads/category/' . $this->id . '/' . $image_name);
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

		$main_category = MainCategory::where([
			'name' => $record_data->main_category,
			'company_id' => $company->id,
		])->first();
		if (!$main_category) {
			dump('Invalid main_category : ' . $record_data->main_category);
		}

		$manufacturer_id = null;
		if ($record_data->manufacturer) {
			$manufacturer = Entity::where([
				'company_id' => $company->id,
				'name' => $record_data->manufacturer,
				'entity_type_id' => 3,
			])->first();
			if (!$manufacturer) {
				dump('Invalid manufacturer : ' . $record_data->manufacturer);
				return;
			} else {
				$manufacturer_id = $manufacturer->id;
			}
		}

		$active_substance = Entity::where([
			'company_id' => $company->id,
			'name' => $record_data->active_substance,
			'entity_type_id' => 1,
		])->first();
		if (!$active_substance) {
			dump('Invalid active_substance : ' . $record_data->active_substance);
			return;
		}

		$package_type = Entity::where([
			'company_id' => $company->id,
			'name' => $record_data->package_type,
			'entity_type_id' => 4,
		])->first();
		if (!$package_type) {
			dump('Invalid package_type : ' . $record_data->package_type);
			return;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->category_name,
		]);
		$record->display_order = $record_data->display_order;
		$record->seo_name = $record_data->seo_name;
		$record->page_title = $record_data->page_title;
		$record->meta_description = $record_data->meta_description;
		$record->meta_keywords = $record_data->meta_keywords;
		$record->description = $record_data->description;
		$record->usage = $record_data->usage;
		$record->package_type_id = $package_type->id;
		$record->manufacturer_id = $manufacturer_id;
		$record->active_substance_id = $active_substance->id;
		$record->customer_rating = $record_data->customer_rating;
		$record->main_category_id = $main_category->id;
		$record->starts_at = 0;
		$record->has_free = $record_data->has_free == 'Yes' ? 1 : 0;
		$record->has_free_shipping = $record_data->has_free_shipping == 'Yes' ? 1 : 0;
		$record->is_best_selling = $record_data->is_best_selling == 'Yes' ? 1 : 0;

		$record->created_by_id = $admin->id;
		if ($record_data->status != 1) {
			$record->deleted_at = date('Y-m-d');
		}
		$record->save();

		// $image = Attachment::firstOrNew([
		// 	'attachment_of_id' => 40, //CATEGORY
		// 	'attachment_type_id' => 60, //PRIMARY
		// 	'entity_id' => $record->id,
		// ]);
		// $image->name = str_replace(' ', '-', $record->name) . '.jpg';
		// $image->save();

		// $record->image_id = $image->id;
		// $record->save();

		// $destination = categoryImagePath($record->id);
		// $status = Storage::makeDirectory($destination, 0777);
		// if (!Storage::exists($destination . '/' . $image->name)) {
		// 	$src_file = 'public/product-src-img/01 big/' . $image->name;
		// 	if (Storage::exists($src_file)) {
		// 		Storage::copy($src_file, $destination . '/' . $image->name);
		// 	} else {
		// 		dump('Category Image Src File Note Found : ' . $src_file);
		// 	}
		// }

		$index = Index::firstOrNew([
			'company_id' => $company->id,
			'url' => $record->seo_name,
		]);
		$index->page_type_id = 20; //CATEGORY
		$index->save();

	}

	public static function mapStrengths($records, $company = null, $specific_company = null, $tc) {
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->company_code) {
					continue;
				}
				$record = self::mapStrength($record_data, $company);
			} catch (Exception $e) {
				dd($e);
			}
		}
	}

	public static function mapStrength($record_data, $company = null) {
		if (!$company) {
			$company = Company::where('code', $record_data->company_code)->first();
		}
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company_code);
			return;
		}
		$admin = $company->admin();

		$errors = [];

		$category = Category::where([
			'name' => $record_data->category_name,
			'company_id' => $company->id,
		])->first();
		if (!$category) {
			$errors[] = 'Invalid category : ' . $record_data->category_name;
		}

		$strength_type = Entity::firstOrCreate([
			'company_id' => $company->id,
			'name' => $record_data->strength_type,
			'entity_type_id' => 5,
		]);

		$strength = Strength::firstOrCreate([
			'company_id' => $company->id,
			'name' => $record_data->strength,
			'type_id' => $strength_type->id,
		]);

		if (count($errors) > 0) {
			dump($errors);
			return;
		}
		$category->strengths()->syncWithoutDetaching([
			$strength->id,
		]);
	}

}
