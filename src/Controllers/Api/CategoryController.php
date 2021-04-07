<?php

namespace Abs\ProductPkg\Controllers\Api;

use Abs\BasicPkg\Classes\ApiResponse;
use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;
use App\Models\Masters\Category;
use Illuminate\Support\Str;

class CategoryController extends BaseController {
	use CrudTrait;
	public $model = Category::class;

    public function alterCrudInput($action, &$input) {
        //if(!isset($input['id']) || !$input['id']){
        //    $input['code'] = Str::random();
        //}
    }

    public function afterSave($Model, $isNew, $input, ApiResponse $response){
        //if($isNew){
        //    $Model->code = sprintf('CUS%04d', $Model->id);
        //    $Model->save();
        //}
    }

    public function beforeCrudAction($action, ApiResponse $Response, $category) {
        if ($action == 'read') {
            //$address = $category->primaryAddress;
            //if ($address) {
            //    $address->formatted = $address->getFormattedAddress();
            //}
            // dd($customer->primaryAddress->formatted);
            // $response->setData('customer', $customer);
        }
    }

    private function alterCrudResponse($action, $response) {
        if ($action == 'read') {
            // $customer = $response->getData('customer');
            // $address = $customer->address;
            // dd()
            // if ($address) {
            // 	$address->formatted_address = $customer->getFormattedAddress();
            // }
            // dd($customer->address->formatted_address);
            // $response->setData('customer', $customer);
        }
    }

}
