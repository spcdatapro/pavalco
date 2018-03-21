angular.module('cpm')
.controller('posRutasController', ['$scope', '$http', '$route', '$routeParams', '$compile',
    function($scope, $http, $route, $routeParams, $compile){
        switch ($routeParams.tipo) {
            case 'mnt':
                var path = 'pos/pages/mnt/';
                break;
            case 'trans':
                var path = 'pos/pages/trans/';
                break;

            default:
                var path = 'pos/pages/';
                break;
        }
        
        $route.current.templateUrl = path + $routeParams.pagina + ".html";

        $http.get($route.current.templateUrl).then(function (msg) {
            $('#contenidoPlanilla').html($compile(msg.data)($scope));
        });
    }
]);