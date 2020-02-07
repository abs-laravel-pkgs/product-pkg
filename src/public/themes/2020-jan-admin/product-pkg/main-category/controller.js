app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/product-pkg/main-category/list', {
        template: '<main-category-list></main-category-list>',
        title: 'Main Categories',
    }).
    when('/product-pkg/main-category/add', {
        template: '<main-category-form></main-category-form>',
        title: 'Add Main Category',
    }).
    when('/product-pkg/main-category/edit/:id', {
        template: '<main-category-form></main-category-form>',
        title: 'Edit Main Category',
    });
}]);

app.component('mainCategoryList', {
    templateUrl: main_category_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var dataTable = $('#main_category_list').DataTable({
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
                url: laravel_routes['getMainCategoryList'],
                data: function(d) {}
            },
            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'name', name: 'main_categories.name', searchable: true },
                { data: 'display_order', name: 'main_categories.display_order', searchable: false },
                { data: 'seo_name', name: 'main_categories.seo_name', searchable: true },
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
        $('.page-header-content .display-inline-block .data-table-title').html('Main Categories <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        $('.add_new_button').html(
            '<a href="#!/product-pkg/main-category/add" type="button" class="btn btn-secondary" dusk="add-btn">' +
            'Add Main Category' +
            '</a>' +
            '<a role="button" id="open" data-toggle="modal"  data-target="#sms-tempalte-filter" class="btn btn-img"> <img src="' + image_scr + '" alt="Filter" onmouseover=this.src="' + image_scr1 + '" onmouseout=this.src="' + image_scr + '"></a>'
        );

        $('.btn-add-close').on("click", function() {
            $('#main_category_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#main_category_list').DataTable().ajax.reload();
        });

        // $scope.clear_search = function() {
        //     $('#search_main-category').val('');
        //     $('#main_category_list').DataTable().search('').draw();
        // }

        // var dataTables = $('#main_category_list').dataTable();
        // $("#search_main-category").keyup(function() {
        //     dataTables.fnFilter(this.value);
        // });

        //DELETE
        $scope.deleteMainCategory = function($id) {
            $('#main_category_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#main_category_id').val();
            $http.get(
                laravel_routes['deleteMainCategory'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', response.data.message);
                    $('#main_category_list').DataTable().ajax.reload();
                    $scope.$apply();
                } else {
                    custom.noty('error', response.data.errors);
                }
            });
        }

        //FOR FILTER
        /*$('#main-category_code').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#main-category_name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#mobile_no').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#email').on('keyup', function() {
            dataTables.fnFilter();
        });
        $scope.reset_filter = function() {
            $("#main-category_name").val('');
            $("#main-category_code").val('');
            $("#mobile_no").val('');
            $("#email").val('');
            dataTables.fnFilter();
        }*/

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('mainCategoryForm', {
    templateUrl: main_category_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        fileUpload();
        $http({
            url: laravel_routes['getMainCategoryFormData'],
            method: 'GET',
            params: {
                'id': typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
            }
        }).then(function(response) {
            //console.log(response.data);
            self.main_category = response.data.main_category;
            self.attachment = response.data.attachment;
            self.action = response.data.action;
            self.theme = response.data.theme;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.main_category.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
                if (self.attachment) {
                    $scope.PreviewImage = 'public/themes/' + self.theme + '/img/main_category_icon/' + self.attachment.name;
                    $('#edited_file_name').val(self.attachment.name);
                } else {
                    $('#edited_file_name').val('');
                }
            } else {
                self.switch_value = 'Active';
            }
        });


        $scope.SelectFile = function(e) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $scope.PreviewImage = e.target.result;
                $scope.$apply();
            };
            reader.readAsDataURL(e.target.files[0]);
        };

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            errorPlacement: function(error, element) {
                if (element.attr("name") == "icon_id") {
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
                'display_order': {
                    required: true,
                    minlength: 3,
                    maxlength: 8,
                },
                'seo_name': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'icon_id': {
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
            },
            messages: {
                'icon_id': {
                    extension: "Accept Image Files Only. Eg: jpg,jpeg,png,ico,bmp,svg,gif"
                }
            },
            invalidHandler: function(event, validator) {
                custom_noty('error', 'You have errors,Please check all tabs');
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveMainCategory'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message)
                            $location.path('/product-pkg/main-category/list');
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
                                $location.path('/product-pkg/main-category/list');
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
