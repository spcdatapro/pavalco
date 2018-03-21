(function(){

    var calculaintegractrl = angular.module('cpm.calculaintegractrl', []);

    calculaintegractrl.controller('calculaIntegraCtrl', ['$scope', 'comunFact', '$interval', function($scope, comunFact, $interval){

        $scope.ultact = '';

        function updateCalculoIntegracion(){ comunFact.doGET('php/calculaintegra.php/calcula').then(function(d){ $scope.ultact = d.ultact; }); }

        var intervalo = 3600000 * 12;
        $interval(updateCalculoIntegracion, intervalo);

        updateCalculoIntegracion();

    }]);

}());
