<?php

namespace Abs\ProductPkg\Models;

use Abs\HelperPkg\Traits\SeederTrait;

use App\BaseModel;
use App\Company;
use App\Category;
use App\Tag;

class CategoryTag extends BaseModel {
	use SeederTrait;
	protected $table = 'category_tags';
	protected $fillable = [
		'category_id',
		'tag_id',
	];
	protected static $excelColumnRules = [
		'Category Name' => [
			'table_column_name' => 'category_id',
			'rules' => [
				'required' => [
				],
				'fk' => [
					'class' => 'App\Category',
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
			'Category Name' => $record_data->category_name,
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

		if (empty($record_data['Category Name'])) {
			$errors[] = 'Category Name is empty';
		} else {
			$category = Category::where([
				'company_id' => $company->id,
				'name' => $record_data['Category Name'],
			])->first();
			if (!$category) {
				$errors[] = 'Invalid Category Name : ' . $record_data['Category Name'];
			}
		}

		if (empty($record_data['Tag Name'])) {
			$errors[] = 'Tag Name is empty';
		} else {
			$tag = Tag::where([
				'company_id' => $company->id,
				'taggable_type_id' => Category::$TAG_TYPE_CONFIG_ID,
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
			'category_id' => $category->id,
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
