<?php

namespace Abs\ProductPkg;
use Abs\ProductPkg\Category;
use Abs\ProductPkg\Strength;
use Abs\ProductPkg\MainCategory;
use Abs\ProductPkg\Strength;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class StrengthController extends Controller {

	private $company_id;
	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
		// $this->company_id = Auth::user()->company_id;
		$this->company_id = config('custom.company_id');
	}

	public function getStrengths(Request $request) {
		$this->data['strengths'] = Strength::
			leftJoin('categories as c', 'c.id', 'strengths.category_id')
			->leftJoin('strengths as s', 's.id', 'strengths.strength_id')
			->leftJoin('main_categories as mc', 'mc.id', 'c.main_category_id')
			->leftJoin('shipping_methods as sm', 'sm.id', 'strengths.shipping_method_id')
			->select([
				'c.name',
				's.name',
				'strengths.package_size',
				'mc.name',
				'strengths.display_order',
				'strengths.special_price',
				'strengths.has_free',
				'strengths.free_qty',
				'strengths.has_free_shipping',
				'sm.name',
			])
			->where('c.company_id', $this->company_id)
			->orderby('c.display_order', 'asc')
			->orderby('strengths.display_order', 'asc')
		;
		$this->data['success'] = true;

		return response()->json($this->data);

	}

	public function getStrengthList(Request $request) {
		$strengths = Strength::withTrashed()
			->leftJoin('categories as c', 'c.id', 'strengths.category_id')
			->leftJoin('strengths as s', 's.id', 'strengths.strength_id')
			->leftJoin('main_categories as mc', 'mc.id', 'c.main_category_id')
			->leftJoin('shipping_methods as sm', 'sm.id', 'strengths.shipping_method_id')
			->select([
				'strengths.id',
				'c.name as category_name',
				's.name as strength_name',
				'strengths.package_size',
				'mc.name as main_category_name',
				'strengths.display_order',
				'strengths.special_price',
				'strengths.has_free',
				'strengths.free_qty',
				'strengths.has_free_shipping',
				'sm.name as shipping_method_name',
				'strengths.deleted_at',
			])
			->where('c.company_id', $this->company_id)
			->orderby('c.display_order', 'asc')
			->orderby('strengths.display_order', 'asc')
		;

		return Datatables::of($strengths)
			->rawColumns(['action', 'category_name'])

			->addColumn('category_name', function ($strength) {
				$status = $strength->deleted_at ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $strength->category_name;
			})
			->addColumn('action', function ($strength) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img2 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye.svg');
				$img2_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/product-pkg/strength/edit/' . $strength->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="#!/product-pkg/strength/view/' . $strength->id . '" id = "" ><img src="' . $img2 . '" alt="View" class="img-responsive" onmouseover=this.src="' . $img2_active . '" onmouseout=this.src="' . $img2 . '"></a>
					<a href="javascript:;"  data-toggle="modal" data-target="#strength-delete-modal" onclick="angular.element(this).scope().deleteRoleconfirm(' . $strength->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
					';
				return $output;
			})
			->make(true);
	}

	public function getStrengthFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$strength = new Strength;
			$action = 'Add';
		} else {
			$strength = Strength::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['strength'] = $strength;
		$this->data['extras'] = [
			'main_category_list' => MainCategory::getList(),
			'category_list' => Category::getList(),
			'strength_list' => Strength::getList(),
		];
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveStrength(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Strength Code is Required',
				'code.max' => 'Maximum 255 Characters',
				'code.min' => 'Minimum 3 Characters',
				'code.unique' => 'Strength Code is already taken',
				'name.required' => 'Strength Name is Required',
				'name.max' => 'Maximum 255 Characters',
				'name.min' => 'Minimum 3 Characters',
			];
			$validator = Validator::make($request->all(), [
				'question' => [
					'required:true',
					'max:255',
					'min:3',
					'unique:strengths,question,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'answer' => 'required|max:255|min:3',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$strength = new Strength;
				$strength->created_by_id = Auth::user()->id;
				$strength->created_at = Carbon::now();
				$strength->updated_at = NULL;
			} else {
				$strength = Strength::withTrashed()->find($request->id);
				$strength->updated_by_id = Auth::user()->id;
				$strength->updated_at = Carbon::now();
			}
			$strength->fill($request->all());
			$strength->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$strength->deleted_at = Carbon::now();
				$strength->deleted_by_id = Auth::user()->id;
			} else {
				$strength->deleted_by_id = NULL;
				$strength->deleted_at = NULL;
			}
			$strength->save();

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

	public function deleteStrength($id) {
		$delete_status = Strength::withTrashed()->where('id', $id)->forceDelete();
		return response()->json(['success' => true]);
	}
}
