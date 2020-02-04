app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/item', {
        template: '<items></items>',
        title: 'Items',
    });
}]);

app.component('items', {
    templateUrl: item_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http({
            url: laravel_routes['getItems'],
            method: 'GET',
        }).then(function(response) {
            self.items = response.data.items;
            $rootScope.loading = false;
        });
        $rootScope.loading = false;
    }
});