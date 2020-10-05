<?php

namespace Abs\ProductPkg;

use Abs\HelperPkg\Traits\SeederTrait;

use App\BaseModel;
use App\Company;
use App\Item;
use App\Tag;

class ItemTag extends BaseModel {
	use SeederTrait;
	protected $table = 'item_tags';
	protected $fillable = [
		'item_id',
		'tag_id',
	];
	protected static $excelColumnRules = [
		'Item Name' => [
			'table_column_name' => 'item_id',
			'rules' => [
				'required' => [
				],
				'fk' => [
					'class' => 'App\Item',
					'foreign_table_column' => 'name',
				],
			],
		],
		'Tag Name' => [
			'table_column_name' => 'name',
			'rules' => [
				'required' => [
				],
				'fk' => [
					'class' => 'App\Tag',
					'foreign_table_column' => 'name',
				],
			],
		],
	];

	public static function saveFromObject($record_data) {
		$record = [
			'Company Code' => $record_data->company_code,
			'Item Name' => $record_data->item_name,
			'Tag Name' => $record_data->tag_name,
		];
		return static::saveFromExcelArray($record);
	}

	public static function saveFromExcelArray($record_data) {
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

		if (empty($record_data['Item Name'])) {
			$errors[] = 'Item Name is empty';
		} else {
			$item = Item::where([
				//'company_id' => $company->id,
				'name' => $record_data['Item Name'],
			])->first();
			if (!$item) {
				$errors[] = 'Invalid Item Name : ' . $record_data['Item Name'];
			}
		}

		if (empty($record_data['Tag Name'])) {
			$errors[] = 'Tag Name is empty';
		} else {
			$tag = Tag::where([
				'company_id' => $company->id,
				'taggable_type_id' => Item::$TAG_TYPE_CONFIG_ID,
				'name' => $record_data['Tag Name'],
			])->first();
			if (!$tag) {
				$errors[] = 'Invalid Tag Name : ' . $record_data['Tag Name'];
			}
		}

		if (count($errors) > 0) {
			return [
				'success' => false,
				'errors' => $errors,
			];
		}

		$record = Self::firstOrNew([
			'item_id' => $item->id,
			'tag_id' => $tag->id,
		]);
		//$result = Self::validateAndFillExcelColumns($record_data, Static::$excelColumnRules, $record);
		//if (!$result['success']) {
		//	return $result;
		//}

		//$record->created_by_id = $created_by_id;
		$record->save();
		return [
			'success' => true,
		];
	}


	//--------------------- Query Scopes -------------------------------------------------------


}
