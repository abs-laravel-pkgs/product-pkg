<?php

namespace Abs\ProductPkg;
use Abs\ProductPkg\Category;
use Abs\ProductPkg\Item;
use Abs\ProductPkg\MainCategory;
use Abs\ProductPkg\Strength;
use Abs\ShippingMethodPkg\ShippingMethod;
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
				'items.free_qty',
				'sm.name as shipping_method_name',
				'items.deleted_at',
				DB::raw('IF(items.has_free = 1, "Yes","No") as has_free'),
				DB::raw('IF(items.has_free_shipping = 1, "Yes","No") as has_free_shipping'),
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
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/product-pkg/item/edit/' . $item->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="javascript:;"  data-toggle="modal" data-target="#item-delete-modal" onclick="angular.element(this).scope().deleteItem(' . $item->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
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
			$item = Item::withTrashed()->with([
				'category',
				'category.mainCategory',
				'strengths',
				'shippingMethods',
			])
				->where('items.id', $id)
				->first();
			$this->data['category_list'] = collect(Category::where('main_category_id', $item->category->main_category_id)->select('id', 'name')->get())->prepend(['name' => 'Select Category', 'id' => '']);
			$action = 'Edit';
		}
		$this->data['item'] = $item;
		$this->data['extras'] = [
			'main_category_list' => collect(MainCategory::where('company_id', $this->company_id)->select('id', 'name')->get())->prepend(['name' => 'Select Main Category', 'id' => '']),
			'shipping_method_list' => collect(ShippingMethod::where('company_id', $this->company_id)->select('id', 'name')->get())->prepend(['name' => 'Select Shipping Method', 'id' => '']),
			'strength_list' => collect(Strength::where('company_id', $this->company_id)->select('id', 'name')->get())->prepend(['name' => 'Select Strength', 'id' => '']),
		];
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function getCategory(Request $r) {
		$id = $r->id;
		if ($id) {
			$category_list = collect(Category::where('main_category_id', $id)->select('id', 'name')->get())->prepend(['name' => 'Select Category', 'id' => '']);
			$this->data['category_list'] = $category_list;
		} else {
			return response()->json(['success' => false, 'error' => 'Category not found']);
		}
		return response()->json($this->data);
	}

	public function saveItem(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'category_id.required' => 'Category is Required',
				'strength_id.required' => 'Strength is Required',
				'package_size.required' => 'Package Size is Required',
				'package_size.unique' => 'Package Size is already taken',
				'display_order.required' => 'Display Order is Required',
				'regular_price.required' => 'Regular price is Required',
				'special_price.required' => 'Special price is Required',
			];
			$validator = Validator::make($request->all(), [
				'category_id' => 'required',
				'strength_id' => 'required',
				'package_size' => [
					'required:true',
					'unique:items,package_size,' . $request->id . ',id,category_id,' . $request->category_id . ',strength_id,' . $request->strength_id,
				],
				'display_order' => 'required',
				'regular_price' => 'required',
				'special_price' => 'required',
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
			// $item->company_id = Auth::user()->company_id;
			if ($request->has_free == 'Yes') {
				$item->has_free = 1;
				$item->free_qty = $request->free_qty;
			} else {
				$item->has_free = 0;
				$item->free_qty = 0;
			}
			if ($request->has_free_shipping == 'Yes') {
				$item->has_free_shipping = 1;
				$item->shipping_method_id = $request->shipping_method_id;
			} else {
				$item->has_free_shipping = 0;
				$item->shipping_method_id = NULL;
			}
			$item->per_qty_price = 0; //per qty price
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
					'message' => 'Item Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Item Updated Successfully',
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

	public function deleteItem(Request $request) {
		DB::beginTransaction();
		try {
			Item::withTrashed()->where('id', $request->id)->forceDelete();
			DB::commit();
			return response()->json(['success' => true, 'message' => 'Item Deleted Successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
