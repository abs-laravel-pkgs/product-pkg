<?php

namespace Abs\ProductPkg;
use Abs\ProductPkg\Category;
use Abs\ProductPkg\Category;
use Abs\ProductPkg\MainCategory;
use Abs\ProductPkg\Strength;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
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

	public function getCategories(Request $request) {
		$this->data['categories'] = Category::
			leftJoin('categories as c', 'c.id', 'categories.category_id')
			->leftJoin('strengths as s', 's.id', 'categories.strength_id')
			->leftJoin('main_categories as mc', 'mc.id', 'c.main_category_id')
			->leftJoin('shipping_methods as sm', 'sm.id', 'categories.shipping_method_id')
			->select([
				'c.name',
				's.name',
				'categories.package_size',
				'mc.name',
				'categories.display_order',
				'categories.special_price',
				'categories.has_free',
				'categories.free_qty',
				'categories.has_free_shipping',
				'sm.name',
			])
			->where('c.company_id', $this->company_id)
			->orderby('c.display_order', 'asc')
			->orderby('categories.display_order', 'asc')
		;
		$this->data['success'] = true;

		return response()->json($this->data);

	}

	public function getCategoryList(Request $request) {
		$categories = Category::withTrashed()
			->leftJoin('entities as pt', 'pt.id', 'categories.package_type_id')
			->leftJoin('entities as m', 'pt.id', 'categories.manufacturer_id')
			->leftJoin('entities as as', 'pt.id', 'categories.active_substance_id')
			->leftJoin('attachments as a', 'a.id', 'categories.image_id')
			->leftJoin('main_categories as mc', 'mc.id', 'categories.main_category_id')
			->select([
				'categories.id',
				'categories.name',
				'categories.display_order',
				'categories.seo_name',
				'pt.name as package_type_name',
				'm.name as manufacturer_name',
				'mc.name as main_category_name',
				'categories.special_price',
				'categories.has_free',
				'categories.free_qty',
				'categories.has_free_shipping',
				'sm.name as shipping_method_name',
				'categories.deleted_at',
			])
			->where('c.company_id', $this->company_id)
			->orderby('c.display_order', 'asc')
			->orderby('categories.display_order', 'asc')
		;

		return Datatables::of($categories)
			->rawColumns(['action', 'category_name'])

			->addColumn('category_name', function ($category) {
				$status = $category->deleted_at ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $category->category_name;
			})
			->addColumn('action', function ($category) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img2 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye.svg');
				$img2_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/product-pkg/category/edit/' . $category->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="#!/product-pkg/category/view/' . $category->id . '" id = "" ><img src="' . $img2 . '" alt="View" class="img-responsive" onmouseover=this.src="' . $img2_active . '" onmouseout=this.src="' . $img2 . '"></a>
					<a href="javascript:;"  data-toggle="modal" data-target="#category-delete-modal" onclick="angular.element(this).scope().deleteRoleconfirm(' . $category->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
					';
				return $output;
			})
			->make(true);
	}

	public function getCategoryFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$category = new Category;
			$action = 'Add';
		} else {
			$category = Category::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['category'] = $category;
		$this->data['extras'] = [
			'main_category_list' => MainCategory::getList(),
			'category_list' => Category::getList(),
			'strength_list' => Strength::getList(),
		];
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveCategory(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Category Code is Required',
				'code.max' => 'Maximum 255 Characters',
				'code.min' => 'Minimum 3 Characters',
				'code.unique' => 'Category Code is already taken',
				'name.required' => 'Category Name is Required',
				'name.max' => 'Maximum 255 Characters',
				'name.min' => 'Minimum 3 Characters',
			];
			$validator = Validator::make($request->all(), [
				'question' => [
					'required:true',
					'max:255',
					'min:3',
					'unique:categories,question,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'answer' => 'required|max:255|min:3',
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
			if ($request->status == 'Inactive') {
				$category->deleted_at = Carbon::now();
				$category->deleted_by_id = Auth::user()->id;
			} else {
				$category->deleted_by_id = NULL;
				$category->deleted_at = NULL;
			}
			$category->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'FAQ Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'FAQ Updated Successfully',
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

	public function deleteCategory($id) {
		$delete_status = Category::withTrashed()->where('id', $id)->forceDelete();
		return response()->json(['success' => true]);
	}
}
