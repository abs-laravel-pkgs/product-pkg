app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/product-pkg/category/list', {
        template: '<category-list></category-list>',
        title: 'Categories',
    }).
    when('/product-pkg/category/add', {
        template: '<category-form></category-form>',
        title: 'Add Category',
    }).
    when('/product-pkg/category/edit/:id', {
        template: '<category-form></category-form>',
        title: 'Edit Category',
    });
}]);

app.requires.push('textAngular');
app.component('categoryList', {
    templateUrl: category_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        alert(laravel_routes['filterCategory'])
        $http.get(
            laravel_routes['filterCategory'],{
        }).then(function(response) {
            // console.log(response.data);
            self.main_categories = response.data.main_categories;
            $rootScope.loading = false;
        });

        var dataTable = $('#category_list').DataTable({
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
                url: laravel_routes['getCategoryList'],
                data: function(d) {
                    d.name = $('#name').val();
                    d.seo_name = $('#seo_name').val();
                    d.main_category = $('#main_category').val();
                    d.status_name = $('#status_name').val();
                }
            },
            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'category_name', name: 'categories.name', searchable: true },
                { data: 'main_category_name', name: 'main_categories.name', searchable: true },
                { data: 'category_display_order', name: 'categories.display_order', searchable: false },
                { data: 'category_seo_name', name: 'categories.seo_name', searchable: true },
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
        $('.page-header-content .display-inline-block .data-table-title').html('Categories <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        $('.add_new_button').html(
            '<a href="#!/product-pkg/category/add" type="button" class="btn btn-secondary" dusk="add-btn">' +
            'Add Category' +
            '</a>' +
            '<a role="button" id="open" data-toggle="modal"  data-target="#modal-categories-list-filter" class="btn btn-img"> <img src="' + image_scr + '" alt="Filter" onmouseover=this.src="' + image_scr1 + '" onmouseout=this.src="' + image_scr + '"></a>'
        );

        $('.btn-add-close').on("click", function() {
            $('#category_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#category_list').DataTable().ajax.reload();
        });

        //DELETE
        $scope.deleteCategory = function($id) {
            $('#category_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#category_id').val();
            $http.get(
                laravel_routes['deleteCategory'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', response.data.message);
                    $('#category_list').DataTable().ajax.reload();
                    $scope.$apply();
                } else {
                    // custom_noty('error', response.data.errors);
                    $noty = new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: response.data.errors,
                    }).show();
                }
            });
        }
        self.status = [
            { id: '', name: 'Select Status' },
            { id: '1', name: 'Active' },
            { id: '0', name: 'Inactive' },
        ];
        //FOR FILTER
        $('#name').on('keyup', function() {
            dataTable.draw();
        });
        $('#seo_name').on('keyup', function() {
            dataTable.draw();
        });
        $scope.myFunc = function(main_category_id) {
            $('#main_category').val(main_category_id);
            dataTable.draw();
        };
        $scope.myFunc2 = function(selected_status_id) {
            $('#status_name').val(selected_status_id);
            dataTable.draw();
        };
        $scope.reset_filter = function() {
            $("#name").val('');
            $("#seo_name").val('');
            $('#main_category').val(null);
            $("#status_name").val(null);
            dataTable.draw();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('categoryForm', {
    templateUrl: category_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        get_form_data_url = typeof($routeParams.id) == 'undefined' ? laravel_routes['getCategoryFormData'] : laravel_routes['getCategoryFormData'] + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        fileUpload();
        $http({
            url: laravel_routes['getCategoryFormData'],
            method: 'GET',
            params: {
                'id': typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
            }
        }).then(function(response) {
            // console.log(response.data);
            self.category = response.data.category;
            self.attachment = response.data.attachment;
            self.extras = response.data.extras;
            self.action = response.data.action;
            self.theme = response.data.theme;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.category.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
                if (self.category.has_free == 1) {
                    self.has_free = 'Yes';
                } else {
                    self.has_free = 'No';
                }
                if (self.category.has_free_shipping == 1) {
                    self.has_free_shipping = 'Yes';
                } else {
                    self.has_free_shipping = 'No';
                }
                if (self.category.is_best_selling == 1) {
                    self.is_best_selling = 'Yes';
                } else {
                    self.is_best_selling = 'No';
                }
                if (self.attachment) {
                    $scope.PreviewImage = 'public/themes/' + self.theme + '/img/category_image/' + self.attachment.name;
                    $('#edited_file_name').val(self.attachment.name);
                } else {
                    $('#edited_file_name').val('');
                }
            } else {
                self.switch_value = 'Active';
                self.has_free = 'Yes';
                self.has_free_shipping = 'Yes';
                self.is_best_selling = 'Yes';
            }
        });
        $('form:first *:input[type!=hidden]:first').focus();
        $scope.SelectFile = function(e) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $scope.PreviewImage = e.target.result;
                $scope.$apply();
            };
            reader.readAsDataURL(e.target.files[0]);
        };

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            errorPlacement: function(error, element) {
                if (element.attr("name") == "image_id") {
                    error.insertAfter("#attachment_error");
                } else {
                    error.insertAfter(element);
                }
            },
            rules: {
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'main_category_id': {
                    required: true,
                },
                'display_order': {
                    required: true,
                    number: true,
                    maxlength: 8,
                },
                'package_type_id': {
                    required: true,
                },
                'display_order': {
                    required: true,
                },
                'image_id': {
                    required: function() {
                        if (self.action == 'Edit') {
                            if (self.attachment) {
                                return false;
                            } else {
                                return true;
                            }
                        } else {
                            return true;
                        }
                    },
                    extension: "jpg|jpeg|png|ico|bmp|svg|gif",
                },
                'customer_rating': {
                    required: true,
                    number: true,
                    min: 1,
                    max: 5,
                    //minlength: 1,
                    //maxlength: 4,
                },
                'seo_name': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'page_title': {
                    required: true,
                    minlength: 3,
                    maxlength: 255,
                },
            },
            invalidHandler: function(event, validator) {
                // custom_noty('error', 'You have errors,Please check all tabs');
                $noty = new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'You have errors,Please check all tabs',
                }).show();
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveCategory'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message)
                            $location.path('/product-pkg/category/list');
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                $noty = new Noty({
                                    type: 'error',
                                    layout: 'topRight',
                                    text: errors,
                                }).show();
                                // custom_noty('error', errors);
                            } else {
                                $('#submit').button('reset');
                                $location.path('/product-pkg/category/list');
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        // custom_noty('error', 'Something went wrong at server');
                        $noty = new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: 'Something went wrong at server',
                        }).show();
                    });
            }
        });
    }
});
