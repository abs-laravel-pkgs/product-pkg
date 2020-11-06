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
            '</a>' +
            '<a role="button" id="open" data-toggle="modal"  data-target="#sms-tempalte-filter" class="btn btn-img"> <img src="' + image_scr + '" alt="Filter" onmouseover=this.src="' + image_scr1 + '" onmouseout=this.src="' + image_scr + '"></a>'
        );

        $('.btn-add-close').on("click", function() {
            $('#items_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#items_list').DataTable().ajax.reload();
        });

        /*$scope.clear_search = function() {
            $('#search_item').val('');
            $('#items_list').DataTable().search('').draw();
        }

        var dataTables = $('#items_list').dataTable();
        $("#search_item").keyup(function() {
            dataTables.fnFilter(this.value);
        });*/

        //DELETE
        $scope.deleteItem = function($id) {
            $('#item_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#item_id').val();
            $http.get(
                laravel_routes['deleteItem'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', response.data.message);
                    $('#items_list').DataTable().ajax.reload();
                    $scope.$apply();
                } else {
                    custom_noty('error', response.data.errors);
                }
            });
        }

        //FOR FILTER
        /*$('#item_code').on('keyup', function() {
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
        }*/

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('itemForm', {
    templateUrl: item_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $timeout) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http({
            url: laravel_routes['getItemFormData'],
            method: 'GET',
            params: {
                'id': typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
            }
        }).then(function(response) { //console.log(response.data);
            self.item = response.data.item;
            self.extras = response.data.extras;
            self.category_list = response.data.category_list;
            self.action = response.data.action;
            self.theme = response.data.theme;
            self.primary_attachment = response.data.primary_attachment;
            self.additional_attachments = response.data.additional_attachments;
            console.log(self.additional_attachments);
            console.log(response.data);
            self.attachments = [];
            self.attachments_count = [{ 'row': [] }];
            self.counts = [1,2,3,4,5];
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.item.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
                if(self.item.has_free == 1) {
                    self.has_free = 'Yes';
                } else {
                    self.has_free = 'No';
                }
                if(self.item.has_free_shipping == 1) {
                    self.has_free_shipping = 'Yes';
                } else {
                    self.has_free_shipping = 'No';
                }
            } else {
                self.switch_value = 'Active';
                self.has_free = 'No';
                self.has_free_shipping = 'No';
            }
        });

        $scope.onSelectedCategory = function($id) {
            $http.get(
                laravel_routes['getCategory'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                console.log(response.data);
                self.category_list = response.data.category_list;
            });
        }

        $scope.addAttachmentRow = function(event){
            self.attachments_count.push({ 'row': [] });
        }

        $scope.onFileChange = function(event, key){
            var file = event[0];
            reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (e) => {
                self.primary_attachment_image = e.target.result;
                $('#attachment_image_' + key).html('<img src=' + e.target.result + ' width="350" height="200">')
            }                     
        }

        $scope.deleteAttachmentRow = function(key){
            alert(key);
            self.attachments_count.splice(key,1);
        }

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                // 'main_category_id': {
                //     required: true,
                // },
                // 'category_id': {
                //     required: true,
                // },
                // 'strength_id': {
                //     required: true,
                // },
                // 'package_size': {
                //     required: true,
                //     number: true,
                // },
                'display_order': {
                    required: true,
                    number: true,
                },
                'regular_price': {
                    required: true,
                    number: true,
                },
                'special_price': {
                    required: true,
                    number: true,
                },
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
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                custom_noty('error', errors);
                            } else {
                                $('#submit').button('reset');
                                $location.path('/product-pkg/item/list');
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            }
        });
    }
});
