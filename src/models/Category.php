<?php

namespace Abs\ProductPkg\Models;

use Abs\CompanyPkg\Traits\CompanyableTrait;
use Abs\HelperPkg\Traits\SeederTrait;
use App\Models\Attachment;
use App\Models\BaseModel;
use App\Company;
use App\Entity;
use App\Index;
use App\Item;
use App\MainCategory;
use App\Strength;
use App\Tag;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Input;

class Category extends BaseModel {
	use SoftDeletes;
	use CompanyableTrait;
	use SeederTrait;

	protected $table = 'categories';

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
		$this->rules = [
			'name' => [
				'min:3',
				'unique:categories,name,' . Input::get('id'),
			],
			'display_order' => [
			],
			'seo_name' => [
				'required',
				'unique:categories,seo_name,' . Input::get('id'),
			],
		];

	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'display_order',
		'seo_name',
		'page_title',
		'meta_description',
		'meta_keywords',
		'description',
		'usage',
		'customer_rating',
		'has_free',
		'has_free_shipping',
		'is_best_selling',
	];

	protected $casts = [
		'has_free' => 'boolean',
		'has_free_shipping' => 'boolean',
		'is_best_selling' => 'boolean',
	];

	public $sortable = [
		'name',
		'display_order',
		'seo_name',
		'page_title',
		'customer_rating',
		'has_free',
		'has_free_shipping',
		'is_best_selling',
	];

	public $sortScopes = [
		//'id' => 'orderById',
		//'code' => 'orderCode',
		//'name' => 'orderBytName',
		//'mobile_number' => 'orderByMobileNumber',
		//'email' => 'orderByEmail',
	];

	// Custom attributes specified in this array will be appended to model
	protected $appends = [
		'active',
	];

	//This model's validation rules for input values
	public $rules = [
		//Defined in constructor
	];

	public $fillableRelationships = [
		'company',
		'packageType',
		'image',
		'manufacturer',
		'activeSubstance',
		'mainCategory',
		'parent',
		'tags',
	];

	public $relationshipRules = [
//		'image' => [
//		    'required',
//		],
	];

	// Relationships to auto load
	public static function relationships($action = '', $format = ''): array
	{
		$relationships = [];

		if ($action === 'index') {
			$relationships = array_merge($relationships, [
				'mainCategory',
				'image',
			]);
		}
		else if ($action === 'read') {
			$relationships = array_merge($relationships, [
				'packageType',
				'image',
				'manufacturer',
				'activeSubstance',
				'mainCategory',
				'parent',
				'tags',
        'items.image',
			]);
		}
		else if ($action === 'save') {
			$relationships = array_merge($relationships, [
			]);
		}
		else if ($action === 'options') {
			$relationships = array_merge($relationships, [
			]);
		}

		return $relationships;
	}

	public static function appendRelationshipCounts($action = '', $format = ''): array
	{
		$relationships = [];

		if ($action === 'index') {
			$relationships = array_merge($relationships, [
				'items',
			]);
    } else if ($action === 'read') {
      $relationships = array_merge($relationships, [
        'items',
      ]);
    } else if ($action === 'options') {
      $relationships = array_merge($relationships, [
      ]);
		}

		return $relationships;
	}

	// Dynamic Attributes --------------------------------------------------------------
	public function getActiveAttribute(): bool
	{
		return !isset($this->attributes['deleted_at']) || !$this->attributes['deleted_at'];
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

	// Relationships --------------------------------------------------------------
	public function strengths(): BelongsToMany {
		return $this->belongsToMany(Strength::class);
	}

	public function image(): BelongsTo {
		return $this->belongsTo(Attachment::class, 'image_id');
	}

	public function parent(): BelongsTo {
		return $this->belongsTo(\App\Models\Masters\Category::class, 'parent_id');
	}

	public function items(): HasMany {
		return $this->hasMany(Item::class);
	}

	public function mainCategory(): BelongsTo {
		return $this->belongsTo(MainCategory::class);
	}

	public function activeSubstance(): BelongsTo {
		return $this->belongsTo(Entity::class, 'active_substance_id')->where('entity_type_id', 1);
	}

	public function drugCategory(): BelongsTo {
		return $this->belongsTo(Entity::class, 'drug_category_id')->where('entity_type_id', 2);
	}

	public function manufacturer(): BelongsTo {
		return $this->belongsTo(Entity::class, 'manufacturer_id')->where('entity_type_id', 3);
	}

	public function packageType(): BelongsTo {
		return $this->belongsTo(Entity::class, 'package_type_id')->where('entity_type_id', 4);
	}

	public function tags(): BelongsToMany {
		return $this->belongsToMany(Tag::class, 'category_tags', 'category_id');
	}

	public function lowestItem() {
		return $this->hasMany(Item::class)->orderBy('special_price')->first();
	}

	//--------------------- Query Scopes -------------------------------------------------------
	public function scopeFilterSearch($query, $term) {
		if ($term !== '') {
			$query->where(function ($query) use ($term) {
				$query->orWhere('name', 'LIKE', '%' . $term . '%');
				$query->orWhere('seo_name', 'LIKE', '%' . $term . '%');
				$query->orWhere('description', 'LIKE', '%' . $term . '%');
			});
		}
	}

	public function scopeFilterExcept($query, $category) {
		$categoryId =  ($category instanceof \App\Models\Masters\Category ) ? $category->id : $category;
		$query->where('categories.id','!=', $categoryId);
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

	public function scopeBest(Builder $query): Builder {
		return $query
			//->where('is_best_selling', 1)
			;
	}

	public function scopeFilterByTagName($query, $tagName){
		return $query->whereHas('tags',function($query) use ($tagName){
			$query->where('name',$tagName);
		});
	}

	//--------------------- Static Operations -------------------------------------------------------

	public static function saveFromObject($record_data) {
		$record = [
			'Company Code' => $record_data->company_code,
			'Main Category' => $record_data->main_category,
			'Display Order' => $record_data->display_order,
			'Category Name' => $record_data->category_name,
			'Image' => $record_data->image,
			'SEO Name' => $record_data->seo_name,
			'Page Title' => $record_data->page_title,
			'Meta Description' => $record_data->meta_description,
			'Meta Keywords' => $record_data->meta_keywords,
			'Description' => $record_data->description,
			'Usage' => $record_data->usage,
			'Manufacturer' => $record_data->manufacturer,
			'Active Substance' => $record_data->active_substance,
			'Customer Rating' => $record_data->customer_rating,
			'Has Free' => $record_data->has_free,
			'Has Free Shipping' => $record_data->has_free_shipping,
			'Package Type' => $record_data->package_type,
			'Is Best Selling' => $record_data->is_best_selling,
			'Status' => $record_data->status,
		];
		return static::saveFromExcelArray($record);
	}

	public static function saveFromExcelArray($record_data, $company = null) {

		$errors = [];
		$company = Company::where('code', $record_data['Company Code'])->first();
		if (!$company) {
			return [
				'success' => false,
				'errors' => ['Invalid Company : ' . $record_data['Company Code']],
			];
		}

		if (!isset($record_data['created_by_id'])) {
			$admin = $company->admin();

			if (!$admin) {
				return [
					'success' => false,
					'errors' => ['Default Admin user not found'],
				];
			}
			$created_by_id = $admin->id;
		} else {
			$created_by_id = $record_data['created_by_id'];
		}

		$main_category_id = null;
		if(!empty($record_data['Main Category'])){

			$main_category = MainCategory::where([
				'name' => $record_data['Main Category'],
				'company_id' => $company->id,
			])->first();
			if (!$main_category) {
				$errors[] = 'Invalid Main Category Name : ' . $record_data['Main Category'];
			} else {
				$main_category_id = $main_category->id;
			}
		}

		$manufacturer_id = null;
		if ($record_data['Manufacturer']) {
			$manufacturer = Entity::where([
				'company_id' => $company->id,
				'name' => $record_data['Manufacturer'],
				'entity_type_id' => 3,
			])->first();
			if (!$manufacturer) {
				$errors[] = 'Invalid manufacturer : ' . $record_data['Manufacturer'];
			} else {
				$manufacturer_id = $manufacturer->id;
			}
		}

		$active_substance_id = null;
		if(!empty($record_data['Active Substance'])){
			$active_substance = Entity::where([
				'company_id' => $company->id,
				'name' => $record_data['Active Substance'],
				'entity_type_id' => 1,
			])->first();
			if (!$active_substance) {
				$errors[] = 'Invalid active_substance : ' . $record_data['Active Substance'];
			}
		}

		$package_type_id = null;
		if(!empty($record_data['Package Type'])){
			$package_type = Entity::where([
				'company_id' => $company->id,
				'name' => $record_data['Package Type'],
				'entity_type_id' => 4,
			])->first();
			if (!$package_type) {
				$errors[] = 'Invalid package_type : ' . $record_data['Package Type'];
			}
		}

		if (count($errors) > 0) {
			return [
				'success' => false,
				'errors' => $errors,
			];
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data['Category Name'],
		]);
		$record->display_order = $record_data['Display Order'];
		$record->seo_name = $record_data['SEO Name'];
		$record->page_title = $record_data['Page Title'];
		$record->meta_description = $record_data['Meta Description'];
		$record->meta_keywords = $record_data['Meta Keywords'];
		$record->description = $record_data['Description'];
		$record->usage = $record_data['Usage'];
		$record->package_type_id = $package_type_id;
		$record->manufacturer_id = $manufacturer_id;
		$record->active_substance_id = $active_substance_id;
		$record->customer_rating = $record_data['Customer Rating'];
		$record->main_category_id = $main_category_id;
		$record->starts_at = 0;
		$record->has_free = $record_data['Has Free'] == 'Yes' ? 1 : 0;
		$record->has_free_shipping = $record_data['Has Free Shipping'] == 'Yes' ? 1 : 0;
		$record->is_best_selling = $record_data['Is Best Selling'] == 'Yes' ? 1 : 0;

		$record->created_by_id = $admin->id;
		if ($record_data['Status'] != 1) {
			$record->deleted_at = date('Y-m-d');
		}
		$record->created_by_id = $created_by_id;
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
			'url' => $record['seo_name'],
		]);
		$index->page_type_id = 20; //CATEGORY
		$index->save();
		return [
			'success' => true,
		];

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
