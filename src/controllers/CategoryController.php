<?php

namespace Abs\ProductPkg;
use Abs\Basic\Attachment;
use Abs\Basic\Entity;
use Abs\ProductPkg\Category;
use Abs\ProductPkg\MainCategory;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class CategoryController extends Controller {

	private $company_id;
	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
		// $this->company_id = Auth::user()->company_id;
		$this->company_id = config('custom.company_id');
	}

	public function filterCategory(Request $request) {
		$this->data['main_categories'] = collect(MainCategory::where('company_id', $this->company_id)->select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Main Category']);
		return response()->json($this->data);
	}

	public function getCategoryList(Request $request) {
		$categories = Category::withTrashed()->select(
			'categories.id',
			'categories.name as category_name',
			'categories.display_order as category_display_order',
			'categories.seo_name as category_seo_name',
			'main_categories.name as main_category_name',
			DB::raw('IF(categories.deleted_at IS NULL, "Active","Inactive") as status')
		)
			->leftJoin('main_categories', 'main_categories.id', 'categories.main_category_id')
			->where('categories.company_id', $this->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('categories.name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->seo_name)) {
					$query->where('categories.seo_name', 'LIKE', '%' . $request->seo_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if(!empty($request->main_category)) {
					$query->where('categories.main_category_id', $request->main_category);
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status_name == '1') {
					$query->whereNull('categories.deleted_at');
				} else if ($request->status_name == '0') {
					$query->whereNotNull('categories.deleted_at');
				}
			})
			->orderby('categories.id', 'desc')
		;

		return Datatables::of($categories)
			->addColumn('name', function ($categories) {
				$status = $categories->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $categories->category_name;
			})
			->addColumn('action', function ($categories) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/product-pkg/category/edit/' . $categories->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="javascript:;"  data-toggle="modal" data-target="#category-delete-modal" onclick="angular.element(this).scope().deleteCategory(' . $categories->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>';
				return $output;
			})
			->make(true);
	}

	public function getCategoryFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$category = new Category;
			$attachment = new Attachment;
			$action = 'Add';
		} else {
			$category = Category::withTrashed()->with([
				'mainCategory',
				'manufacturer',
				'packageType',
				'activeSubstance',
			])
				->where('categories.id', $id)
				->first();
			$attachment = Attachment::where('id', $category->image_id)->first();
			$action = 'Edit';
		}
		$this->data['category'] = $category;
		$this->data['attachment'] = $attachment;
		$this->data['extras'] = [
			'main_category_list' => collect(MainCategory::where('company_id', $this->company_id)->select('id', 'name')->get())->prepend(['name' => 'Select Main Category', 'id' => '']),
			'active_substance_list' => collect(Entity::where('entity_type_id', 1)->select('id', 'name')->get())->prepend(['name' => 'Select Active Substance', 'id' => '']),
			'manufacture_list' => collect(Entity::where('entity_type_id', 3)->select('id', 'name')->get())->prepend(['name' => 'Select Manufacture', 'id' => '']),
			'package_type_list' => collect(Entity::where('entity_type_id', 4)->select('id', 'name')->get())->prepend(['name' => 'Select Package Type', 'id' => '']),
		];
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveCategory(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'display_order.required' => 'Display Order is Required',
				'package_type_id.required' => 'Package Type is Required',
				'customer_rating.required' => 'Customer Rating is Required',
				'seo_name.required' => 'SEO Name is Required',
				'seo_name.unique' => 'SEO Name is already taken',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required',
					'unique:categories,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'display_order' => 'required',
				'package_type_id' => 'required',
				'customer_rating' => 'required',
				'seo_name' => [
					'required',
					'unique:categories,seo_name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'image_id' => 'mimes:jpeg,jpg,png,gif,ico,bmp,svg|nullable|max:10000',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$category = new Category;
				$category->created_by_id = Auth::user()->id;
				$category->created_at = Carbon::now();
				$category->updated_at = NULL;
			} else {
				$category = Category::withTrashed()->find($request->id);
				$category->updated_by_id = Auth::user()->id;
				$category->updated_at = Carbon::now();
			}
			$category->fill($request->all());
			$category->company_id = Auth::user()->company_id;
			$category->starts_at = 0; //Starts at Dummy value
			if ($request->status == 'Inactive') {
				$category->deleted_at = Carbon::now();
				$category->deleted_by_id = Auth::user()->id;
			} else {
				$category->deleted_by_id = NULL;
				$category->deleted_at = NULL;
			}
			if ($request->has_free == 'Yes') {
				$category->has_free = 1;
			} else {
				$category->has_free = 0;
			}
			if ($request->has_free_shipping == 'Yes') {
				$category->has_free_shipping = 1;
			} else {
				$category->has_free_shipping = 0;
			}
			if ($request->is_best_selling == 'Yes') {
				$category->is_best_selling = 1;
			} else {
				$category->is_best_selling = 0;
			}
			$category->save();

			if (!empty($request->image_id)) {
				if (!File::exists(public_path() . '/themes/' . config('custom.admin_theme') . '/img/category_image')) {
					File::makeDirectory(public_path() . '/themes/' . config('custom.admin_theme') . '/img/category_image', 0777, true);
				}

				$attacement = $request->image_id;
				$remove_previous_attachment = Attachment::where([
					'entity_id' => $request->id,
					'attachment_of_id' => 21,
				])->first();
				if (!empty($remove_previous_attachment)) {
					$remove = $remove_previous_attachment->forceDelete();
					$img_path = public_path() . '/themes/' . config('custom.admin_theme') . '/img/category_image/' . $remove_previous_attachment->name;
					if (File::exists($img_path)) {
						File::delete($img_path);
					}
				}
				$random_file_name = $category->id . '_category_file_' . rand(0, 1000) . '.';
				$extension = $attacement->getClientOriginalExtension();
				$attacement->move(public_path() . '/themes/' . config('custom.admin_theme') . '/img/category_image', $random_file_name . $extension);

				$attachment = new Attachment;
				$attachment->company_id = Auth::user()->company_id;
				$attachment->attachment_of_id = 21; //Company
				$attachment->attachment_type_id = 40; //Primary
				$attachment->entity_id = $category->id;
				$attachment->name = $random_file_name . $extension;
				$attachment->save();
				$category->image_id = $attachment->id;
				$category->save();
			}

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Category Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Category Updated Successfully',
				]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'error' => $e->getMessage(),
			]);
		}
	}

	public function deleteCategory(Request $request) {
		DB::beginTransaction();
		try {
			Category::withTrashed()->where('id', $request->id)->forceDelete();
			Attachment::where('company_id', Auth::user()->company_id)->where('attachment_of_id', 21)->where('entity_id', $request->id)->forceDelete();
			DB::commit();
			return response()->json(['success' => true, 'message' => 'Category Deleted Successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
