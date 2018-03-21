(function(){

    var facturactrl = angular.module('cpm.facturactrl', []);

    facturactrl.controller('facturaCtrl', ['$scope', 'facturaSrvc', 'authSrvc', 'empresaSrvc', 'toaster', 'DTOptionsBuilder', '$route', function($scope, facturaSrvc, authSrvc, empresaSrvc, toaster, DTOptionsBuilder, $route){
        //$scope.tituloPagina = 'CPM';

        $scope.facturas = [];
        $scope.factura = {};
        $scope.dectc = 2;
        $scope.verTodas = 0;
        $scope.permiso = {};

        $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withBootstrap().withOption('responsive', true);

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
                empresaSrvc.getDecimalesTC(parseInt(usrLogged.workingon)).then(function(r){ $scope.dectc = parseInt(r.dectc); });
            }
        });

        $scope.getLstFacturas = function(todas){
            facturaSrvc.lstFacturas(todas).then(function(d){
                for(var i = 0; i < d.length; i++){
                    d[i].id = parseInt(d[i].id);
                    d[i].idcontrato = parseInt(d[i].idcontrato);
                    d[i].idcliente = parseInt(d[i].idcliente);
                    d[i].fecha = moment(d[i].fecha).toDate();
                    d[i].iva = parseFloat(d[i].iva).toFixed(2);
                    d[i].total = parseFloat(d[i].total).toFixed(2);
                    d[i].pagada = parseInt(d[i].pagada);
                    d[i].tipocambio = parseFloat(d[i].tipocambio).toFixed($scope.dectc);
                }
                $scope.facturas = d;
            });
        };

        $scope.pagarFactura = function(obj){
            facturaSrvc.editRow(obj, 'pagar').then(function(){
                toaster.pop('success', 'Factura pagada', 'La factura No. ' + obj.factura + ' fue marcada como pagada.', 'timeout:1000');
                $scope.getLstFacturas($scope.verTodas);
            });
        };

        $scope.getLstFacturas($scope.verTodas);
    }]);

}());
