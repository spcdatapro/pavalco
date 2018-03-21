(function(){

    var monedactrl = angular.module('cpm.monedactrl', ['cpm.monedasrvc']);

    monedactrl.controller('monedaCtrl', ['$scope', 'monedaSrvc', 'authSrvc', 'empresaSrvc', '$route', function($scope, monedaSrvc, authSrvc, empresaSrvc, $route){
        //$scope.tituloPagina = 'CPM';

        $scope.laMoneda = {tipocambio: 1};
        $scope.lasMonedas = [];
        $scope.dectc = 2;
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
                empresaSrvc.getDecimalesTC(parseInt(usrLogged.workingon)).then(function(r){ $scope.dectc = parseInt(r.dectc); });
            }
        });

        $scope.getLstMonedas = function(){
            monedaSrvc.lstMonedas().then(function(d){
                for(var i = 0; i < d.length; i++){
                    d[i].id = parseInt(d[i].id);
                    d[i].tipocambio = parseFloat(d[i].tipocambio);
                };
                $scope.lasMonedas = d;
            });
        };

        $scope.addMoneda = function(obj){
            monedaSrvc.editRow(obj, 'c').then(function(){
                $scope.getLstMonedas();
                $scope.laMoneda = {};
            });
        };

        $scope.updMoneda = function(data, id){
            data.id = id;
            monedaSrvc.editRow(data, 'u').then(function(){
                $scope.getLstMonedas();
            });
        };

        $scope.delMoneda = function(id){
            monedaSrvc.editRow({id:id}, 'd').then(function(){
                $scope.getLstMonedas();
            });
        };

        $scope.getLstMonedas();
    }]);

}());