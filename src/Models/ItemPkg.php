<?php

namespace Abs\ProductPkg\Models;

use Abs\CompanyPkg\Traits\CompanyableTrait;
use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
use App\Entity;
use App\Index;
use App\Models\Attachment;
use App\Models\BaseModel;
use App\Models\Masters\ItemImage;
use App\Tag;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Input;
use App\Review;
use App\Item;
use App\ShippingMethod;
use App\Strength;
use App\Category;

class ItemPkg extends BaseModel {
	use SoftDeletes;
	use CompanyableTrait;
	use SeederTrait;
	protected $table = 'items';

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
		$this->rules = [
			'name' => [
				'min:3',
				'unique:items,name,' . Input::get('id'),
			],
			'seo_name' => [
				'unique:items,seo_name,' . Input::get('id'),
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
		'seo_name',
		'short_description',
		'full_description',
		'rating',
		'package_size',
		'display_order',
		'regular_price',
		'special_price',
		'has_free',
		'has_free_shipping',
		'free_qty',
		'per_qty_price',
		'page_title',
		'meta_description',
		'meta_keywords',
	];

	protected $casts = [
		'rating' => 'integer',
		'regular_price' => 'float',
		'special_price' => 'float',
		'per_qty_price' => 'float',
		'free_qty' => 'float',
		'has_free' => 'boolean',
		'has_free_shipping' => 'boolean',
	];

	public $sortable = [
		'name',
		'display_order',
		'seo_name',
		'page_title',
		'rating',
		'has_free',
		'has_free_shipping',
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
		'image',
		'company',
		'category',
		'strength',
		'shippingMethod',
		'tags',
		'relatedItems',
		'images',
	];

	public $relationshipRules = [
		'image' => [
			'required',
		],
	];

	// Relationships to auto load
	public static function relationships($action = '', $format = ''): array
	{
		$relationships = [];

		if ($action === 'index') {
			$relationships = array_merge($relationships, [
				'category',
			]);
		}
		else if ($action === 'read') {
			$relationships = array_merge($relationships, [
				'category',
				'strength',
				'shippingMethod',
				'tags',
				'image',
				'images.image',
				'relatedItems',
			]);
		}
		else if ($action === 'save') {
			$relationships = array_merge($relationships, [
				'category',
			]);
		}
		else if ($action === 'options') {
			$relationships = array_merge($relationships, [
				'category',
			]);
		}

		return $relationships;
	}

	public static function appendRelationshipCounts($action = '', $format = ''): array
	{
		$relationships = [];

		if ($action === 'index') {
			$relationships = array_merge($relationships, [
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

	//--------------------- Relations -------------------------------------------------------

	public function category(): BelongsTo {
		return $this->belongsTo(\App\Models\Masters\Category::class);
	}

	public function strengths(): BelongsTo {
		return $this->belongsTo(Strength::class, 'strength_id');
	}

	public function strength(): BelongsTo {
		return $this->belongsTo(Strength::class);
	}

	public function shippingMethod(): BelongsTo {
		return $this->belongsTo(ShippingMethod::class);
	}

	public function shippingMethods(): BelongsTo {
		return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
	}

	public function tags(): BelongsToMany {
		return $this->belongsToMany(Tag::class, 'item_tags', 'item_id');
	}

	public function image(): BelongsTo {
		return $this->belongsTo(Attachment::class, 'image_id');
	}

	public function images(): HasMany {
		return $this->hasMany(ItemImage::class);
	}

	public function reviews(): MorphMany{
		return $this->morphMany(Review::class, 'reviewable');
	}

	public function relatedItems(){
		return $this->belongsToMany('App\Item', 'item_related_item','item_id');
	}

	// public function relatedItems(): BelongsToMany{
	// 	return $this->belongsToMany(Item::class, 'item_related_item','related_item_id');
	// }

	//--------------------- Query Scopes -------------------------------------------------------
	public function scopeFilterSearch($query, $term): void {
		if ($term !== '') {
			$query->where(function ($query) use ($term) {
				$query->orWhere('name', 'LIKE', "%{$term}%");
			});
		}
	}

	public function scopeFilterCategory($query, $category): void {
		$categoryId = $category instanceof Category ? $category->id : $category;
		$query->where('category_id', '=', $categoryId);
	}

	public function scopeFilterByTagName($query, $tagName){
		return $query->whereHas('tags',function($query) use ($tagName){
			$query->select('*','items.id as item_id', 'attachments.id as attachment_id','attachments.name as primary_attachment')
				->leftjoin('attachments', 'items.id', 'attachments.entity_id')
				->where('items.name',$tagName)
                ->where('attachments.attachment_type_id',202);
		});
	}

	//--------------------- Static Operations -------------------------------------------------------

	public static function saveFromObject($record_data): array
	{
		//$record = [
		//	'Company Code' => $record_data->company_code,
		//	'Display Order' => $record_data->display_order,
		//	'Category Name' => $record_data->category_name,
		//	'Category Name' => $record_data->category_name,
		//	'Image' => $record_data->image,
		//	'SEO Name' => $record_data->seo_name,
		//	'Page Title' => $record_data->page_title,
		//	'Meta Description' => $record_data->meta_description,
		//	'Meta Keywords' => $record_data->meta_keywords,
		//	'Description' => $record_data->description,
		//	'Usage' => $record_data->usage,
		//	'Manufacturer' => $record_data->manufacturer,
		//	'Active Substance' => $record_data->active_substance,
		//	'Customer Rating' => $record_data->customer_rating,
		//	'Has Free' => $record_data->has_free,
		//	'Has Free Shipping' => $record_data->has_free_shipping,
		//	'Package Type' => $record_data->package_type,
		//	'Is Best Selling' => $record_data->is_best_selling,
		//	'Status' => $record_data->status,
		//];
		//return static::saveFromExcelArray($record);
		return static::createFromObject($record_data);
	}

	public static function createFromObject($record_data, $company = null): array
	{
		try {
			$errors = [];
			$company = Company::where('code', $record_data->company_code)->first();
			if (!$company) {
				return [
					'success' => false,
					'errors' => ['Invalid Company : ' . $record_data->company_code],
				];
			}

			if (!isset($record_data->created_by_id)) {
				$admin = $company->admin();

				if (!$admin) {
					return [
						'success' => false,
						'errors' => ['Default Admin user not found'],
					];
				}
				$created_by_id = $admin->id;
			} else {
				$created_by_id = $record_data->created_by_id;
			}

			$category = Category::where([
				'company_id' => $company->id,
				'name' => $record_data->category_name,
			])->first();
			if (!$category) {
				$errors[] = ('Invalid category : ' . $record_data->category_name);
			}

			if ($record_data->strength_type) {
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
				$strength_type_id = $strength ? $strength->id : null;
			} else {
				$strength_type_id = null;
			}

			$free_shipping_id = null;
			if ($record_data->free_shipping_type) {
				$shipping_method = ShippingMethod::where([
					'company_id' => $company->id,
					'name' => $record_data->free_shipping_type,
				])->first();
				if (!$shipping_method) {
					$errors[] = ('Invalid free_shipping_type : ' . $record_data->free_shipping_type);
				} else {
					$free_shipping_id = $shipping_method->id;
				}
			}

			if (count($errors) > 0) {
				return [
					'success' => false,
					'errors' => $errors,
				];
			}

			if($strength_type_id){
				$record = self::firstOrNew([
					'category_id' => $category->id,
					'strength_id' => $strength->id,
					'package_size' => floatval($record_data->package_size),
				]);
			}else{
				$record = self::firstOrNew([
					'category_id' => $category->id,
					'name' => $record_data->name,
				]);
			}
			$record->display_order = $record_data->display_order;
			$record->regular_price = floatval($record_data->regular_price);
			$record->special_price = floatval($record_data->special_price);
			$record->per_qty_price = $record->special_price && $record->package_size ? round($record->special_price / $record->package_size, 2) : 0;
			$record->has_free = $record_data->has_free == 'Yes' ? 1 : 0;
			$record->free_qty = $record_data->free_qty ? $record_data->free_qty : 0;
			$record->has_free_shipping = $record_data->has_free_shipping == 'Yes' ? 1 : 0;
			$record->shipping_method_id = $free_shipping_id;
			$record->created_by_id = $created_by_id;
			if ($record_data->status != 1) {
				$record->deleted_at = date('Y-m-d');
			}
			$record->save();
			return [
				'success' => true,
			];

		} catch (\Exception $e) {
			return [
				'success' => false,
				'errors' => [$e->getMessage() . '. Line : ' . $e->getLine()],
			];
		}
	}

}
