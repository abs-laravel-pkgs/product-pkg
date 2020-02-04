@if(config('product-pkg.DEV'))
    <?php $product_pkg_prefix = '/packages/abs/product-pkg/src';?>
@else
    <?php $product_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var main_category_list_template_url = "{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/main-category/list.html')}}";
    var main_category_form_template_url = "{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/main-category/form.html')}}";
</script>
<script type="text/javascript" src="{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/main-category/controller.js')}}"></script>

<script type="text/javascript">
    var category_list_template_url = "{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/category/list.html')}}";
    var category_form_template_url = "{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/category/form.html')}}";
</script>
<script type="text/javascript" src="{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/category/controller.js')}}"></script>

<script type="text/javascript">
    var strength_list_template_url = "{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/strength/list.html')}}";
    var strength_form_template_url = "{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/strength/form.html')}}";
</script>
<script type="text/javascript" src="{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/strength/controller.js')}}"></script>

<script type="text/javascript">
    var item_list_template_url = "{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/item/list.html')}}";
    var item_form_template_url = "{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/item/form.html')}}";
</script>
<script type="text/javascript" src="{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/item/controller.js')}}"></script>
