<?php

namespace Abs\ProductPkg\Controllers;
use Abs\BasicPkg\Models\Attachment;
use Abs\ProductPkg\Models\MainCategory;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class MainCategoryController extends Controller {

	public $company_id;
	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
		// $this->company_id = Auth::user()->company_id;
		$this->company_id = config('custom.company_id');
	}

	public function getMainCategoryList(Request $request) {
		// dd($request->all());
		$main_categories = MainCategory::withTrashed()
			->select(
				'main_categories.*',
				DB::raw('IF(main_categories.deleted_at IS NULL, "Active","Inactive") as status')
			)
			->where('main_categories.company_id', $this->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('main_categories.name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->seo_name)) {
					$query->where('main_categories.seo_name', 'LIKE', '%' . $request->seo_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status_name == '1') {
					$query->whereNull('main_categories.deleted_at');
				} else if ($request->status_name == '0') {
					$query->whereNotNull('main_categories.deleted_at');
				}
			})
			->orderby('name', 'asc');

		return Datatables::of($main_categories)
			->addColumn('category_name', function ($main_categories) {
				$status = $main_categories->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $main_categories->name;
			})
			->addColumn('action', function ($main_categories) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/product-pkg/main-category/edit/' . $main_categories->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="javascript:;"  data-toggle="modal" data-target="#main-category-delete-modal" onclick="angular.element(this).scope().deleteMainCategory(' . $main_categories->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
					';
				return $output;
			})
			->make(true);
	}

	public function getMainCategoryFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$main_category = new MainCategory;
			$attachment = new Attachment;
			$action = 'Add';
		} else {
			$main_category = MainCategory::withTrashed()->find($id);
			$attachment = Attachment::where('id', $main_category->icon_id)->first();
			$action = 'Edit';
		}
		$this->data['main_category'] = $main_category;
		$this->data['attachment'] = $attachment;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveMainCategory(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'display_order.required' => 'Display Order is Required',
				'seo_name.required' => 'SEO Name is Required',
				'seo_name.unique' => 'SEO Name is already taken',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required',
					'unique:main_categories,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'display_order' => 'required',
				'seo_name' => [
					'required',
					'unique:main_categories,seo_name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'icon_id' => 'mimes:jpeg,jpg,png,gif,ico,bmp,svg|nullable|max:10000',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$main_category = new MainCategory;
				$main_category->created_by_id = Auth::user()->id;
				$main_category->created_at = Carbon::now();
				$main_category->updated_at = NULL;
			} else {
				$main_category = MainCategory::withTrashed()->find($request->id);
				$main_category->updated_by_id = Auth::user()->id;
				$main_category->updated_at = Carbon::now();
			}
			$main_category->fill($request->all());
			$main_category->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$main_category->deleted_at = Carbon::now();
				$main_category->deleted_by_id = Auth::user()->id;
			} else {
				$main_category->deleted_by_id = NULL;
				$main_category->deleted_at = NULL;
			}
			$main_category->save();
			if (!empty($request->icon_id)) {
				if (!File::exists(public_path() . '/themes/' . config('custom.admin_theme') . '/img/main_category_icon')) {
					File::makeDirectory(public_path() . '/themes/' . config('custom.admin_theme') . '/img/main_category_icon', 0777, true);
				}

				$attacement = $request->icon_id;
				$remove_previous_attachment = Attachment::where([
					'entity_id' => $request->id,
					'attachment_of_id' => 21,
				])->first();
				if (!empty($remove_previous_attachment)) {
					$remove = $remove_previous_attachment->forceDelete();
					$img_path = public_path() . '/themes/' . config('custom.admin_theme') . '/img/main_category_icon/' . $remove_previous_attachment->name;
					if (File::exists($img_path)) {
						File::delete($img_path);
					}
				}
				$random_file_name = $main_category->id . '_main_category_file_' . rand(0, 1000) . '.';
				$extension = $attacement->getClientOriginalExtension();
				$attacement->move(public_path() . '/themes/' . config('custom.admin_theme') . '/img/main_category_icon', $random_file_name . $extension);

				$attachment = new Attachment;
				$attachment->company_id = Auth::user()->company_id;
				$attachment->attachment_of_id = 21; //Company
				$attachment->attachment_type_id = 40; //Primary
				$attachment->entity_id = $main_category->id;
				$attachment->name = $random_file_name . $extension;
				$attachment->save();
				$main_category->icon_id = $attachment->id;
				$main_category->save();
			}

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Main Category Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Main Category Updated Successfully',
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

	public function deleteMainCategory(Request $request) {
		DB::beginTransaction();
		try {
			$main_category = MainCategory::withTrashed()->where('id', $request->id)->first();
			if (!is_null($main_category->icon_id)) {
				Attachment::where('company_id', Auth::user()->company_id)->where('attachment_of_id', 21)->where('entity_id', $request->id)->forceDelete();
			}
			MainCategory::withTrashed()->where('id', $request->id)->forceDelete();

			DB::commit();
			return response()->json(['success' => true, 'message' => 'Main Category Deleted Successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
