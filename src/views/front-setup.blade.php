@if(config('custom.PKG_DEV'))
    <?php $product_pkg_prefix = '/packages/abs/product-pkg/src';?>
@else
    <?php $product_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var item_list_template_url = "{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/item/items.html')}}";
</script>
<script type="text/javascript" src="{{asset($product_pkg_prefix.'/public/themes/'.$theme.'/product-pkg/item/controller.js')}}"></script>
