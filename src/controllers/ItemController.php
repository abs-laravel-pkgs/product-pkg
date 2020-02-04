<?php

namespace Abs\ProductPkg;
use Abs\ProductPkg\Category;
use Abs\ProductPkg\Item;
use Abs\ProductPkg\MainCategory;
use Abs\ProductPkg\Strength;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class ItemController extends Controller {

	private $company_id;
	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
		// $this->company_id = Auth::user()->company_id;
		$this->company_id = config('custom.company_id');
	}

	public function getItems(Request $request) {
		$this->data['items'] = Item::
			leftJoin('categories as c', 'c.id', 'items.category_id')
			->leftJoin('strengths as s', 's.id', 'items.strength_id')
			->leftJoin('main_categories as mc', 'mc.id', 'c.main_category_id')
			->leftJoin('shipping_methods as sm', 'sm.id', 'items.shipping_method_id')
			->select([
				'c.name',
				's.name',
				'items.package_size',
				'mc.name',
				'items.display_order',
				'items.special_price',
				'items.has_free',
				'items.free_qty',
				'items.has_free_shipping',
				'sm.name',
			])
			->where('c.company_id', $this->company_id)
			->orderby('c.display_order', 'asc')
			->orderby('items.display_order', 'asc')
		;
		$this->data['success'] = true;

		return response()->json($this->data);

	}

	public function getItemList(Request $request) {
		$items = Item::withTrashed()
			->leftJoin('categories as c', 'c.id', 'items.category_id')
			->leftJoin('strengths as s', 's.id', 'items.strength_id')
			->leftJoin('main_categories as mc', 'mc.id', 'c.main_category_id')
			->leftJoin('shipping_methods as sm', 'sm.id', 'items.shipping_method_id')
			->select([
				'items.id',
				'c.name as category_name',
				's.name as strength_name',
				'items.package_size',
				'mc.name as main_category_name',
				'items.display_order',
				'items.special_price',
				'items.has_free',
				'items.free_qty',
				'items.has_free_shipping',
				'sm.name as shipping_method_name',
				'items.deleted_at',
			])
			->where('c.company_id', $this->company_id)
			->orderby('c.display_order', 'asc')
			->orderby('items.display_order', 'asc')
		;

		return Datatables::of($items)
			->rawColumns(['action', 'category_name'])

			->addColumn('category_name', function ($item) {
				$status = $item->deleted_at ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $item->category_name;
			})
			->addColumn('action', function ($item) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img2 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye.svg');
				$img2_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/product-pkg/item/edit/' . $item->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="#!/product-pkg/item/view/' . $item->id . '" id = "" ><img src="' . $img2 . '" alt="View" class="img-responsive" onmouseover=this.src="' . $img2_active . '" onmouseout=this.src="' . $img2 . '"></a>
					<a href="javascript:;"  data-toggle="modal" data-target="#item-delete-modal" onclick="angular.element(this).scope().deleteRoleconfirm(' . $item->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
					';
				return $output;
			})
			->make(true);
	}

	public function getItemFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$item = new Item;
			$action = 'Add';
		} else {
			$item = Item::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['item'] = $item;
		$this->data['extras'] = [
			'main_category_list' => MainCategory::getList(),
			'category_list' => Category::getList(),
			'strength_list' => Strength::getList(),
		];
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveItem(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Item Code is Required',
				'code.max' => 'Maximum 255 Characters',
				'code.min' => 'Minimum 3 Characters',
				'code.unique' => 'Item Code is already taken',
				'name.required' => 'Item Name is Required',
				'name.max' => 'Maximum 255 Characters',
				'name.min' => 'Minimum 3 Characters',
			];
			$validator = Validator::make($request->all(), [
				'question' => [
					'required:true',
					'max:255',
					'min:3',
					'unique:items,question,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'answer' => 'required|max:255|min:3',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$item = new Item;
				$item->created_by_id = Auth::user()->id;
				$item->created_at = Carbon::now();
				$item->updated_at = NULL;
			} else {
				$item = Item::withTrashed()->find($request->id);
				$item->updated_by_id = Auth::user()->id;
				$item->updated_at = Carbon::now();
			}
			$item->fill($request->all());
			$item->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$item->deleted_at = Carbon::now();
				$item->deleted_by_id = Auth::user()->id;
			} else {
				$item->deleted_by_id = NULL;
				$item->deleted_at = NULL;
			}
			$item->save();

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

	public function deleteItem($id) {
		$delete_status = Item::withTrashed()->where('id', $id)->forceDelete();
		return response()->json(['success' => true]);
	}
}
