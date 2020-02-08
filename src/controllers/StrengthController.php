<?php

namespace Abs\ProductPkg;
use Abs\Basic\Entity;
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

	public function getStrengthList(Request $request) {
		$strengths = Strength::withTrashed()
			->leftJoin('entities', 'entities.id', 'strengths.type_id')
			->select(
				'strengths.*',
				'entities.name as entity_name',
				DB::raw('IF(strengths.deleted_at IS NULL, "Active","Inactive") as status')
			)
			->where('strengths.company_id', $this->company_id)
			->orderby('strengths.id', 'desc');

		return Datatables::of($strengths)
			->rawColumns(['action', 'name'])
			->addColumn('name', function ($strengths) {
				$status = $strengths->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $strengths->name;
			})
			->addColumn('action', function ($strengths) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/product-pkg/strength/edit/' . $strengths->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="javascript:;"  data-toggle="modal" data-target="#strength-delete-modal" onclick="angular.element(this).scope().deleteStrength(' . $strengths->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
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
			$strength = Strength::withTrashed()->where('id', $id)->with([
				'type',
			])->first();
			$action = 'Edit';
		}
		$this->data['strength'] = $strength;
		$this->data['extras'] = [
			'type_list' => collect(Entity::where('entity_type_id', 5)->select('id', 'name')->get())->prepend(['name' => 'Select Type', 'id' => '']),
		];
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveStrength(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Strength Name is Required',
				'type_id.required' => 'Type is Required',
				'display_order.required' => 'Display Order is Required',
			];
			$validator = Validator::make($request->all(), [
				'name' => 'required',
				'type_id' => 'required',
				'display_order' => 'required',
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
					'message' => 'Strength Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Strength Updated Successfully',
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

	public function deleteStrength(Request $request) {
		DB::beginTransaction();
		try {
			Strength::withTrashed()->where('id', $request->id)->forceDelete();
			DB::commit();
			return response()->json(['success' => true, 'message' => 'Strength Deleted Successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
