app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/product-pkg/item/list', {
        template: '<item-list></item-list>',
        title: 'Items',
    }).
    when('/product-pkg/item/add', {
        template: '<item-form></item-form>',
        title: 'Add Item',
    }).
    when('/product-pkg/item/edit/:id', {
        template: '<item-form></item-form>',
        title: 'Edit Item',
    });
}]);

app.component('itemList', {
    templateUrl: item_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var table_scroll;
        table_scroll = $('.page-main-content').height() - 37;
        var dataTable = $('#items_list').DataTable({
            "dom": dom_structure,
            "language": {
                "search": "",
                "searchPlaceholder": "Search",
                "lengthMenu": "Rows Per Page _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            stateSave: true,
            pageLength: 10,
            processing: true,
            serverSide: true,
            paging: true,
            ordering: false,
            ajax: {
                url: laravel_routes['getItemList'],
                data: function(d) {}
            },
            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'category_name', name: 'c.name', searchable: true },
                { data: 'strength_name', name: 's.name', searchable: true },
                { data: 'package_size', name: 'items.package_size', searchable: true },
                { data: 'main_category_name', name: 'mc.name', searchable: true },
                { data: 'display_order', searchable: false },
                { data: 'special_price', searchable: false },
                { data: 'has_free', searchable: false },
                { data: 'free_qty', searchable: false },
                { data: 'has_free_shipping', searchable: false },
                { data: 'shipping_method_name', name: 'sm.name', searchable: true },
            ],
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(total + '/' + max)
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            },
            initComplete: function() {
                $('.search label input').focus();
            },
        });
        $('.dataTables_length select').select2();
        $('.page-header-content .display-inline-block .data-table-title').html('Items <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        $('.add_new_button').html(
            '<a href="#!/product-pkg/item/add" type="button" class="btn btn-secondary" dusk="add-btn">' +
            'Add Item' +
            '</a>'
        );

        $('.btn-add-close').on("click", function() {
            $('#items_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#items_list').DataTable().ajax.reload();
        });

        $('.dataTables_length select').select2();

        $scope.clear_search = function() {
            $('#search_item').val('');
            $('#items_list').DataTable().search('').draw();
        }

        var dataTables = $('#items_list').dataTable();
        $("#search_item").keyup(function() {
            dataTables.fnFilter(this.value);
        });

        //DELETE
        $scope.deleteItem = function($id) {
            $('#item_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#item_id').val();
            $http.get(
                item_delete_data_url + '/' + $id,
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Item Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#items_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/product-pkg/item/list');
                }
            });
        }

        //FOR FILTER
        $('#item_code').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#item_name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#mobile_no').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#email').on('keyup', function() {
            dataTables.fnFilter();
        });
        $scope.reset_filter = function() {
            $("#item_name").val('');
            $("#item_code").val('');
            $("#mobile_no").val('');
            $("#email").val('');
            dataTables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('itemForm', {
    templateUrl: item_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        get_form_data_url = typeof($routeParams.id) == 'undefined' ? laravel_routes['getItemFormData'] : laravel_routes['getItemFormData'] + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http({
            url: laravel_routes['getItemFormData'],
            method: 'GET',
            params: {
                'id': typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
            }
        }).then(function(response) {
            self.item = response.data.item;
            self.extras = response.data.extras;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.item.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
        });

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'main_category_id': {
                    required: true,
                },
                'category_id': {
                    required: true,
                },
                'strength_id': {
                    required: true,
                },
                'package_size': {
                    required: true,
                },
                'display_order': {
                    required: true,
                },
                'special_price': {
                    required: true,
                },
            },
            invalidHandler: function(event, validator) {
                checkAllTabNoty()
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveItem'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message)
                            $location.path('/product-pkg/item/list');
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                showErrorNoty(res)
                            } else {
                                $('#submit').button('reset');
                                $location.path('/product-pkg/item/list');
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        showServerErrorNoty()
                    });
            }
        });
    }
});
